<?php
require_once("include/Sugar_Smarty.php");

class Viewiplist extends SugarView {
	function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_iplist.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smarty) {
        // Đến ngày
        if (empty($_REQUEST['to_date'])) {
            $to_date = date('Y-m-d');
        } else $to_date = $_REQUEST['to_date'];

        // Từ ngày
        if(empty($_REQUEST['from_date'])) {
            // $from_date = date('Y-m-d', strtotime('-7 days', strtotime($to_date)));
            $from_date = date('Y-m-d');
        } else $from_date = $_REQUEST['from_date'];

        // Lấy ds ip
        if(isset($_POST['btnExport'])) {
            $is_export = 1;
        } else $is_export = 0;

        $data = $this->getIPList($from_date, $to_date, $is_export);

        if ($is_export) { 
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"ip_list_" . date('YmdHis') . "\".txt");
            ob_clean();
            flush();
            echo $data;
            exit;
        } else {
            $smarty->assign('IP_LIST_TBL', $data);
        }

        $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
        $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
        $smarty->assign('TODAY', date('d-m-Y'));
        $smarty->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smarty->assign('THREEDAY_AGO', date('d-m-Y', strtotime('-3 day')));
        $smarty->assign('SEVENDAY_AGO', date('d-m-Y', strtotime('-7 day')));
        $smarty->assign('THIRTYDAY_AGO', date('d-m-Y', strtotime('-30 day')));
    }

    function getIPList($from_date, $to_date, $is_export) {
        global $db, $app_list_strings;
        $from_date  = date('Y-m-d 00:00:00', strtotime($from_date));
        $to_date    = date('Y-m-d 23:59:59', strtotime($to_date));
        $html       = '';

        // trạng thái 3 - 8 là Xác nhận - Hoàn tất

        $sql = '
            SELECT 
                ip_address,
                COUNT(id) AS cnt,
                DATE_ADD(date_entered, INTERVAL 8 HOUR) AS first_date,
                DATE_ADD(MAX(date_entered), INTERVAL 8 HOUR) AS last_date,
                GROUP_CONCAT(booking_status) AS bk_stt,
                SUM(total_amount) AS bk_tt_amt,
                SUM(
                    IFNULL((
                        SELECT COUNT(DISTINCT parent_id)
                        FROM ec_flight_bookings_audit
                        WHERE 
                            parent_id = ec_flight_bookings.id
                            AND field_name = "contact_name"
                            AND before_value_string REGEXP "Panda Po|Cuong Nguyen|IT"
                            AND deleted = 0
                    ), 0)
                ) AS booker1,
                SUM(IF(contact_name REGEXP "Panda Po|Cuong Nguyen|IT", 1, 0)) AS booker2
            FROM ec_flight_bookings
            WHERE 
                DATE_ADD(date_entered, INTERVAL 8 HOUR) >= "' . $from_date . '"
                AND DATE_ADD(date_entered, INTERVAL 8 HOUR) <= "' . $to_date . '"
                AND ip_address IS NOT NULL AND ip_address <> ""
                AND deleted = 0
            GROUP BY ip_address
            HAVING bk_stt NOT REGEXP "8|3"
            ORDER BY cnt DESC, first_date
        ';

        $res = $db->query($sql);
        $i   = 0;
        while($row = $db->fetchByAssoc($res)) {
            if($is_export) {
                if(!empty($html)) $html .= "\n";
                $html .= $row['ip_address'];
            } else {
                // thống kê trạng thái booking
                $bk_stt_arr = array_count_values(explode(',', $row['bk_stt']));

                asort($bk_stt_arr);
                $bk_stt_str = '';
                foreach($bk_stt_arr AS $bk_stt_value => $bk_cnt) {
                    if(!empty($bk_stt_str)) $bk_stt_str .= ', ';
                    $bk_stt_str .= '<font color="' . $app_list_strings['booking_status_color_list'][$bk_stt_value] . '"><b>' . $app_list_strings['booking_status_list'][$bk_stt_value] . '</b></font> (<span class="bk_detail" add="' . $row['ip_address'] . '" fdate="' . $from_date . '" tdate="' . $to_date . '" stt="' . $bk_stt_value . '">' . format_number($bk_cnt) . '</span>)';
                }

                // tính ngày đầu tiên xuất hiện
                $first_date = myGetDayBetween($row['first_date'], $to_date);

                if($first_date > 0) {
                    $first_date_txt = $first_date . ' ngày trước';
                } else {
                    $first_date_txt = 'hôm nay';
                }

                // tính ngày cuối xuất hiện
                $last_date = myGetDayBetween($row['last_date'], $to_date);
                if($last_date > 0) {
                    $last_date_txt = $last_date . ' ngày trước';
                } else {
                    $last_date_txt = 'hôm nay';
                }

                $html .= '
                    <tr>
                        <td class="text-center">' . ($i + 1) . '</td>
                        <td class="text-center">' . $row['ip_address'] . '</td>
                        <td class="text-center"><span class="bk_detail" add="' . $row['ip_address'] . '" fdate="' . $from_date . '" tdate="' . $to_date . '" stt="all">' . format_number($row['cnt']) . '</span></td>
                        <td class="text-center">' . $first_date_txt . '<br>' . date('d-m-Y', strtotime($row['first_date'])) . '</td>
                        <td class="text-center">' . $last_date_txt . '<br>' . date('d-m-Y', strtotime($row['last_date'])) . '</td>
                        <td class="text-center">' . format_number($row['booker1'] + $row['booker2']) . '</td>
                        <td class="text-center">' . format_number($row['bk_tt_amt']) . '</td>
                        <td class="hide-mobile">' . $bk_stt_str . '</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                <input type="button" class="ip_btn btn-allow btn btn-success" value="Allow">
                                <input type="button" add="' . $row['ip_address'] . '" class="ip_btn btn-deny btn btn-danger" value="Deny">
                            </div>
                        </td>
                    </tr>
                ';
                $i++;
            }
        }

        return $html;
    }
}
