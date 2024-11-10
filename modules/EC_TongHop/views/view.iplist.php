<?php
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewiplist extends SugarView {
	function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->displayJS();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_TongHop/tpls/view_iplist.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function displayJS()
    {
         $js = '';
         $js .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
         $js .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
         echo $js;
    }

    function populateContent($smarty) {
        // Đến ngày
        if (empty($_REQUEST['to_date'])) {
            $to_date = date('d-m-Y');
        } else $to_date = $_REQUEST['to_date'];

        // Từ ngày
        if(empty($_REQUEST['from_date'])) {
            // $from_date = date('Y-m-d', strtotime('-7 days', strtotime($to_date)));
            $from_date = date('d-m-Y');
        } else $from_date = $_REQUEST['from_date'];

        if(strtotime($from_date) > strtotime($to_date)){
            echo '<div class="alert alert-danger fs-6">Khoảng thời gian không hợp lệ.</div>';
        }

        $data = $this->getIPList($from_date, $to_date);
        $smarty->assign('IP_LIST_TBL', $data);

        if (strpos($from_date, ":") === false) { 
            $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
            $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
        } else {
            $smarty->assign('FROM_DATE_VALUE', date('d-m-Y H:i', strtotime($from_date)));
            $smarty->assign('TO_DATE_VALUE', date('d-m-Y H:i', strtotime($to_date)));
        }

        $smarty->assign('CURRENT_DATE', date('d-m-Y H:i'));
        $smarty->assign('THREE_HOURS_AGO', date('d-m-Y H:i', strtotime('-3 hours')));
        $smarty->assign('SIX_HOURS_AGO', date('d-m-Y H:i', strtotime('-6 hours')));
        $smarty->assign('TWELVE_HOURS_AGO', date('d-m-Y H:i', strtotime('-12 hours')));

        $smarty->assign('TODAY', date('d-m-Y'));
        $smarty->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smarty->assign('THREEDAY_AGO', date('d-m-Y', strtotime('-3 day')));
        $smarty->assign('SEVENDAY_AGO', date('d-m-Y', strtotime('-7 day')));
        $smarty->assign('THIRTYDAY_AGO', date('d-m-Y', strtotime('-30 day')));
    }

    function getIPList($from_date, $to_date) {
        global $db;

        $html = $html_nav = '';
        $array_summary = array();

        $arr_site = array(
            'timchuyenbay.com' => 'tcbcom',
            // 'vietjet.net' => 'bookingvj',
        );

        $name_tab = array(
            'tcbcom' => 'Tìm chuyến bay',
            // 'bookingvj' => 'Vietjet',
        );

        $html_nav .= '<nav><div class="nav nav-tabs ip-nav-tabs mt-3" id="nav-tab" role="tablist">';

        $html .= '<div class="tab-content ip-tab-content overflow-auto" id="nav-tabContent">';

        foreach($arr_site as $domain => $site){
            $is_start = $site === reset($arr_site);

            // lOOP NAV
            $html_nav .= '<button class="nav-link '.($is_start ? 'active' : '').'" id="nav-log-'.$site.'-tab" data-bs-toggle="tab" data-bs-target="#nav-log-'.$site.'" type="button" role="tab" aria-controls="nav-log-'.$site.'" aria-selected="'.($is_start ? 'true' : 'false').'">'.$name_tab[$site].'</button>';

            // CURL - GET IP - LOOP tab-content
            $html .= '<div class="tab-pane tab-pane-'.$site.' fade '.($is_start ? 'show active' : '').'" id="nav-log-'.$site.'" role="tabpanel" aria-labelledby="nav-log-'.$site.'-tab" tabindex="0">
                        <div class="white-list-ip white-list-ip-'.$site.'">
                            <div class="group">
                                <label for="white-list">White list IP:</label>
                                <input id="white-list" class="box-input input-whitelist" type="text" value="" />
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    White List
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a domain="'.$domain.'" time-wl="86400" class="dropdown-item white-list-item cursor-pointer">1 ngày</a></li>
                                    <li><a domain="'.$domain.'" time-wl="172800" class="dropdown-item white-list-item cursor-pointer">2 ngày</a></li>
                                    <li><a domain="'.$domain.'" time-wl="604800" class="dropdown-item white-list-item cursor-pointer">1 tuần</a></li>
                                    <li><a domain="'.$domain.'" time-wl="2592000" class="dropdown-item white-list-item cursor-pointer">1 tháng</a></li>
                                    <li><a domain="'.$domain.'" time-wl="-1" class="dropdown-item white-list-item cursor-pointer">Vĩnh viễn</a></li>
                                </ul>
                            </div>
                        </div>
                        <table id="iplist_tbl" class="table-iplist table-iplist-'.$site.' table-details__booking w-100" cellpadding="0" cellspacing="0" border="0">
                            <thead>
                                <th class="bg-yellow" width="10%">Danh sách IP</th>
                                <th class="bg-yellow">Tìm chuyến bay</th>
                                <th class="bg-yellow" width="5%">Tổng</th>
                                <th class="bg-yellow" width="5%">Session</th>
                                <th class="bg-yellow" width="10%">Bắt đầu</th>
                                <th class="bg-yellow" width="10%">Kết thúc</th>
                                <th class="bg-yellow" width="5%">API</th>
                                <th class="bg-yellow" width="18%"></th>
                            </thead>
                            <tbody>
            ';

            $url = 'https://'.$domain.'/info-tcb';
            $post_data = [
                'from-date' => $from_date,
                'to-date' => $to_date,
                'type' => 'get-infor-tcb',
            ];



            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            $json = curl_exec($curl);

            // Lấy mã trạng thái HTTP
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_msg = curl_error($curl);
            curl_close($curl);

            // if($GLOBALS['current_user']->user_name == 'hungnh'){
            //     echo "url: ";
            //     pr($url);

            //     echo "post_data: ";
            //     pr($post_data);

            //     echo "http_code: ";
            //     pr($http_code);

            //     echo "cURL error: ";
            //     pr($error_msg);
            // }

            $arr = json_decode(html_entity_decode($json), true);

            if(isset($arr['code']) && $arr['code'] == 200){
                $total_ip           = $arr['count_file'];
                $total_tcb_tcb      = $arr['count_tcb'];
                $total_session_tcb  = $arr['count_session'];
                $total_api_tcb      = $arr['count_api'];

                if(isset($arr['data']) && !empty($arr['data'])){
                    foreach($arr['data'] as $ip => $value) {
                        $tcb = '';
                        $class_block = in_array($ip,$arr['data_block']) ? "alert alert-danger" : "";
                        $total_tcb = 0;

                        if(isset($arr['data'][$ip]['journey']) && !empty($arr['data'][$ip]['journey'])){
                            $tcb .= '<div class="tcb-wrap">';

                            arsort($arr['data'][$ip]['journey']);
                            
                            foreach($arr['data'][$ip]['journey'] as $journey => $count) {
                                $tcb .= '<span class="btn btn-primary-2 fw-normal">' .$journey . '<b class="'.($count >= 30 ? 'text-danger' : 'text-normal').'"> ('.$count.')</b></span>';
                                $total_tcb += $count;
                            }
                            $tcb .= '</div>';
                        }

                        $start_dateTime = (!empty($value['start_date'])) ? DateTime::createFromFormat("d/m/Y H:i", $value['start_date']) : false;
                        if ($start_dateTime) {
                            $start_date = $start_dateTime->format("H:i d/m");
                        } else {
                            $start_date = 'ERROR: Malformed';
                        } 

                        $last_dateTime = (!empty($value['last_date'])) ? DateTime::createFromFormat("d/m/Y H:i", $value['last_date']) : false;
                        if ($last_dateTime) {
                            $last_date = $last_dateTime->format("H:i d/m");
                        } else {
                            $last_date = 'ERROR: Malformed';
                        }
                        
                        if($value['total_tcb'] > 9){
                            $html .= '
                                <tr class="row-'.$site.' '.$class_block.'">
                                    <td class="text-start ip"><a target="_blank" href="https://ipinfo.io/'.$ip.'"><span class="text-overflow-mobile">'.$ip.'</span></a></td>
                                    <td class="text-start tcb">'.$tcb.'</td>
                                    <td class="text-center fw-semibold total-tcb">'.$total_tcb.'</td>
                                    <td class="text-center total-session">'.$arr['data'][$ip]['session'].'</td>
                                    <td class="text-center start_date">' . $start_date. '</td>
                                    <td class="text-center last_date">' . $last_date . '</td>
                                    <td class="text-center api">'.$arr['data'][$ip]['api'].'</td>
                                    <td class="text-center">
                                        <div class="btn-group__wrap">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Block
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a ip="' . $ip . '" domain="'.$domain.'" time-block="3600" class="dropdown-item block-item cursor-pointer">Block: 1 Tiếng</a></li>
                                                    <li><a ip="' . $ip . '" domain="'.$domain.'" time-block="21600" class="dropdown-item block-item cursor-pointer">Block: 6 Tiếng</a></li>
                                                    <li><a ip="' . $ip . '" domain="'.$domain.'" time-block="86400" class="dropdown-item block-item cursor-pointer">Block: 1 ngày</a></li>
                                                    <li><a ip="' . $ip . '" domain="'.$domain.'" time-block="259200" class="dropdown-item block-item cursor-pointer">Block: 3 ngày</a></li>
                                                    <li><a ip="' . $ip . '" domain="'.$domain.'" time-block="-1" class="dropdown-item block-item cursor-pointer">Block: Vĩnh viễn</a></li>
                                                </ul>
                                            </div>
                                            <div class="btn-group">
                                                <button type="button" data-bs-toggle="modal" data-bs-target="#modal_history-block-'.$site.'" class="btn btn-secondary btn-history-block cursor-pointer" domain="'.$domain.'" site="'.$site.'" ip="' . $ip . '" data-fromdate="'.$from_date.'" data-todate="'.$to_date.'">
                                                    History
                                                </button>
                                            </div>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-allow cursor-pointer" domain="'.$domain.'" ip="' . $ip . '">
                                                    Unlock
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            ';
                        }

                        // $total_journey +=$total_tcb;
                        // $total_session +=$arr['data'][$ip]['session'];
                        // $total_api +=$arr['data'][$ip]['api'];
                    }
                }
            } else {
                $html .= '<tr>
                            <td colspan="6" class="text-start">
                                '.$arr['message'].'
                            </td>
                        </tr>';
            }

            $html .= '</tbody>
                </table>
                <div class="modal fade" id="modal_history-block-'.$site.'" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5 text-white">Lịch sử block IP</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="modal-body" id="content-history"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

            // SUMMARY
            $array_summary[$domain] = array(
                'total_ip' => $total_ip,
                // 'total_tcb' => $total_journey,
                // 'total_session' => $total_session,
                // 'total_api' => $total_api
                'total_tcb' => $total_tcb_tcb,
                'total_session' => $total_session_tcb,
                'total_api' => $total_api_tcb
            );
        }

        $html_nav .= '<button class="nav-link" id="nav-statistic-tab" data-bs-toggle="tab" data-bs-target="#nav-statistic" type="button" role="tab" aria-controls="nav-statistic" aria-selected="false">Thống kê</button>
                    </div>
                </nav>';

        $html .= '<div class="tab-pane fade" id="nav-statistic" role="tabpanel" aria-labelledby="nav-statistic-tab" tabindex="0">
                    <h3 class="sub-title text-center mt-3">Báo cáo IP từ '.$from_date.' đến '.$to_date.'</h3>
                    <div class="call-statistics__wrap">
                        <section class="call-statistics__total flex-fill">
                            <canvas id="chartjs__ip--total" class="mx-auto"></canvas>
                        </section>
                        <section class="call-statistics__total flex-fill">
                            <canvas id="chartjs__callbk--total" class="mx-auto"></canvas>
                        </section>
                    </div>
                </div>
            </div>';


        
        // Session  /  Tìm chuyến bay   /   truy vấn API   
        // =============================================
        // =============================================

        $label_total = $data_tcb_total = $data_session_total = $data_api_total = "[";
        foreach($array_summary as $site => $total){
            $label_total .= "'".$site."',";
            $data_tcb_total .= "'".$total['total_tcb']."',";
            $data_session_total .= "'".$total['total_session']."',";
            $data_api_total .= "'".$total['total_api']."',";
        }

        $label_total = substr($label_total, 0, -1); //Loại bỏ dấu , của element cuối cùng
        $label_total .= "]";

        $data_tcb_total = substr($data_tcb_total, 0, -1); //Loại bỏ dấu , của element cuối cùng
        $data_tcb_total .= "]";

        $data_session_total = substr($data_session_total, 0, -1); //Loại bỏ dấu , của element cuối cùng
        $data_session_total .= "]";

        $data_api_total = substr($data_api_total, 0, -1); //Loại bỏ dấu , của element cuối cùng
        $data_api_total .= "]";

        // Cuộc gọi đến  /  Cuộc gọi nhỡ  /  Booking
        // =============================================
        // =============================================
        $sql_search = "";
        $format = "d-m-Y";
        if(date($format, strtotime($from_date)) == date($from_date)) {
            $sql_search .= 'DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "'. date('Y-m-d', strtotime($from_date)) .'"
            AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "'. date('Y-m-d', strtotime($to_date)) .' 23:59:59"';
		} else {
            $sql_search .= 'DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= "'. date('Y-m-d H:i:s', strtotime($from_date)) .'"
            AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= "'. date('Y-m-d H:i:s', strtotime($to_date)) .'"';
        }

        $sql_call = 'SELECT COUNT(id) as quantity, direction 
                    FROM calls bk
                    WHERE '.$sql_search.'
                    AND deleted = 0
                    GROUP BY direction
                    HAVING direction IN ("inbound", "missed")';

        $total_inbound = $total_missed = 0;
        $res_call = $db->query($sql_call);
        while ($row = $db->fetchByAssoc($res_call)) {
            switch ($row['direction']) {
                case 'inbound':
                     $total_inbound = $row['quantity'];
                     break;
                case 'missed':
                     $total_missed = $row['quantity'];
                     break;
                default:
                     break;
           }
        }

        $total_bk = $total_bk_completed = $total_sales = 0;
        $total_bk_booker = $total_bk_booker_completed = 0;
        $total_prior_bk = $total_bk_prior_completed = 0;
        $total_bk_inter = $total_bk_inter_completed = 0;

        $sql_booking = 'SELECT 
                            bk.id AS bk_id, bk.name AS bk_name, bk.booking_status, bk.ticket_type
                            , IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
                            , IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id AND (add_type IS NULL OR add_type = ""))), 0) AS bk_sales
                            , SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi")), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi"))) AS my_bk
				            , SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND bk.booking_status IN (3, 7, 8)), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND booking_status IN (3, 7, 8))) AS com_my_bk
                            , MIN(i.departure_date) AS min_dep_date 
                            , DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
                        FROM ec_flight_bookings bk
                        INNER JOIN ec_booking_itineraries i ON i.deleted = 0 AND i.booking_id = bk.id
                        INNER JOIN users u ON u.id = bk.assigned_user_id
                        WHERE '.$sql_search.'
                        AND bk.deleted = 0
                        GROUP BY bk_id
                        ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4)';

        $res_booking = $db->query($sql_booking);
        while ($row = $db->fetchByAssoc($res_booking)) {
            // Booker đặt
            if($row['my_bk'] > 0){
                $total_bk_booker++;
            }
            if($row['com_my_bk'] > 0){
                $total_bk_booker_completed++;
            }

            // BK cận
            if (((strtotime($row['min_dep_date']) - strtotime($row['bk_date_entered'])) / 60) <= 1440) {
                $total_prior_bk++;
                if (in_array($row['booking_status'], array(3, 7, 8))) {
                    $total_bk_prior_completed++;
                }
            }

            // Booker inter
            if($row['ticket_type'] == 2){
                $total_bk_inter++;

                if (in_array($row['booking_status'], array(3, 7, 8))) {
                    $total_bk_inter_completed++;
                }
            }

			if (in_array($row['booking_status'], array(3, 7, 8))) {
                $total_bk_completed++;
            }

		    $total_sales += $row['bk_sales'];
		    $total_bk++;
        }

        // pr($sql_booking);

        // Booking hoàn tất  /  Doanh số bán  /  DS Quốc tế
        // =============================================
        // =============================================
        echo '<script type="text/javascript">
                $(document).ready(function() {

                    // Data chart pie
                    const data_tcb_total        = '.$data_tcb_total.';
                    const data_session_total    = '.$data_session_total.';
                    const data_api_total        = '.$data_api_total.';
                    const data_sales_total      = '.($total_sales/1000).';
                    
                    const data_inbound_total = '.$total_inbound.';
                    const data_missed_total = '.$total_missed.';

                    // Tổng
                    const data_bk_total = '.$total_bk.';
                    const data_bk_completed = '.$total_bk_completed.';

                    // Booker
                    const data_mybk_total = '.$total_bk_booker.';
                    const data_mybk_completed = '.$total_bk_booker_completed.';
                    
                    // Vé cận
                    const data_priorbk_total = '.$total_prior_bk.';
                    const data_priorbk_completed = '.$total_bk_prior_completed.';
                    
                    // Booker Inter
                    const data_bk_inter_total = '.$total_bk_inter.';
                    const data_bk_inter_completed = '.$total_bk_inter_completed.';

                    // Chart IP
                    // ====================
                    // ====================
                    const calls_duration_average = document.getElementById("chartjs__ip--total");

                    let data_summary_total = {
                        labels: ["Session", "Tìm chuyến bay", "Truy vấn API", "Doanh số bán"],
                        datasets: [
                            {
                                label: "Tổng",
                                data: [data_session_total, data_tcb_total, data_api_total, data_sales_total],
                                backgroundColor: [
                                    "rgb(236, 178, 16)",
                                    "rgb(35, 185, 228)",
                                    "rgb(138, 188, 60)",
                                    "rgb(255, 99, 132)"
                                ],
                                datalabels: {
                                    align: "center",
                                    anchor: "center",
                                    formatter: function(value, context) {
                                        const nFormat = new Intl.NumberFormat();
                                        return `${nFormat.format(Math.round(value))}`;
                                    }
                                }
                            }
                        ]
                    };

                    let options_summary_total = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: "top",
                            },
                            title: {
                                display: true,
                                text: "(ghi chú : DS bán /1K)",
                                align:"end",
                                position:"top",
                                font: {
                                    style: "italic"
                                }
                            },
                            datalabels: {
                                anchor: "center",
                                align: "center",
                                padding: 0,
                                color: "#000",
                                font: {
                                    size: 14,
                                    weight: "bold"
                                },
                            },
                        }
                    };

                    const duration_averageChart = new Chart(calls_duration_average, {
                        type: "pie",
                        data: data_summary_total, // Dữ liệu
                        plugins: [ChartDataLabels],
                        options: options_summary_total // Tùy chọn cấu hình
                    });
         








                    
                    // Chart CALL - BOOKING
                    // ====================
                    // ====================
                    const calls_booking = document.getElementById("chartjs__callbk--total");

                    let data_summary_callbk_total = {
                        labels: ["Cuộc gọi đến", "Cuộc gọi nhỡ", "Tổng bk", "Bk hoàn tất", "Booker đặt", "Booker đặt OK", "BK vé cận", "BK vé cận OK", "BK Quốc tế", "BK Quốc tế OK"],
                        datasets: [
                            {
                                label: "SL",
                                type: "bar",
                                data: [data_inbound_total, data_missed_total, data_bk_total, data_bk_completed, data_mybk_total, data_mybk_completed, data_priorbk_total, data_priorbk_completed, data_bk_inter_total, data_bk_inter_completed],
                                backgroundColor: [
                                    "rgb(26, 207, 180)",
                                    "rgb(255, 99, 132)",
                                    "rgb(35, 185, 228)",
                                    "rgb(35, 185, 228)",
                                    "rgb(138, 188, 60)",
                                    "rgb(138, 188, 60)",
                                    "rgb(236, 178, 16)",
                                    "rgb(236, 178, 16)",
                                    "rgb(35, 218, 224)",
                                    "rgb(35, 218, 224)",
                                ],
                                borderWidth: 1,
                                borderRadius: 10,
                                borderSkipped: false,
                                yAxisID: "bar-y-axis",
                                datalabels: {
                                    align: "center",
                                    anchor: "center",
                                }
                            }
                       ]
                    };

                    let options_summary_call_bk_total = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                           },
                            title: {
                                display: true,
                                align:"start",
                                text: "(Số lượng)",
                                position:"top",
                                font: {
                                    style: "italic"
                                }
                            },
                            datalabels: {
                                anchor: "center",
                                align: "center",
                                padding: 0,
                                color: "#000",
                                font: {
                                    size: 14,
                                    weight: "bold"
                                },
                            },
                        },
                    };

                    const callbk_Chart = new Chart(calls_booking, {
                        type: "bar",
                        data: data_summary_callbk_total,
                        plugins: [ChartDataLabels],
                        options: options_summary_call_bk_total
                    });
                });
            </script>';

        return $html_nav.$html;
    }
}
