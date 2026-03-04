<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewbkreport_telesale extends SugarView
{
    function display()
    {
        global $current_user;

        if (is_admin($current_user)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/' . $this->bean->object_name . '/tpls/bkreport_telesale.tpl');
        } else {
            header("Location: index.php?module$=" . $this->bean->object_name . "&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    public function populateContent($smartyobj)
    {
        // Từ ngày
        if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $from_date  = $_REQUEST['from_date'];
        } else {
            $from_date  = date('d-m-Y');
        }

        // Đến ngày
        if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $to_date = $_REQUEST['to_date'];
        } else {
            $to_date = date('d-m-Y');
        }

        switch (ceil(date('n') / 3)) {
            case 1:
                $quater_fromdate = '01-01-' . date('Y');
                $quater_todate = '31-03-' . date('Y');
                break;
            case 2:
                $quater_fromdate = '01-04-' . date('Y');
                $quater_todate = '30-06-' . date('Y');
                break;
            case 3:
                $quater_fromdate = '01-07-' . date('Y');
                $quater_todate = '30-09-' . date('Y');
                break;
            case 4:
                $quater_fromdate = '01-10-' . date('Y');
                $quater_todate = '31-12-' . date('Y');
                break;
            default:
                break;
        }

        $arr_date = array(
            '<option value="" fromdate="" todate="">---Trống---</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'daybefore' ? 'selected' : '') . ' value="daybefore" fromdate="' . date('d-m-Y', strtotime('-2 day')) . '" todate="' . date('d-m-Y', strtotime('-2 day')) . '">Hôm trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'current_week' ? 'selected' : '') . ' value="current_week" fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'previous_week' ? 'selected' : '') . ' value="previous_week" fromdate="' . date('d-m-Y', strtotime('monday previous week')) . '" todate="' . date('d-m-Y', strtotime('sunday previous week')) . '">Tuần trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'quater_this_month' ? 'selected' : '') . ' value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'quater_previous_month' ? 'selected' : '') . ' value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option ' . (isset($_POST['rc_date_select']) && (string)$_POST['rc_date_select'] === 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
        );

        $smartyobj->assign('MODULE_NAME', $this->bean->object_name);
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));
        $smartyobj->assign('FROM_DATE', $from_date);
        $smartyobj->assign('TO_DATE', $to_date);

        $data = $this->getReportRevenueTelesale($from_date, $to_date);
        $smartyobj->assign('DATA_TOTAL', $data['html_total']);
        $smartyobj->assign('DATA', $data['html_details']);
    }

    public function getReportRevenueTelesale($from_date, $to_date)
    {
        global $db, $current_user;

        $html = $html_total = '';
        $user_list = get_user_array(true, '', '', true);

        $sql = "SELECT
                b.id                 AS booking_id,
                b.name               AS booking_name,
                b.date_ticket_issue  AS date_ticket_issue,
                b.telesale_call_id   AS call_id,
                c.assigned_user_id   AS telesale_agent_id,
                c.name               AS call_name,
                c.date_entered       AS call_end_at,
                b.date_entered       AS booking_created_at,
                ROUND(TIMESTAMPDIFF(SECOND, c.date_entered, b.date_entered)) AS time_to_book
            FROM ec_flight_bookings b
            LEFT JOIN calls c ON c.id = b.telesale_call_id AND c.deleted = 0
            WHERE b.telesale_call_id IS NOT NULL
            AND b.is_telesale = 1
            AND b.booking_status IN ('7', '8', '3')
            AND b.date_ticket_issue BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
            AND b.deleted = 0
        ";

        $res    = $db->query($sql);
        $i      = 0;
        $total_amount     = 0;
        $total_purchase  = 0;
        $total_profit        = 0;
        while ($row = $db->fetchByAssoc($res)) {
            if (!empty($row['booking_id'])) {
                $info_bk = calculateBKAmt($row['booking_id']);

                // Khoảng thời gian đặt booking so với thời gian gọi
                if($row['time_to_book'] < 0) {
                    $time_to_book = '<span class="text-danger fw-semibold">Gọi sau khi đặt booking</span>';
                } else {
                    $time_to_book = seconds_to_ngay_hms($row['time_to_book']);
                }

                $html .= '<tr>
                            <td class="text-center hide-mobile">' . ($i + 1) . '</td>
                            <td class="text-center"><a target="_blank" title="Xem chi tiết" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking_name'] . '</a></td>
                            <td class="text-end total_amount">' . format_number($info_bk['total_amount']) . '</td>
                            <td class="text-end hide-mobile total_purchase">' . format_number($info_bk['total_purchase']) . '</td>
                            <td class="text-end hide-mobile total_profit">' . format_number($info_bk['total_profit']) . '</td>
                            <td class="text-end hide-mobile booking_created_at">' . $row['booking_created_at'] . '</td>
                            <td class="text-end hide-mobile date_ticket_issue">' . $row['date_ticket_issue'] . '</td>
                            <td class="text-end hide-mobile call_name"><a target="_blank" title="Xem chi tiết" href="index.php?module=Calls&action=DetailView&record=' . $row['call_id'] . '">' . $row['call_name'] . '</a></td>
                            <td class="text-end hide-mobile call_end_at">' . $row['call_end_at'] . '</td>
                            <td class="text-start hide-mobile telesale_agent">' . $user_list[$row['telesale_agent_id']] . '</td>
                            <td class="text-center hide-mobile time_to_book">' . $time_to_book . '</td>
                        </tr>
                    ';

                $total_amount += (int)$info_bk['total_amount'];
                $total_purchase += (int)$info_bk['total_purchase'];
                $total_profit += (int)$info_bk['total_profit'];
                $i++;
            }
        }

        $html_total = '<tr class="total">
                            <td class="text-center fw-semibold color-red total_amount">' . format_number($total_amount) . '</td>
                            <td class="text-center fw-semibold color-red total_purchase">' . format_number($total_purchase) . '</td>
                            <td class="text-center fw-semibold color-red total_profit">' . format_number($total_profit) . '</td>
                        </tr>';

        $arr['html_details'] = $html;
        $arr['html_total'] = $html_total;
        return $arr;
    }
}
