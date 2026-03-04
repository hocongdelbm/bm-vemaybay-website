<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewairportstatistics extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->displayJS();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_airport_statistics.tpl');
        } else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này!"));
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

    function populateContent($smartyobj)
    {
        global $db, $app_list_strings, $current_user;

        // $from_date  = date('d-m-Y', strtotime('first day of this month'));
        // $from_date  = date('d-m-Y', strtotime('-7 day'));
        $from_date    = date('d-m-Y');
        $to_date    = date('d-m-Y');

        $airport = array_keys($app_list_strings['domestic_airport_list']); // Key
        $booking_status = array_keys($app_list_strings['booking_status_list']);

        if (!empty($_POST['from_date'])) {
            $from_date = $_POST['from_date'];
        }
        if (!empty($_POST['to_date'])) {
            $to_date = $_POST['to_date'];
        }
        if (!empty($_POST['airport'])) {
            $airport = $_POST['airport'];
        }
        if (!empty($_POST['booking_status'])) {
            $booking_status = $_POST['booking_status'];
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
            '<option value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
            '<option value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
            '<option value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
            '<option value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
            '<option value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
            '<option value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
            '<option value="2previous_year" fromdate="' . date('01-01-Y', strtotime('-2 year')) . '" todate="' . date('31-12-Y', strtotime('-2 year')) . '">2 năm trước</option>',
        );
        // $smartyobj->assign('TEST_DATE', date('d-m-Y H:i:s'));
        $smartyobj->assign('DATE_OPTION', implode('', $arr_date));

        // OTPION DATE - RADIO
        $smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 day')));
        $smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 day')));
        $smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));

        // Đếm số vé hoàn trong ngày tìm kiếm
        $sql_return = '
            SELECT SUM(tt_return_ticket) AS tt_return_ticket,
                COUNT(booking_id) AS tt_return_booking,
                SUM(return_amt) AS tt_return
            FROM (
                SELECT COUNT(ct.id) AS tt_return_ticket,
                    hv.booking_id,
                    (SUM(IFNULL(hv.tongtiendv,0)) / COUNT(ct.id)) AS return_amt
                FROM ec_chitiethoanve ct
                    INNER JOIN ec_hoanve hv ON hv.id = ct.hoanve_id
                        AND hv.tinhtrang = 1 
                        AND hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '"
                        AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
                WHERE ct.deleted = 0
                GROUP BY hv.id
            ) AS t';

        $res_return = $this->bean->db->query($sql_return);
        $return_inf = $this->bean->db->fetchByAssoc($res_return);

        $sql = "
            SELECT
                departure,
                arrival,
                SUM(bk_qty) AS bk_qty,
                SUM(total_ticket) AS total_ticket,
                GROUP_CONCAT(CONCAT_WS(',', departure, arrival, bk_qty, total_ticket, airline_code) ORDER BY bk_qty DESC, airline_code SEPARATOR '|') AS dt_line
                FROM
                (
                    SELECT
                        COUNT(b.id) AS bk_qty,
                        i.departure,
                        i.arrival,
                        i.airline_code,
                        SUM((SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = b.id AND deleted = 0)) AS total_ticket
                    FROM
                        ec_booking_itineraries i
                        LEFT JOIN ec_flight_bookings b ON b.id = i.booking_id
                        AND b.deleted = 0
                    WHERE DATE_FORMAT(DATE_ADD(b.date_entered, INTERVAL 7 HOUR), '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                        AND i.direction = 0
                        AND i.add_type = 0
                        AND i.deleted = 0
                        AND i.departure IN ('" . implode($airport, "','") . "')
                        AND i.arrival IN ('" . implode($airport, "','") . "')
                    GROUP BY
                        CONCAT(i.departure, i.arrival),
                        i.airline_code
                ) AS t
                GROUP BY CONCAT(departure, arrival)
                ORDER BY bk_qty DESC";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res                = $db->query($sql);
        $html               = '';
        $i                  = 0;
        $total_qty          = 0;
        $total_ticket       = 0;

        $label_journey_arr  = array();
        $total_ticket_arr   = array();
        $total_qty_arr      = array();

        $js_label           = "[";
        $js_total_ticket    = "[";
        $js_total_bk_qty    = "[";
        $airport_arr        = $app_list_strings['domestic_airport_list'];

        $row_count  = $db->countRows($res);
        while ($row = $db->fetchByAssoc($res)) {
            $departure = $row['departure'];
            $arrival = $row['arrival'];

            $html .= '<tr class="main-line">
                <td class="text-center fw-bold">' . ($i + 1) . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$departure] . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$arrival] . '</td>
                <td class="text-center fw-bold">
                    <a href="#" class="text-primary text-decoration-underline" data-bs-toggle="modal" data-bs-target="#mainLineModal" data-fromdate="'.$from_date.'" data-todate="'.$to_date.'" data-departure="'.$departure.'" data-arrival="'.$arrival.'">
                        ' . format_number($row['bk_qty']) . '
                    </a>
                </td>
                <td class="text-center fw-bold">' . format_number($row['total_ticket']) . '</td>
                <td class="text-center fw-bold"></td>
            </tr>';

            $label_journey_arr[($i + 1)] = $departure . ' - ' . $arrival;
            $total_ticket_arr[($i + 1)]  = $row['total_ticket'];
            $total_qty_arr[($i + 1)]  = $row['bk_qty'];

            // hiện chi tiết theo hãng bay
            $dt_arr         = explode('|', $row['dt_line']);
            for ($k = 0; $k < count($dt_arr); $k++) {
                $dt_val = explode(',', $dt_arr[$k]);
                $html .= '<tr>
                    <td></td>
                    <td class="text-end">' . $airport_arr[$dt_val[0]] . '</td>
                    <td class="text-end">' . $airport_arr[$dt_val[1]] . '</td>
                    <td class="text-center">
                        ' . format_number($dt_val[2]) . '
                    </td>
                    <td class="text-center">' . format_number($dt_val[3]) . '</td>
                    <td class="text-center">' . $app_list_strings['aircode_list'][$dt_val[4]] . '</td>
                </tr>';
            }

            $total_qty      += $row['bk_qty'];
            $total_ticket   += $row['total_ticket'];
            $i++;
        }

        // LẤY RA 10 HÀNH TRÌNH
        foreach ($label_journey_arr as $key => $label) {
            if ($key <= 10) {
                $js_label .= "'" . $label . "',";
            }
        }

        // LẤY RA số vé của 10 HÀNH TRÌNH
        foreach ($total_ticket_arr as $key => $ticket) {
            if ($key <= 10) {
                $js_total_ticket .= $ticket . ",";
            }
        }

        // LẤY RA số bk của 10 HÀNH TRÌNH
        foreach ($total_qty_arr as $key => $qty) {
            if ($key <= 10) {
                $js_total_bk_qty .= $qty . ",";
            }
        }

        // CHARTJS
        if ($row_count > 0) {
            $js_total_ticket_new = substr($js_total_ticket, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_ticket_new .= "]";

            $js_total_bkqty_new = substr($js_total_bk_qty, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_bkqty_new .= "]";

            $js_label_new = substr($js_label, 0, -1);
            $js_label_new .= "]";

            echo '<script type="text/javascript">
                const label_journey = ' . $js_label_new . ';
                const data_total_ticket = ' . $js_total_ticket_new . ';
                const data_total_bkqty = ' . $js_total_bkqty_new . ';
            </script>';
            // END CHARTJS
        } else {
            echo '<script type="text/javascript">
                    const label_journey = [];
                    const data_total_ticket = [];
                    const data_total_bkqty = [];
                </script>';
        }

        $smartyobj->assign('FROM_DATE', $from_date);
        $smartyobj->assign('TO_DATE', $to_date);
        $smartyobj->assign('AIRPORT', get_select_options_with_id($airport_arr, $airport));
        $smartyobj->assign('BOOKING_STATUS', get_select_options_with_id($app_list_strings['booking_status_list'], $booking_status));
        $smartyobj->assign('DATA', $html);
        $smartyobj->assign('TOTAL_ROW', $i);
        $smartyobj->assign('TOTAL_QTY', format_number($total_qty));
        $smartyobj->assign('TOTAL_TICKET', format_number($total_ticket - $return_inf['tt_return_ticket']));
        $smartyobj->assign('RETURN_BK', format_number($return_inf['tt_return_booking']));
        $smartyobj->assign('RETURN_TICKET', format_number($return_inf['tt_return_ticket']));
        $smartyobj->assign('RETURN_AMT', format_number($return_inf['tt_return']));

        // Vé quốc tế
        $html_inter = $this->populateBookingInter($from_date, $to_date);

        $smartyobj->assign('DATA_INTER', $html_inter['html_inter']);
        $smartyobj->assign('TOTAL_QTY_INTER', format_number($html_inter['total_qty']));
        $smartyobj->assign('TOTAL_TICKET_INTER', format_number($html_inter['total_ticket']));
    }

    function populateBookingInter($from_date, $to_date)
    {
        global $db, $app_list_strings, $current_user;

        $airport_key_domestic    = array_keys($app_list_strings['domestic_airport_list']);
        $airport_arr             = array_merge($app_list_strings['domestic_airport_list'], $app_list_strings['southeast_asia_airport_list'], $app_list_strings['northeast_asia_airport_list'], $app_list_strings['europe_airport_list'], $app_list_strings['americas_airport_list'], $app_list_strings['australia_airport_list'], $app_list_strings['africa_airport_list']);

        $sql_inter = "
            SELECT departure, arrival,
                SUM(bk_qty) AS bk_qty,
                SUM(total_ticket) AS total_ticket,
                GROUP_CONCAT(CONCAT_WS(',', departure, arrival, bk_qty, total_ticket, airline_code) ORDER BY bk_qty DESC, airline_code SEPARATOR '|') AS dt_line
            FROM (
                SELECT COUNT(b.id) AS bk_qty,
                    i.departure,
                    i.arrival,
                    i.airline_code,
                    SUM((SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = b.id AND deleted = 0)) AS total_ticket
                FROM ec_booking_itineraries i
                    LEFT JOIN ec_flight_bookings b ON b.id = i.booking_id AND b.deleted = 0
                WHERE DATE_FORMAT(DATE_ADD(b.date_entered, INTERVAL 7 HOUR), '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($from_date)) . "' AND '" . date('Y-m-d', strtotime($to_date)) . "'
                    AND i.direction = 0
                    AND i.add_type = 0
                    AND i.deleted = 0
                    AND i.transit_order = 0
                    AND (i.departure NOT IN ('" . implode($airport_key_domestic, "','") . "') OR i.arrival NOT IN ('" . implode($airport_key_domestic, "','") . "'))
                GROUP BY CONCAT(i.departure, i.arrival), i.airline_code
            ) AS t
            GROUP BY CONCAT(departure, arrival)
            ORDER BY bk_qty DESC";

        $res = $db->query($sql_inter);
        $row_count = $db->countRows($res);

        $total_qty      = 0;
        $total_ticket   = 0;

        // chartjs
        $label_journey_inter_arr  = array();
        $total_ticket_inter_arr   = array();
        $total_bkqty_inter_arr   = array();

        $js_label_inter           = "[";
        $js_total_ticket_inter    = "[";
        $js_total_bkqty_inter    = "[";

        $html = '';
        $i    = 0;

        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr class="main-inter-line">
                <td class="text-center fw-bold">' . ($i + 1) . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['departure']] . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['arrival']] . '</td>
                <td class="text-center fw-bold">' . format_number($row['bk_qty']) . '</td>
                <td class="text-center fw-bold">' . format_number($row['total_ticket']) . '</td>
                <td class="text-center fw-bold"></td>
            </tr>';

            $label_journey_inter_arr[($i + 1)] = $row['departure'] . ' - ' . $row['arrival'];
            $total_ticket_inter_arr[($i + 1)]  = format_number($row['total_ticket']);
            $total_bkqty_inter_arr[($i + 1)]  = format_number($row['bk_qty']);

            // hiện chi tiết theo hãng bay
            $dt_arr         = explode('|', $row['dt_line']);

            for ($k = 0; $k < count($dt_arr); $k++) {
                $dt_val = explode(',', $dt_arr[$k]);
                $html .= '<tr>
                    <td></td>
                    <td class="text-end">' . $airport_arr[$dt_val[0]] . '</td>
                    <td class="text-end">' . $airport_arr[$dt_val[1]] . '</td>
                    <td class="text-center">' . format_number($dt_val[2]) . '</td>
                    <td class="text-center">' . format_number($dt_val[3]) . '</td>
                    <td class="text-center">' . $GLOBALS['app_list_strings']['ma_hang'][$dt_val[4]] . '</td>
                </tr>';
            }

            $total_qty      += $row['bk_qty'];
            $total_ticket   += $row['total_ticket'];
            $i++;
        }

        // LẤY RA 10 HÀNH TRÌNH
        foreach ($label_journey_inter_arr as $key => $label) {
            if ($key <= 10) {
                $js_label_inter .= "'" . $label . "',";
            }
        }

        // LẤY RA số vé của 10 HÀNH TRÌNH
        foreach ($total_ticket_inter_arr as $key => $ticket) {
            if ($key <= 10) {
                $js_total_ticket_inter .= $ticket . ",";
            }
        }

        // LẤY RA số bk của 10 HÀNH TRÌNH
        foreach ($total_bkqty_inter_arr as $key => $bk) {
            if ($key <= 10) {
                $js_total_bkqty_inter .= $bk . ",";
            }
        }

        // CHARTJS - INTER
        if ($row_count > 0) {
            $js_total_ticket_new = substr($js_total_ticket_inter, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_ticket_new .= "]";

            $js_total_bkqty_new = substr($js_total_bkqty_inter, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_bkqty_new .= "]";

            $js_label_new = substr($js_label_inter, 0, -1);
            $js_label_new .= "]";

            echo '<script type="text/javascript">
                const label_journey_inter = ' . $js_label_new . ';
                const data_total_ticket_inter = ' . $js_total_ticket_new . ';
                const data_total_bkqty_inter = ' . $js_total_bkqty_new . ';
            </script>';
            // END CHARTJS
        } else {
            echo '<script type="text/javascript">
                const label_journey_inter = [];
                const data_total_ticket_inter = [];
                const data_total_bkqty_inter = [];
            </script>';
        }

        return array(
            "html_inter" => $html,
            "total_qty" => $total_qty,
            "total_ticket" => $total_ticket,
        );
    }
}
