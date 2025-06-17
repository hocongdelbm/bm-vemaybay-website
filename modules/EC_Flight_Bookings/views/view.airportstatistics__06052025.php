<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewairportstatistics extends SugarView {
    function display() {
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

    function displayJS() {
		$js = '';
		$js .= '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.3/dist/chart.umd.min.js"></script>';
		$js .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.1.0"></script>';
		echo $js;
	}

    function populateContent($smartyobj) {
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
                        AND hv.ngayhachtoan >= "'. date('Y-m-d', strtotime($from_date)) .'"
                        AND hv.ngayhachtoan <= "'. date('Y-m-d', strtotime($to_date)) .'"
                WHERE ct.deleted = 0
                GROUP BY hv.id
            ) AS t';

        $res_return = $this->bean->db->query($sql_return);
        $return_inf = $this->bean->db->fetchByAssoc($res_return);

        $sql = "
            SELECT departure, arrival,
                SUM(bk_qty) AS bk_qty,
                SUM(total_ticket) AS total_ticket,
                SUM(total_profit) AS total_profit,
                GROUP_CONCAT(CONCAT_WS(',', departure, arrival, bk_qty, total_ticket, total_profit, airline_code) ORDER BY total_profit DESC, airline_code SEPARATOR '|') AS dt_line
            FROM (
                SELECT COUNT(b.id) AS bk_qty,
                    i.departure,
                    i.arrival,
                    i.airline_code,
                    (
                        SUM(b.total_amount) 
                      - SUM(
                            (
                                SELECT SUM(IFNULL(d.total_bought_price, 0))
                                FROM ec_booking_details d
                                WHERE d.booking_id = i.booking_id AND d.deleted = 0
                            )
                        ) 
                      - SUM((
                            SELECT SUM(IFNULL(IF(px.luggage_price > 0, px.luggage_purchase, 0),0)) + SUM(IFNULL(IF(px.luggage_price_inbound, luggage_purchase_inbound, 0),0)) FROM ec_booking_passengers px WHERE px.booking_id=b.id AND px.add_type IS NULL AND px.deleted=0
                        ))
                    ) AS total_profit,
                    SUM((SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = b.id AND deleted = 0)) AS total_ticket
                FROM ec_booking_itineraries i
                    LEFT JOIN ec_flight_bookings b ON b.id = i.booking_id AND b.deleted = 0
                WHERE b.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "'
                    AND b.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "'
                    AND b.booking_status IN (3, 7, 8)
                    AND i.direction = '0'
                    AND i.add_type = 0
                    AND i.deleted = 0
                    AND i.departure IN ('" . implode($airport, "','") . "')
                    AND i.arrival IN ('" . implode($airport, "','") . "')
                GROUP BY CONCAT(i.departure, i.arrival), i.airline_code
            ) AS t
            GROUP BY CONCAT(departure, arrival)
            ORDER BY total_profit DESC";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql);
        // }

        $res                = $db->query($sql);
        $html               = '';
        $i                  = 0;
        $total_qty          = 0;
        $total_ticket       = 0;
        $total_profit       = 0;
        $amount_arr         = array();

        $label_journey_arr  = array();
        $total_ticket_arr   = array();
        $js_data            = "[";
        $js_label           = "[";
        $js_total_ticket    = "[";
        $airport_arr        = $app_list_strings['domestic_airport_list'];
        
        $row_count  = $db->countRows($res);
        while ($row = $db->fetchByAssoc($res)) {

            $html .= '<tr class="main-line">
                <td class="text-center fw-bold">' . ($i + 1) . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['departure']] . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['arrival']] . '</td>
                <td class="text-center fw-bold">' . format_number($row['bk_qty']) . '</td>
                <td class="text-center fw-bold">' . format_number($row['total_ticket']) . '</td>
                <td class="text-center fw-bold"><b>' . format_number($row['total_profit']) . '</b></td>
                <td class="text-center fw-bold">$$' . ($i + 1) . '_percent%</td>
                <td class="text-center fw-bold"></td>
            </tr>';

            $label_journey_arr[($i + 1)] = $row['departure'] . ' - ' . $row['arrival'];
            $total_ticket_arr[($i + 1)]  = $row['total_ticket'];

            // hiện chi tiết theo hãng bay
            $dt_arr         = explode('|', $row['dt_line']);
            $amount_dt_arr  = array();

            for($k = 0; $k < count($dt_arr); $k++) {
                $dt_val = explode(',', $dt_arr[$k]);
                $html .= '<tr>
                    <td></td>
                    <td class="text-end">' . $airport_arr[$dt_val[0]] . '</td>
                    <td class="text-end">' . $airport_arr[$dt_val[1]] . '</td>
                    <td class="text-center">' . format_number($dt_val[2]) . '</td>
                    <td class="text-center">' . format_number($dt_val[3]) . '</td>
                    <td class="text-center">' . format_number($dt_val[4]) . '</td>
                    <td class="text-center">$$' . ($k + 1) . '_dt_percent%</td>
                    <td class="text-center">' . $app_list_strings['aircode_list'][$dt_val[5]] . '</td>
                </tr>';

                $amount_dt_arr['$$' . ($k + 1) . '_dt_percent'] = $dt_val[4];
            }

            foreach ($amount_dt_arr as $kdtamt => $vdtamt) {
                $html = str_replace($kdtamt, number_format($vdtamt / $row['total_profit'] * 100, 2), $html);
            }

            $total_qty      += $row['bk_qty'];
            $total_ticket   += $row['total_ticket'];
            $total_profit   += $row['total_profit'];
            $amount_arr['$$' . ($i + 1) . '_percent'] = $row['total_profit'];
            $i++;
        }

        $j = 1;
        $js_data_total = "[";
        foreach($amount_arr as $kamt => $vamt) {
            $html = str_replace($kamt, number_format($vamt / $total_profit * 100, 2), $html);

            $js_data_total .= "'".$vamt."',";
            if($j <= 10){
                $js_data .= "'".$vamt."',";
                $j++;
            }
        }

        // LẤY RA 10 HÀNH TRÌNH CÓ total_profit CAO NHẤT
        foreach($label_journey_arr as $key => $label){
            if($key <= 10){
                $js_label .= "'".$label."',";
            }
        }

        // LẤY RA số vé của 10 HÀNH TRÌNH CÓ total_profit CAO NHẤT
        foreach($total_ticket_arr as $key => $ticket){
            if($key <= 10){
                $js_total_ticket .= $ticket.",";
            }
        }

        // CHARTJS
        if($row_count > 0){
            $js_total_ticket_new = substr($js_total_ticket, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_ticket_new .= "]";
        
            $js_label_new = substr($js_label, 0, -1);
            $js_label_new .= "]";
        
            $data_total_profit_new = substr($js_data_total, 0, -1); 
            $data_total_profit_new .= "]";

            $js_data_new = substr($js_data, 0, -1);
            $js_data_new .= "]";
            echo '<script type="text/javascript">
                const label_journey = '.$js_label_new.';
                const data_journey = '.$js_data_new.';
                const data_total_profit_new = '.$data_total_profit_new.';
                const data_total_ticket = '.$js_total_ticket_new.';
            </script>';
            // END CHARTJS
        } else {
            echo '<script type="text/javascript">
                    const label_journey = [];
                    const data_journey = [];
                    const data_total_profit_new = [];
                    const data_total_ticket = [];
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
        $smartyobj->assign('TOTAL_PROFIT', format_number($total_profit + $return_inf['tt_return']));
        $smartyobj->assign('RETURN_BK', format_number($return_inf['tt_return_booking']));
        $smartyobj->assign('RETURN_TICKET', format_number($return_inf['tt_return_ticket']));
        $smartyobj->assign('RETURN_AMT', format_number($return_inf['tt_return']));

        // Vé quốc tế
        $html_inter = $this->populateBookingInter($from_date, $to_date);

        $smartyobj->assign('DATA_INTER', $html_inter['html_inter']);
        $smartyobj->assign('TOTAL_QTY_INTER', format_number($html_inter['total_qty']));
        $smartyobj->assign('TOTAL_TICKET_INTER', format_number($html_inter['total_ticket']));
        $smartyobj->assign('TOTAL_PROFIT_INTER', format_number($html_inter['total_profit']));

        // Nơi đặt vé
        // $data = $this->populateBookingLocation($from_date, $to_date);
        // $smartyobj->assign('DATA_LOCATION', $data['content']);
        // $smartyobj->assign('DATA_LOCATION_TOTAL', $data['total']);
    }

    function populateBookingInter($from_date, $to_date){
        global $db, $app_list_strings, $current_user;

        $airport_key_domestic    = array_keys($app_list_strings['domestic_airport_list']);
        $airport_arr             = array_merge($app_list_strings['domestic_airport_list'], $app_list_strings['southeast_asia_airport_list'], $app_list_strings['northeast_asia_airport_list'], $app_list_strings['europe_airport_list'], $app_list_strings['americas_airport_list'], $app_list_strings['australia_airport_list'], $app_list_strings['africa_airport_list']);

        $sql_inter = "
            SELECT departure, arrival,
                SUM(bk_qty) AS bk_qty,
                SUM(total_ticket) AS total_ticket,
                SUM(total_profit) AS total_profit,
                GROUP_CONCAT(CONCAT_WS(',', departure, arrival, bk_qty, total_ticket, total_profit, airline_code) ORDER BY total_profit DESC, airline_code SEPARATOR '|') AS dt_line
            FROM (
                SELECT COUNT(b.id) AS bk_qty,
                    i.departure,
                    i.arrival,
                    i.airline_code,
                    (
                        SUM(b.total_amount) 
                      - SUM(
                            (
                                SELECT SUM(IFNULL(d.total_bought_price, 0))
                                FROM ec_booking_details d
                                WHERE d.booking_id = i.booking_id AND d.deleted = 0
                            )
                        ) 
                      - SUM((
                            SELECT SUM(IFNULL(IF(px.luggage_price > 0, px.luggage_purchase, 0),0)) + SUM(IFNULL(IF(px.luggage_price_inbound, luggage_purchase_inbound, 0),0)) FROM ec_booking_passengers px WHERE px.booking_id=b.id AND px.add_type IS NULL AND px.deleted=0
                        ))
                    ) AS total_profit,
                    SUM((SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = b.id AND deleted = 0)) AS total_ticket
                FROM ec_booking_itineraries i
                    LEFT JOIN ec_flight_bookings b ON b.id = i.booking_id AND b.deleted = 0
                WHERE b.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "'
                    AND b.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "'
                    AND b.booking_status IN (3, 7, 8)
                    AND i.direction = '0'
                    AND i.add_type = 0
                    AND i.deleted = 0
                    AND i.transit_order = 0
                    AND (i.departure NOT IN ('" . implode($airport_key_domestic, "','") . "') OR i.arrival NOT IN ('" . implode($airport_key_domestic, "','") . "'))
                GROUP BY CONCAT(i.departure, i.arrival), i.airline_code
            ) AS t
            GROUP BY CONCAT(departure, arrival)
            ORDER BY total_profit DESC";

        // if($current_user->user_name == 'hungnh'){
        //     pr($sql_inter);
        // }

        $res = $db->query($sql_inter);
        $row_count = $db->countRows($res);

        $total_qty      = 0;
        $total_ticket   = 0;
        $total_profit   = 0;
        $amount_arr  = array();

        // chartjs
        $label_journey_inter_arr  = array();
        $total_ticket_inter_arr   = array();
        $js_data_inter            = "[";
        $js_label_inter           = "[";
        $js_total_ticket_inter    = "[";
        
        $html = '';
        $i    = 0;

        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr class="main-inter-line">
                <td class="text-center fw-bold">' . ($i + 1) . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['departure']] . '</td>
                <td class="text-center fw-bold">' . $airport_arr[$row['arrival']] . '</td>
                <td class="text-center fw-bold">' . format_number($row['bk_qty']) . '</td>
                <td class="text-center fw-bold">' . format_number($row['total_ticket']) . '</td>
                <td class="text-center fw-bold"><b>' . format_number($row['total_profit']) . '</b></td>
                <td class="text-center fw-bold">$$' . ($i + 1) . '_percent%</td>
                <td class="text-center fw-bold"></td>
            </tr>';

            $label_journey_inter_arr[($i + 1)] = $row['departure'] . ' - ' . $row['arrival'];
            $total_ticket_inter_arr[($i + 1)]  = format_number($row['total_ticket']);

            // hiện chi tiết theo hãng bay
            $dt_arr         = explode('|', $row['dt_line']);
            $amount_dt_arr  = array();

            for($k = 0; $k < count($dt_arr); $k++) {
                $dt_val = explode(',', $dt_arr[$k]);
                $html .= '<tr>
                    <td></td>
                    <td class="text-end">' . $airport_arr[$dt_val[0]] . '</td>
                    <td class="text-end">' . $airport_arr[$dt_val[1]] . '</td>
                    <td class="text-center">' . format_number($dt_val[2]) . '</td>
                    <td class="text-center">' . format_number($dt_val[3]) . '</td>
                    <td class="text-center">' . format_number($dt_val[4]) . '</td>
                    <td class="text-center">$$' . ($k + 1) . '_dt_percent%</td>
                    <td class="text-center">' . $GLOBALS['app_list_strings']['ma_hang'][$dt_val[5]] . '</td>
                </tr>';

                $amount_dt_arr['$$' . ($k + 1) . '_dt_percent'] = $dt_val[4];
            }

            foreach ($amount_dt_arr as $kdtamt => $vdtamt) {
                $html = str_replace($kdtamt, number_format($vdtamt / $row['total_profit'] * 100, 2), $html);
            }

            $total_qty      += $row['bk_qty'];
            $total_ticket   += $row['total_ticket'];
            $total_profit   += $row['total_profit'];
            $amount_arr['$$' . ($i + 1) . '_percent'] = $row['total_profit'];
            $i++;
        }

        $j = 1;
        $js_data_total_inter = "[";
        foreach($amount_arr as $kamt => $vamt) {
            $html = str_replace($kamt, number_format($vamt / $total_profit * 100, 2), $html);

            $js_data_total_inter .= "'".$vamt."',";
            if($j <= 10){
                $js_data_inter .= "'".$vamt."',";
                $j++;
            }
        }

         // LẤY RA 10 HÀNH TRÌNH CÓ total_profit CAO NHẤT
         foreach($label_journey_inter_arr as $key => $label){
            if($key <= 10){
                $js_label_inter .= "'".$label."',";
            }
        }

        // LẤY RA số vé của 10 HÀNH TRÌNH CÓ total_profit CAO NHẤT
        foreach($total_ticket_inter_arr as $key => $ticket){
            if($key <= 10){
                $js_total_ticket_inter .= $ticket.",";
            }
        }

        // CHARTJS - INTER
        if($row_count > 0){
            $js_total_ticket_new = substr($js_total_ticket_inter, 0, -1); //Loại bỏ dấu , của element cuối cùng
            $js_total_ticket_new .= "]";
            
            $js_label_new = substr($js_label_inter, 0, -1);
            $js_label_new .= "]";
            
            $data_total_profit_new = substr($js_data_total_inter, 0, -1); 
            $data_total_profit_new .= "]";
    
            $js_data_new = substr($js_data_inter, 0, -1);
            $js_data_new .= "]";
            echo '<script type="text/javascript">
                const label_journey_inter = '.$js_label_new.';
                const data_journey_inter = '.$js_data_new.';
                const data_total_profit_new_inter = '.$data_total_profit_new.';
                const data_total_ticket_inter = '.$js_total_ticket_new.';
            </script>';
            // END CHARTJS
        } else {
            echo '<script type="text/javascript">
                const label_journey_inter = [];
                const data_journey_inter = [];
                const data_total_profit_new_inter = [];
                const data_total_ticket_inter = [];
            </script>';
        }

        return array(
                "html_inter" => $html, 
                "total_qty" => $total_qty,
                "total_ticket" => $total_ticket,
                "total_profit" => $total_profit,
            );

    }

    function populateBookingLocation($from_date, $to_date) {
        global $db;
        $sql = "
            SELECT country as location,
                COUNT(id) AS booking_quantity,
                SUM(total_qty) AS ticket_quantity,
                SUM(total_profit) AS total_profit
            FROM (
                SELECT id,
                    country,
                    total_qty,
                    (
                        b.total_amount - b.luggage_fee
                        - (
                            SELECT SUM(IFNULL(d.total_bought_price, 0))
                            FROM ec_booking_details d
                            WHERE d.booking_id = b.id AND d.deleted = 0
                        ) 
                    ) AS total_profit
                FROM ec_flight_bookings b
                WHERE b.date_ticket_issue >= '" . date('Y-m-d', strtotime($from_date)) . "'
                    AND b.date_ticket_issue <= '" . date('Y-m-d', strtotime($to_date)) . "'
                    AND b.booking_status IN (3, 7, 8)
                    AND b.deleted = 0
            ) as t
            GROUP BY country
            ORDER BY total_profit DESC
        ";
        $i              = 0;
        $html           = "";
        $arr_percent    = array();
        $total = array(
            'booking'   => 0,
            'ticket'    => 0,
            'profit'    => 0
        );

        $js_label_location = "[";
        $js_profit_location = "[";
        $js_ticket_location = "[";

        $response = $db->query($sql);
        while ($row = $db->fetchByAssoc($response)) {
            $html .= '<tr class="">
                <td class="text-center">' . ($i + 1) . '</td>
                <td class="text-center">' . $row['location'] . '</td>
                <td class="text-center">' . format_number($row['booking_quantity']) . '</td>
                <td class="text-center">' . format_number((int)$row['ticket_quantity']) . '</td>
                <td class="text-end">' . format_number($row['total_profit']) . '</td>
                <td class="text-end">%percent' .  ($i + 1) . '%</td>
            </tr>';

            $label_location_arr[($i + 1)] = $row['location'];
            $ticket_quantity_arr[($i + 1)] = (int)$row['ticket_quantity'];

            $arr_percent['%percent' .  ($i + 1)] = $row['total_profit'];
            $total['booking'] += $row['booking_quantity'];
            $total['ticket']  += $row['ticket_quantity'];
            $total['profit']  += $row['total_profit'];
            $i++;
        }

        // LẤY RA 5 location CÓ total_profit CAO NHẤT
        foreach($label_location_arr as $key => $label){
            if($key <= 5){
                $js_label_location .= "'".$label."',";
            }
        }

        // LẤY RA số vé của 5 location CÓ total_profit CAO NHẤT
        foreach($ticket_quantity_arr as $key => $ticket){
            if($key <= 5){
                $js_ticket_location .= "'".$ticket."',";
            }
        }

        $j = 1;
        $js_total_profit_location = "[";
        foreach($arr_percent as $key => $value) {
            $html = str_replace($key, number_format($value / $total['profit'] * 100, 2), $html);

            $js_total_profit_location .= "'".$value."',";
            if($j <= 5){
                $js_profit_location .= "'".$value."',";
                $j++;
            }
        }

        $html_total = '<tr class="total-line footer-tr">
            <th colspan="2" class="text-center text-uppercase fw-bold">Tổng cộng</th>
            <th class="text-center fw-bold">' . format_number($total['booking']) . '</th>
            <th class="text-center fw-bold">' . format_number((int)$total['ticket']) . '</th>
            <th class="text-end fw-bold">' . format_number($total['profit']) . '</th>
            <th class="text-end fw-bold">100%</th>
        </tr>';

        // CHARTJS
        $label_location = substr($js_label_location, 0, -1); //Loại bỏ dấu , của element cuối cùng
		$label_location .= "]";
        
        $data_profit_location = substr($js_profit_location, 0, -1); //Loại bỏ dấu , của element cuối cùng
		$data_profit_location .= "]";
       
        $data_ticket_location = substr($js_ticket_location, 0, -1); //Loại bỏ dấu , của element cuối cùng
		$data_ticket_location .= "]";
        
        $data_total_profit_location = substr($js_total_profit_location, 0, -1); //Loại bỏ dấu , của element cuối cùng
		$data_total_profit_location .= "]";

        echo '<script type="text/javascript">
			const label_location = '.$label_location.';
			const data_profit_location = '.$data_profit_location.';
			const data_ticket_location = '.$data_ticket_location.';
			const data_total_profit_location = '.$data_total_profit_location.';
		</script>';

        return array("content" => $html, "total" => $html_total);
    }
}
