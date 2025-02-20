<?php
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewiplist extends SugarView {
    private $websites = [
        'vietjet.net' => 'bookingvj',
        'timchuyenbay.com' => 'tcbcom',
    ];

    function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $from_date = isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date']) ? $_REQUEST['from_date'] : date('d-m-Y');
            $to_date = isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date']) ? $_REQUEST['to_date'] : date('d-m-Y');
            if (strtotime($from_date) > strtotime($to_date)) {
                echo '<div class="alert alert-danger fs-6">Khoảng thời gian không hợp lệ</div>';
            }

            $data = $this->populateContent($from_date, $to_date);

            $smartyCont = new Sugar_Smarty();
            $smartyCont->assign('IP_LIST_TBL', $data);
            if (strpos($from_date, ":") === false) {
                $smartyCont->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
                $smartyCont->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
            } else {
                $smartyCont->assign('FROM_DATE_VALUE', date('d-m-Y H:i', strtotime($from_date)));
                $smartyCont->assign('TO_DATE_VALUE', date('d-m-Y H:i', strtotime($to_date)));
            }
            $smartyCont->assign('CURRENT_DATE', date('d-m-Y H:i'));
            $smartyCont->assign('THREE_HOURS_AGO', date('d-m-Y H:i', strtotime('-3 hours')));
            $smartyCont->assign('SIX_HOURS_AGO', date('d-m-Y H:i', strtotime('-6 hours')));
            $smartyCont->assign('TWELVE_HOURS_AGO', date('d-m-Y H:i', strtotime('-12 hours')));
            $smartyCont->assign('TODAY', date('d-m-Y'));
            $smartyCont->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
            $smartyCont->assign('THREEDAY_AGO', date('d-m-Y', strtotime('-3 day')));
            $smartyCont->assign('SEVENDAY_AGO', date('d-m-Y', strtotime('-7 day')));
            $smartyCont->assign('THIRTYDAY_AGO', date('d-m-Y', strtotime('-30 day')));
            $smartyCont->display('modules/EC_TongHop/tpls/view_iplist.tpl');
        }
        else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    private function populateContent($from_date, $to_date) {
        $tab = $tab_content = '';
        $i = 0;
        foreach ($this->websites as $domain => $username) {
            $tbody = $overview_html = "";
            if($i === 0) {
                $json = $this->getInfoLog($domain, $from_date, $to_date);
                $arr = json_decode(html_entity_decode($json), true);

                if (isset($arr['error']) && $arr['error'] == 0) {
                    $total_ip               = $arr['total_count_file'];
                    $total_count_search     = $arr['total_count_search'];
                    $total_count_session    = $arr['total_count_session'];
                    $total_count_api_vj     = $arr['total_count_api_vj']; // Vietjet API
    
                    if (isset($arr['data']) && !empty($arr['data'])) {
                        foreach ($arr['data'] as $ip => $value) {
                            // Block infp
                            $class_block = $btn_blocking_history = '';
                            if(isset($arr['data_block']['ip']) && !empty($arr['data_block']['ip'])) {
                                $class_block = " alert alert-danger";

                                $block_data = base64_encode(json_encode($arr['data_block']['ip']));
                                $btn_blocking_history = '<div class="btn-group">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#modal_history-block-'. $username .'" class="btn btn-secondary btn-history-block" data="'.$block_data.'" site="'. $username .'">
                                        Blocking history
                                    </button>
                                </div>';
                            }
    
                            $count_search = 0;
                            $journey_html = '';
                            if (isset($value['journey']) && !empty($value['journey'])) {
                                $journey_html .= '<div class="search-journey-wrap">';
                                arsort($value['journey']);
                                foreach ($value['journey'] as $journey => $count) {
                                    $journey_html .= '<span class="btn btn-primary-2 journey">' . $journey . '<b class="' . ($count >= 30 ? 'text-danger' : 'text-normal') . '"> (' . $count . ')</b></span>';
                                    $count_search += $count;
                                }
                                $journey_html .= '</div>';
                            }
                            
                            $count_session  = isset($value['session']) ? $value['session'] : 0;
                            $count_apivj    = isset($value['api_vj']) ? $value['api_vj'] : 0;
                            $start_datetime = !empty($value['start_time']) ? date("H:i d-m", $value['start_time']) : 'ERROR: Malformed';
                            $end_datetime   = !empty($value['end_time']) ? date("H:i d-m", $value['end_time']) : 'ERROR: Malformed';
    
                            if ($value['total_search'] > 9) {
                                $tbody .= '
                                    <tr class="row-'. $username . $class_block .'">
                                        <td class="text-start ip"><a target="_blank" href="https://ipinfo.io/'. $ip .'"><span class="text-overflow-mobile">'. $ip .'</span></a></td>
                                        <td class="text-start">'. $journey_html .'</td>
                                        <td class="text-center fw-semibold">'. $count_search .'</td>
                                        <td class="text-center count-session">'. $count_session .'</td>
                                        <td class="text-center start-datetime">'. $start_datetime .'</td>
                                        <td class="text-center end-datetime">'. $end_datetime .'</td>
                                        <td class="text-center count-apivj">'. $count_apivj .'</td>
                                        <td class="text-center">
                                            <div class="btn-group__wrap">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Block
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="3600" class="dropdown-item btn-block">Block: 1 Tiếng</a></li>
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="21600" class="dropdown-item btn-block">Block: 6 Tiếng</a></li>
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="86400" class="dropdown-item btn-block">Block: 1 ngày</a></li>
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="604800" class="dropdown-item btn-block">Block: 7 ngày</a></li>
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="2592000" class="dropdown-item btn-block">Block: 30 ngày</a></li>
                                                        <li><a ip="'. $ip .'" domain="'. $domain .'" duration="-1" class="dropdown-item btn-block">Block: Vĩnh viễn</a></li>
                                                    </ul>
                                                </div>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-unblock" domain="'. $domain .'" ip="'. $ip .'">
                                                        Unlock
                                                    </button>
                                                </div>
                                                '.$btn_blocking_history.'
                                            </div>
                                        </td>
                                    </tr>
                                ';
                            }
                        }
                    }
                }
                else {
                    $m = isset($arr['message']) && !empty($arr['message']) ? : '<i>Chưa có dữ liệu</i>';
                    $tbody .= '<tr><td colspan="8" class="text-center">'. $m .'</td></tr>';
                }

                $overview_html = $this->populateOverview($total_ip, $total_count_search, $total_count_session, $total_count_api_vj, count($arr['data_block']));
            }

            // Tab
            $tab_id             = "nav-log-$username-tab";
            $tab_class          = $i == 0 ? "nav-link active" : "nav-link";
            $tab_aria_selected  = $i == 0 ? "true" : "false";
            $tab_aria_controls  = "nav-log-$username";
            $tab_target         = "#nav-log-$username";
            $tab .= '<button id="'. $tab_id .'"
                class="'. $tab_class .'" 
                type="button"
                role="tab"
                data-bs-toggle="tab"
                data-bs-target="'. $tab_target .'"
                aria-controls="'. $tab_aria_controls .'"
                aria-selected="'. $tab_aria_selected .'"
            >'
                .$domain.
            '</button>';
            
            $tab_content .= '<div class="tab-pane tab-pane-'. $username .' fade '. ($i == 0 ? 'show active' : '') .'" id="nav-log-'. $username .'" role="tabpanel" aria-labelledby="nav-log-'. $username .'-tab" tabindex="0">
                <div class="handle-ip handle-ip-'. $username .'">
                    <div class="group">
                        <label for="input_ip">IP:</label>
                        <input id="input_ip" class="box-input" type="text" value="" size="12" />
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-unblock" id="unblock_ip" domain="'. $domain .'">Unblock</button>
                        <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Block</button>
                        <ul class="dropdown-menu">
                            <li><a domain="'. $domain .'" duration="3600" class="dropdown-item handle-ip-item block-item">1 Tiếng</a></li>
                            <li><a domain="'. $domain .'" duration="21600" class="dropdown-item handle-ip-item block-item">6 Tiếng</a></li>
                            <li><a domain="'. $domain .'" duration="86400" class="dropdown-item handle-ip-item block-item">1 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="604800" class="dropdown-item handle-ip-item block-item">7 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="2592000" class="dropdown-item handle-ip-item block-item">30 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="-1" class="dropdown-item handle-ip-item block-item">Vĩnh viễn</a></li>
                        </ul>
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Whitelist</button>
                        <ul class="dropdown-menu">
                            <li><a domain="'. $domain .'" duration="3600" class="dropdown-item handle-ip-item whitelist-item">1 Tiếng</a></li>
                            <li><a domain="'. $domain .'" duration="21600" class="dropdown-item handle-ip-item whitelist-item">6 Tiếng</a></li>
                            <li><a domain="'. $domain .'" duration="86400" class="dropdown-item handle-ip-item whitelist-item">1 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="604800" class="dropdown-item handle-ip-item whitelist-item">7 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="2592000" class="dropdown-item handle-ip-item whitelist-item">30 ngày</a></li>
                            <li><a domain="'. $domain .'" duration="-1" class="dropdown-item handle-ip-item whitelist-item">Vĩnh viễn</a></li>
                        </ul>
                    </div>
                </div>

                <h3 class="sub-title text-center mt-3">Thống kê truy vấn từ '. $from_date .' đến '. $to_date .'</h3>
                <div class="overview__wrap">
                    <section class="chart overview-chart flex-fill">
                        <canvas id="chartjs__overview" class="mx-auto" style="height:350px; width:70%;"></canvas>
                    </section>
                    '.$overview_html.'
                </div>

                <table id="iplist_tbl" class="table-iplist table-iplist-'. $username .' table-details__booking w-100" cellpadding="0" cellspacing="0" border="0">
                    <thead>
                        <th class="bg-yellow" width="10%">Danh sách IP</th>
                        <th class="bg-yellow">Tìm hành trình</th>
                        <th class="bg-yellow" width="5%">Tổng</th>
                        <th class="bg-yellow" width="5%">Session</th>
                        <th class="bg-yellow" width="10%">Bắt đầu</th>
                        <th class="bg-yellow" width="10%">Kết thúc</th>
                        <th class="bg-yellow" width="5%">API</th>
                        <th class="bg-yellow" width="18%"></th>
                    </thead>
                    <tbody>'.$tbody.'</tbody>
                </table>

                <div class="modal fade" id="modal_history-block-'. $username .'" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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

            $i++;
        }
        
        $nav = '<nav><div class="nav nav-tabs ip-nav-tabs mt-3" id="nav-tab" role="tablist">'.$tab.'</div></nav>';
        $html = '<div class="tab-content ip-tab-content overflow-auto" id="nav-tabContent">'.$tab_content.'</div>';
        return $nav . $html;
    }

    private function populateOverview($total_ip, $total_search, $total_session, $total_apivj, $total_blocked_ip) {
        return '<script type="text/javascript">
            $(document).ready(function() {
                const data_total_ip         = '. $total_ip .';
                const data_total_search     = '. $total_search .';
                const data_total_session    = '. $total_session .';
                const data_total_apivj      = '. $total_apivj .';
                const data_total_blocked_ip = '. $total_blocked_ip .';

                let dataset = {
                    labels: ["Số lượng IP", "Tìm chuyến bay", "Session", "Tìm qua API VJ", "Chặn IP"],
                    datasets: [{
                        label: "Tổng",
                        data: [data_total_ip, data_total_search, data_total_session, data_total_apivj, data_total_blocked_ip],
                        minBarLength: 15,
                        backgroundColor: [
                            "rgb(42, 61, 153)",
                            "rgb(16, 110, 233)",
                            "rgb(241, 187, 8)",
                            "rgb(235, 33, 39)",
                            "rgb(44, 48, 57)",
                        ],
                        datalabels: {
                            align: "center",
                            anchor: "center",
                            formatter: function(value, textContent) {
                                const nFormat = new Intl.NumberFormat();
                                return `${nFormat.format(Math.round(value))}`;
                            }
                        }
                    }]
                };

                let options_ = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                        },
                        // title: {
                        //     display: true,
                        //     text: "(ghi chú : DS bán /1K)",
                        //     align:"end",
                        //     position:"top",
                        //     font: {
                        //         style: "italic"
                        //     }
                        // },
                        datalabels: {
                            anchor: "center",
                            align: "center",
                            padding: 0,
                            color: "#fff",
                            font: {
                                size: 15,
                                weight: "bold"
                            },
                        },
                    }
                };

                // Chart
                const overview_chart = document.getElementById("chartjs__overview");
                const duration_averageChart = new Chart(overview_chart, {
                    type: "bar",
                    data: dataset,
                    plugins: [ChartDataLabels],
                    options: options_
                });
            });
        </script>';
    }

    /**
     * Get info log (IP) on website
     * 
     * @param string $domain
     * @param string $from_date
     * @param string $to_date
     * @param string $ip
     * @return string JSON
     */
    public function getInfoLog($domain, $from_date, $to_date, $ip = '') {
        global $sugar_config;
        $url = "https://$domain/search-tracking";
        $post_data = [
            'token'     => $sugar_config['search_tracking_token'][$domain],
            'action'    => 'get_info_log',
            'from_date' => $from_date,
            'to_date'   => $to_date,
        ];
        if(!is_null($ip) && !empty($ip)) $post_data['ip'] = $ip;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $json = curl_exec($curl);
        curl_close($curl);

        return $json;
    }

    /**
     * Block IP on website
     * 
     * @param string $domain
     * @param string $ip
     * @param int $duration
     * @return string JSON
     */
    public function blockIP($domain, $ip, $duration) {
        global $sugar_config;
        $url = "https://$domain/search-tracking";
        $post_data = [
            'token'  	=> $sugar_config['search_tracking_token'][$domain],
            'action' 	=> 'block_ip',
            'ip' 		=> $ip,
            'duration' 	=> $duration,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Unblock IP on website
     * 
     * @param string $domain
     * @param string $ip
     * @return string JSON
     */
    public function unblockIP($domain, $ip) {
        global $sugar_config;
        $url = "https://$domain/search-tracking";
        $post_data = [
            'token'  	=> $sugar_config['search_tracking_token'][$domain],
            'action'    => 'unblock_ip',
            'ip' 		=> $ip,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Whitelist IP on website
     * 
     * @param string $domain
     * @param string $ip
     * @param int $duration
     * @return string JSON
     */
    public function whitelistIP($domain, $ip, $duration) {
        global $sugar_config;
        $url = "https://$domain/search-tracking";
        $post_data = [
            'token'  	=> $sugar_config['search_tracking_token'][$domain],
            'action' 	=> 'whitelist_ip',
            'ip' 		=> $ip,
            'duration' 	=> $duration,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
