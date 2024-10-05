<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewticketreport extends SugarView
{
    private $_is_allow_recheck = true;
    var $_loai_thu_str = "'4', '5', '10', '11', '12', '13', '14', '16'";

    function __construct()
    {
        global $current_user;
        if (is_admin($current_user) || (ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && ACLController::checkAccess('Bugs', 'list', true))
        ) {
            $this->_is_allow_recheck = true;
        }
    }

    function display()
    {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)) {
            $smartyCont = new Sugar_Smarty();
            $this->populateContent($smartyCont);
            $smartyCont->display('modules/EC_TongHop/tpls/view_ticket_report.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function populateContent($smartyobj)
    {
        global $app_list_strings, $current_user;

        $sql_search = "";
        // Từ ngày
        if (!empty($_REQUEST['from_date']) && strtotime($_REQUEST['from_date']) !== false) {
            $sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d', strtotime($_REQUEST['from_date'])) . "' ";
            $post_from_date = $_REQUEST['from_date'];
        } else {
            $sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d') . "' ";
            $post_from_date = date('d-m-Y');
        }

        // Đến ngày
        if (!empty($_REQUEST['to_date']) && strtotime($_REQUEST['to_date']) !== false) {
            $sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d', strtotime($_REQUEST['to_date'])) . "' ";
            $post_to_date = $_REQUEST['to_date'];
        } else {
            $sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d') . "' ";
            $post_to_date = date('d-m-Y');
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
		$smartyobj->assign('DATE_OPTION', implode('', $arr_date));

        // RADIO
        $smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 days')));
		$smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 days')));
        $smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('CURRENT_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('CURRENT_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVIOUS_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVIOUS_TODATE', date('t-m-Y', strtotime('last day of last month')));
        $smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
		$smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));

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
        $smartyobj->assign('CURRENT_QUARTER_FROMDATE', $quater_fromdate);
        $smartyobj->assign('CURRENT_QUARTER_TODATE', $quater_todate);

        // Check if report over 31 days
        $days_diff = (abs(strtotime($post_to_date) - strtotime($post_from_date)) / 60 / 60 / 24) + 1;
        if (!is_admin($current_user) && $days_diff > 31) {
            echo '<p class="error">Vui lòng chọn trong khoảng thời gian 31 ngày</p>';
            exit;
        }

        // Check permission
        // chỉ kế toán trưởng hoặc admin hệ thống mới được xem hết, còn lại xem của mình
        $sql_manager = '
            SELECT COUNT(id) 
            FROM acl_roles_users 
            WHERE 
                user_id = "' . $current_user->id . '"
                AND role_id IN (
                    "'.$GLOBALS['app_list_strings']['roles_users']['QUANLY'].'",
                    "'.$GLOBALS['app_list_strings']['roles_users']['KETOAN'].'"
                )
                AND deleted = 0';

        $is_manager = $this->bean->db->getOne($sql_manager);
        $sql_role = "";
        if (!$is_manager && !is_admin($current_user)) {
            $sql_role .= " AND bk.assigned_user_id='" . $current_user->id . "' ";
        }

        // $full_report_titles = array_keys($app_list_strings['user_title_allow_full_report']);
        // $user_title = strtolower(preg_replace('/\W/', '', $current_user->title));
        // $user_id = !empty($_REQUEST['uid']) && is_guid($_REQUEST['uid']) ? $_REQUEST['uid'] : '';
        // $sql_role = "";
        // if (in_array($user_title, $full_report_titles)) {
        //     if(!empty($user_id)){
        //         $sql_role .= " AND bk.assigned_user_id='" . $user_id . "' ";
        //     } else {
        //         $sql_role .= "";
        //     }
        // } else {
        //     if ($user_id != $current_user->id) {
        //         $user_id = $current_user->id;
        //     }
        //     $sql_role .= " AND bk.assigned_user_id='" . $user_id . "' ";
        // }

        // routing
        $data = '';
        $post_group_by = !empty($_REQUEST['group_by']) ? $_REQUEST['group_by'] : 'booking';
        $payment_stt = isset($_POST['payment_stt']) ? $_POST['payment_stt'] : 0;

        switch ($post_group_by) {
            case 'booking':
                $data = $this->bookingQuery($sql_search, $sql_role, $post_from_date, $post_to_date, array('payment_stt' => $payment_stt));
                break;
            case 'airline_code':
                $data = $this->airlineCodeQuery($sql_search, $sql_role, $post_from_date, $post_to_date);
                break;
            case 'ticket_class':
                $data = $this->ticketClassQuery($sql_search, $sql_role);
                break;
            case 'itinerary':
                $data = $this->itineraryQuery($sql_search, $sql_role);
                break;
        }

        $smartyobj->assign('DATA', (is_array($data) ? $data['html'] : $data));
        $smartyobj->assign('DATA_TOTAL', $data['html_total']);
        $smartyobj->assign('FROM_DATE_VALUE', $post_from_date);
        $smartyobj->assign('TO_DATE_VALUE', $post_to_date);

        $smartyobj->assign('CHECKED_1', !empty($_REQUEST['group_by']) && $_REQUEST['group_by'] == 'booking' ? 'checked="checked"' : 'checked="checked"');
        $smartyobj->assign('CHECKED_2', !empty($_REQUEST['group_by']) && $_REQUEST['group_by'] == 'airline_code' ? 'checked="checked"' : '');
        $smartyobj->assign('CHECKED_3', !empty($_REQUEST['group_by']) && $_REQUEST['group_by'] == 'ticket_class' ? 'checked="checked"' : '');
        $smartyobj->assign('CHECKED_4', !empty($_REQUEST['group_by']) && $_REQUEST['group_by'] == 'itinerary' ? 'checked="checked"' : '');

        $smartyobj->assign('ALLOWED_EXPORT', ACLController::checkAccess('EC_Payment_Voucher', 'edit', true));
        $smartyobj->assign('IS_ALLOW_RECHECK', $this->_is_allow_recheck);

        // tìm nhanh khoảng thời gian
        $smartyobj->assign('TODAY', date('d-m-Y'));
        $smartyobj->assign('YESTERDAY', date('d-m-Y', strtotime('-1 day')));
        $smartyobj->assign('THISWEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
        $smartyobj->assign('THISWEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
        $smartyobj->assign('PREVWEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
        $smartyobj->assign('PREVWEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));
        $smartyobj->assign('THISMONTH_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
        $smartyobj->assign('THISMONTH_TODATE', date('d-m-Y', strtotime('last day of this month')));
        $smartyobj->assign('PREVMONTH_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
        $smartyobj->assign('PREVMONTH_TODATE', date('d-m-Y', strtotime('last day of last month')));

        // Tình trạng thu
        $payment_status = array(
            '0' => 'Tất cả',
            '1' => 'Chưa thu',
            '2' => 'Chưa thu đủ',
        );

        $smartyobj->assign('PAYMENT_STT', get_select_options_with_id($payment_status, (int)$payment_stt));

        // Xuất excel
        if (isset($_REQUEST['btnExport'])) {
            ob_clean();
            header("Pragma: cache");
            require_once('modules/EC_Flight_Bookings/views/doanhthubooking.xls.php');
            $xls = generateXLSTemplate($data['xls'], $post_from_date, $post_to_date);
            $xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
            header("Content-type: application/x-msdownload");
            header("Content-disposition: xls; filename=doanhthubooking_" . time() . ".xls; size=" . strlen($xls));
            echo $xls;
            exit();
        }
    }

    // Thống kê doanh thu theo chặng bay
    function itineraryQuery($sql_search, $sql_role)
    {
        global $db;
        $sql = "SELECT (SELECT CONCAT(departure,'-',arrival) FROM ec_booking_itineraries WHERE booking_id = bk.id AND direction = bkd.direction LIMIT 0,1) AS itinerary_code
					  ,(SELECT CONCAT(cti1.name,' - ',cti2.name) 
					  	FROM ec_booking_itineraries iti 
						LEFT JOIN ec_flight_cities cti1 ON iti.departure = cti1.code AND cti1.deleted = 0
						LEFT JOIN ec_flight_cities cti2 ON iti.arrival = cti2.code AND cti2.deleted = 0
						WHERE iti.booking_id = bk.id AND iti.direction = bkd.direction LIMIT 0,1) AS itinerary_name
					  ,SUM(bkd.quantity) AS total_quantity
					  ,SUM(bkd.total_price) AS subtotal_amount
				FROM ec_booking_details bkd
				LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id
				WHERE 
                    ( bk.booking_status = '7' OR bk.booking_status = '8' )
                    AND bk.deleted = 0 AND bkd.deleted = 0
                    " . $sql_search . $sql_role . "
				GROUP BY itinerary_code
				ORDER BY total_quantity DESC ";
        $res = $db->query($sql);
        $res2 = $db->query($sql); // dùng cho tính toán phần trăm

        $qty = 0;
        $amt = 0;
        while ($row2 = $db->fetchByAssoc($res2)) {
            $qty += $row2['total_quantity'];
            $amt += $row2['subtotal_amount'];
        }

        $i = 0;
        $html = '';
        $total_quantity = 0;
        $subtotal_amount = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr style="background:#fff" >';
            $html .= '<td class="text-center">' . ($i + 1) . '</td>';
            $html .= '<td style="text-align:left;">' . $row['itinerary_name'] . '</td>';
            $html .= '<td class="text-center">' . format_number($row['total_quantity']) . '</td>';
            $html .= '<td class="text-end">' . format_number($row['subtotal_amount']) . '</td>';

            // tính phần trăm
            if ($amt > 0)
                $percent = $row['subtotal_amount'] > 0 ? $row['subtotal_amount'] * 100 / $amt : 0;
            $pixel = (int)($percent * 120 / 100);
            $div_percent = '<div style="width:' . $pixel . 'px; height:10px; background:#09F">&nbsp;</div>';

            $html .= '<td class="text-end"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="90%">' . $div_percent . '</td><td align="right">' . round($percent, 1) . '%</td></tr></table></td>';
            $html .= '<td class="text-center">&nbsp;</td>';

            $html .= '</tr>';

            $total_quantity += $row['total_quantity'];
            $subtotal_amount += $row['subtotal_amount'];
            $i++;
        }

        $html .= '<tr>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">' . format_number($total_quantity) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF; font-weight:bold">' . format_number($subtotal_amount) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
        </tr>';

        return $html;
    }

    // Thống kê doanh thu theo hạng vé
    function ticketClassQuery($sql_search, $sql_role)
    {
        global $db;
        $sql = "SELECT (SELECT ticket_class FROM ec_booking_itineraries WHERE booking_id = bk.id AND direction = bkd.direction LIMIT 0,1) AS ticket_class
					   ,SUM(bkd.quantity) AS total_quantity
					   ,SUM(bkd.total_price) AS subtotal_amount
				FROM ec_booking_details bkd
				LEFT JOIN ec_flight_bookings bk ON bkd.booking_id = bk.id
				WHERE 
				    ( bk.booking_status = '7' OR bk.booking_status = '8' )
                    AND bk.deleted = 0 AND bkd.deleted = 0
				    " . $sql_search . $sql_role . "
				GROUP BY ticket_class
				ORDER BY total_quantity DESC ";
        $res = $db->query($sql);
        $res2 = $db->query($sql); // dùng cho tính toán phần trăm

        $qty = 0;
        $amt = 0;
        while ($row2 = $db->fetchByAssoc($res2)) {
            $qty += $row2['total_quantity'];
            $amt += $row2['subtotal_amount'];
        }

        $i = 0;
        $html = '';
        $total_quantity = 0;
        $subtotal_amount = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr style="background:#fff" >';
            $html .= '<td class="text-center">' . ($i + 1) . '</td>';
            $html .= '<td style="text-align:left;">' . $row['ticket_class'] . '</td>';
            $html .= '<td class="text-center">' . format_number($row['total_quantity']) . '</td>';
            $html .= '<td class="text-end">' . format_number($row['subtotal_amount']) . '</td>';

            // tính phần trăm
            if ($amt > 0)
                $percent = $row['subtotal_amount'] > 0 ? $row['subtotal_amount'] * 100 / $amt : 0;
            $pixel = (int)($percent * 120 / 100);
            $div_percent = '<div style="width:' . $pixel . 'px; height:10px; background:#09F">&nbsp;</div>';

            $html .= '<td class="text-end"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="90%">' . $div_percent . '</td><td align="right">' . round($percent, 1) . '%</td></tr></table></td>';
            $html .= '<td class="text-center">&nbsp;</td>';

            $html .= '</tr>';

            $total_quantity += $row['total_quantity'];
            $subtotal_amount += $row['subtotal_amount'];
            $i++;
        }

        $html .= '<tr>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">' . format_number($total_quantity) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF; font-weight:bold">' . format_number($subtotal_amount) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
        </tr>';

        return $html;
    }

    // Thống kê doanh thu theo hãng bay
    function airlineCodeQuery($sql_search, $sql_role, $post_fdate, $post_tdate)
    {
        global $db;

        $sql = "SELECT t.airline_code
                    , SUM(t.quantity) AS total_quantity
                    , (
                    SUM(t.total_price) 
                    + IFNULL((
                        SELECT SUM(IFNULL(p.amount,0))
                        FROM ec_receipt_voucher p
                        WHERE p.deleted=0 AND p.rv_status='1' 
                        AND p.loai_thu IN (" . $this->_loai_thu_str . ") 
                        AND p.aircode=t.airline_code
                        AND DATE(p.ngayhachtoan)>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                        AND DATE(p.ngayhachtoan)<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                        ),0) 
                        + IFNULL((
                        SELECT SUM(IFNULL(c.sotienhang,0))
                        FROM ec_chitiethoanve c
                        LEFT JOIN ec_hoanve p ON c.hoanve_id=p.id AND p.deleted=0
                        WHERE c.deleted=0 AND p.tinhtrang='1' AND c.airline_code=t.airline_code
                        AND p.ngayhachtoan>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                        AND p.ngayhachtoan<='" . date('Y-m-d', strtotime($post_tdate)) . "'	
                        ),0)
                    ) AS subtotal_amount
                    , (
                    SUM(t.total_bought_price) 
                    + IFNULL((
                        SELECT SUM(IFNULL(p.bought_amount,0))
                        FROM ec_receipt_voucher p
                        WHERE p.deleted=0 AND p.rv_status='1' 
                        AND p.loai_thu IN (" . $this->_loai_thu_str . ") AND p.aircode=t.airline_code
                        AND DATE(p.ngayhachtoan)>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                        AND DATE(p.ngayhachtoan)<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                        ),0)
                        + IFNULL((
                        SELECT SUM(IFNULL(c.sotienkhach,0))
                        FROM ec_chitiethoanve c
                        LEFT JOIN ec_hoanve p ON c.hoanve_id=p.id AND p.deleted=0
                        WHERE c.deleted=0 AND p.tinhtrang='1' AND c.airline_code=t.airline_code
                        AND p.ngayhachtoan>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                        AND p.ngayhachtoan<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                        ),0)
                    ) AS total_bought_amount
				FROM (
					SELECT IFNULL(bkd.quantity,0) AS quantity
						 , IFNULL(bkd.total_price,0) AS total_price
						 , IFNULL(bkd.total_bought_price,0) AS total_bought_price
						 , bkd.booking_id
						 , i.airline_code
					FROM ec_booking_details bkd
					LEFT JOIN ec_booking_itineraries i ON i.booking_id=bkd.booking_id AND i.deleted=0
					WHERE i.direction=bkd.direction AND bkd.deleted=0
				) AS t
				LEFT JOIN ec_flight_bookings bk ON t.booking_id=bk.id AND bk.deleted=0
				WHERE bk.booking_status IN ('7','8') " . $sql_search . $sql_role . "
				GROUP BY t.airline_code ";

        $res = $db->query($sql);

        $i = 0;
        $html = '';
        $total_quantity = 0;
        $subtotal_amount = 0;
        $total_bought_amount = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $html .= '<tr style="background:#fff" >';

            $html .= '<td class="text-center">' . ($i + 1) . '</td>';

            $html .= '<td class="text-start">' . $row['airline_code'] . '</td>';

            $html .= '<td class="text-center">' . format_number($row['total_quantity']) . '</td>';

            $html .= '<td class="text-end">' . format_number($row['subtotal_amount']) . '</td>';

            $opening_margin = $this->getTheOpeningMarginByAircode($row['airline_code'], $post_fdate); // so dau ky
            $html .= '<td class="text-end">' . format_number($opening_margin) . '</td>';

            $total_margin = $this->getTheTotalMarginByAircode($row['airline_code'], $post_fdate, $post_tdate);
            $html .= '<td class="text-end">' . format_number($total_margin) . '</td>';

            $html .= '<td class="text-end">' . format_number($row['total_bought_amount']) . '</td>';

            $html .= '<td class="text-end">' . format_number($opening_margin + $total_margin - $row['total_bought_amount']) . '</td>';

            $html .= '</tr>';

            $total_quantity += $row['total_quantity'];
            $subtotal_amount += $row['subtotal_amount'];
            $total_bought_amount += $row['total_bought_amount'];
            $i++;
        } // end while

        $html .= '<tr>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">' . format_number($total_quantity) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF; font-weight:bold">' . format_number($subtotal_amount) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF; font-weight:bold">' . format_number($total_bought_amount) . '</td>
            <td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>
        </tr>';

        return $html;
    }

    // Thống kê doanh thu theo booking
    function bookingQuery($sql_search, $sql_role, $post_fdate, $post_tdate, $condition_arr = array())
    {
        global $db, $app_list_strings, $current_user;
        $user_list = get_user_array(true, '', '', true);
        
        // set view_percent = 100 để ai cũng có thể xem được
        $current_user->view_percent = 100;

        $view_percent = ",SUM(bkd.quantity) AS total_quantity 
                        ,bk.total_amount AS subtotal_amount 
                        ,(SUM(IFNULL(bkd.total_bought_price,0)) 
							+
							IFNULL((
								SELECT IF(bk.flight_type='0', SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0) + IF(px.luggage_price_inbound>0, IFNULL(px.luggage_purchase_inbound,0), 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
								FROM ec_booking_passengers px
								WHERE px.booking_id=bk.id AND px.deleted=0 AND px.add_type IS NULL
							),0)) AS total_bought_price";

        if (isset($current_user->view_percent) && $current_user->view_percent < 100) {
            $view_percent = "
                ,IF(ROUND(" . $current_user->view_percent . " * SUM(bkd.quantity) / 100)<1,1,ROUND(" . $current_user->view_percent . " * SUM(bkd.quantity) / 100)) AS total_quantity
				,ROUND(" . $current_user->view_percent . " * bk.total_amount / 100) AS subtotal_amount
				,ROUND(" . $current_user->view_percent . " * 
                    (SUM(IFNULL(bkd.total_bought_price,0)) 
                        +
                        IFNULL((
                            SELECT IF(bk.flight_type='0', SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0) + IF(px.luggage_price_inbound>0, IFNULL(px.luggage_purchase_inbound,0), 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
                            FROM ec_booking_passengers px
                            WHERE px.booking_id=bk.id AND px.deleted=0 AND px.add_type IS NULL
                        ),0)
                    ) / 100
                ) AS total_bought_price ";
        }

        $sql_having = '';
        // Tìm theo tình trạng phiếu thu của booking: chưa thu / chưa thu đủ
        if ($condition_arr['payment_stt'] == 1) {
            // Chưa thu
            $sql_having = ' HAVING receipt_amount = 0';
        } else if ($condition_arr['payment_stt'] == 2) {
            // Chưa thu đủ
            $sql_having = ' HAVING receipt_amount < subtotal_amount AND receipt_amount > 0';
        }

        $sql = "
            SELECT 
                bk.id AS parent_id
                , bk.name AS parent_name
                , 'EC_Flight_Bookings' AS parent_type
                , (SELECT airline_code FROM ec_booking_itineraries WHERE booking_id=bk.id AND direction=0 AND deleted=0 LIMIT 1) AS airline_outbound
                , (SELECT airline_code FROM ec_booking_itineraries WHERE booking_id=bk.id AND direction=1 AND deleted=0 LIMIT 1) AS airline_inbound
                " . $view_percent . "
                , bk.flight_type
                , bk.ticket_type
                , bk.description AS booking_description
                , (SELECT iti.departure FROM ec_booking_itineraries iti WHERE iti.booking_id=bk.id AND iti.direction=0 AND iti.deleted=0 LIMIT 1) AS departure
                , (SELECT iti.departure_date FROM ec_booking_itineraries iti WHERE iti.booking_id=bk.id AND iti.direction=0 AND iti.deleted=0 LIMIT 1) AS departure_date
                , (SELECT iti.arrival FROM ec_booking_itineraries iti WHERE iti.booking_id=bk.id AND iti.direction=0 AND iti.deleted=0 LIMIT 1) AS arrival
                , (SELECT iti.arrival_date FROM ec_booking_itineraries iti WHERE iti.booking_id=bk.id AND iti.direction=0 AND iti.deleted=0 LIMIT 1) AS arrival_date
                , bk.booking_status AS parent_status
                , 'booking_status_list' AS parent_status_list
                , (SELECT ticket_class FROM  ec_booking_itineraries WHERE booking_id=bk.id AND direction=0 AND deleted=0 LIMIT 1) AS ticket_class_outbound
                , (SELECT ticket_class FROM  ec_booking_itineraries WHERE booking_id=bk.id AND direction=1 AND deleted=0 LIMIT 1) AS ticket_class_inbound
                , bk.is_ticket_exported
                , bk.is_agent
                , bk.assigned_user_id AS user_id
                , (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
                , bk.recheck_status
                , bk.country
                , IFNULL((
                    SELECT SUM(IFNULL(r.amount_converted,0))
                    FROM ec_receipt_voucher r
                    WHERE 
                        r.booking_id=bkd.booking_id
                        AND r.rv_status='1'
                        AND r.loai_thu='1'
                        AND r.deleted=0
                    GROUP BY r.booking_id
                ), 0) AS receipt_amount
                ,DATE_FORMAT(bk.date_ticket_issue, '%d-%m-%Y') AS date_ticket_issue
                ,(SELECT amount FROM ec_payment_voucher WHERE booking_id=bkd.booking_id AND pv_status='3' AND ec_payment_types_id_c='3f9f8060-1866-2b2e-8322-52e36b8f58d5' AND deleted=0 LIMIT 1) AS discount_amt
                ,bk.phone AS contact_mobile
                ,DATE_FORMAT(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), '%d-%m-%Y') AS bk_date_entered
                ,DATE_FORMAT(bk.date_ticket_issue, '%d-%m-%Y') AS bk_date_ticket_issue
                ,(SELECT IF(u.title NOT LIKE '%bot%', 1, 0) FROM users u WHERE u.id=bk.created_by) AS not_from_web
            FROM ec_booking_details bkd 
            LEFT JOIN ec_flight_bookings bk ON bkd.booking_id=bk.id AND bk.deleted=0 
            WHERE 
                bk.booking_status IN ('7', '8')
                " . $sql_search . $sql_role . " 
                AND bkd.deleted=0 
            GROUP BY bk.id" . $sql_having;

        // nếu không tìm kiếm những booking chưa thu tiền 
        // hiện thêm những hoàn vé với phiếu thu
        if (empty($condition_arr['payment_stt'])) {
            $sql .= "
                UNION
                SELECT 
                    p.id AS parent_id
                    ,p.name AS parent_name
                    ,'EC_Receipt_Voucher' AS parent_type
                    ,p.aircode AS airline_outbound
                    ,'' AS airline_inbound
                    ,0 AS total_quantity
                    ,SUM(IF(p.rv_status IN (1, 2), p.amount, 0))  AS subtotal_amount
                    ,SUM(
                    IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount, 0), 0) 
                    + IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount2, 0), 0) 
                    + IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount3, 0), 0)
                    ) AS total_bought_price
                    ,'' AS flight_type
                    ,'' AS ticket_type
                    ,'' AS booking_description
                    ,'' AS departure
                    ,'' AS departure_date
                    ,'' AS arrival
                    ,'' AS arrival_date
                    ,p.rv_status AS parent_status
                    ,'receipt_voucher_status_list' AS parent_status_list
                    ,'' AS ticket_class_outbound
                    ,'' AS ticket_class_inbound
                    ,'' AS is_ticket_exported
                    ,'' AS is_agent
                    , p.assigned_user_id AS user_id
                    ,(SELECT u.user_name FROM users u WHERE u.id=p.assigned_user_id) AS user_name
                    ,'' AS recheck_status
                    ,'' AS country
                    ,SUM(IF(p.rv_status IN (1, 2), p.amount, 0)) AS receipt_amount
                    ,DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%d-%m-%Y') AS date_ticket_issue
                    ,0 AS discount_amt
                    ,p.guest_phone AS contact_mobile
                    ,'' AS bk_date_entered
                    ,'' AS bk_date_ticket_issue
                    ,0 AS not_from_web
                FROM ec_receipt_voucher p
                WHERE 
                    p.loai_thu IN (" . $this->_loai_thu_str . ") 
                    AND DATE(p.ngayhachtoan)>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                    AND DATE(p.ngayhachtoan)<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                    AND p.deleted=0
                    " . str_replace('bk.', 'p.', $sql_role) . "
                AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
                GROUP BY p.id
                
                -- hoan ve

                UNION
                SELECT hv_t.parent_id, hv_t.parent_name, hv_t.parent_type
                    , hv_t.airline_outbound, hv_t.airline_inbound
                    , SUM(hv_t.total_quantity) AS total_quantity
                    , SUM(hv_t.subtotal_amount) AS subtotal_amount
                    , SUM(hv_t.total_bought_price) AS total_bought_price, hv_t.flight_type
                    , hv_t.ticket_type, hv_t.booking_description, hv_t.departure
                    , hv_t.departure_date, hv_t.arrival, hv_t.arrival_date
                    , hv_t.parent_status, hv_t.parent_status_list
                    , hv_t.ticket_class_outbound, hv_t.ticket_class_inbound
                    , hv_t.is_ticket_exported, hv_t.is_agent, hv_t.user_id, hv_t.user_name
                    , hv_t.recheck_status, hv_t.country, hv_t.receipt_amount
                    , hv_t.date_ticket_issue, hv_t.discount_amt, hv_t.contact_mobile
                    , '' AS bk_date_entered
                    , '' AS bk_date_ticket_issue
                    , 0 AS not_from_web
                FROM 
                (
                    SELECT 
                        p.id AS parent_id
                        ,p.name AS parent_name
                        ,'EC_HoanVe' AS parent_type
                        ,'' AS airline_outbound
                        ,'' AS airline_inbound
                        , -(SELECT COUNT(id) FROM ec_chitiethoanve WHERE deleted = 0 AND hoanve_id = p.id) AS total_quantity
                        ,IF( SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienhang,0)), 0)  AS subtotal_amount
                        ,IF( SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienkhach,0)), 0) AS total_bought_price
                        ,'' AS flight_type
                        ,'' AS ticket_type
                        ,'' AS booking_description
                        ,'' AS departure
                        ,'' AS departure_date
                        ,'' AS arrival
                        ,'' AS arrival_date
                        ,p.tinhtrang AS parent_status
                        ,'tinhtranghoanve_list' AS parent_status_list
                        ,'' AS ticket_class_outbound
                        ,'' AS ticket_class_inbound
                        ,'' AS is_ticket_exported
                        ,'' AS is_agent
                        , bk.assigned_user_id AS user_id
                        , (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
                        , '' AS recheck_status
                        , '' AS country
                        , 0 AS receipt_amount
                        ,DATE_FORMAT(p.ngayhachtoan, '%d-%m-%Y') AS date_ticket_issue
                        ,0 AS discount_amt
                        ,'' AS contact_mobile
                    FROM ec_hoanve p
                    INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id
                    WHERE p.deleted=0
                    AND p.tinhtrang='1'
                    AND p.ngayhachtoan>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                    AND p.ngayhachtoan<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                    " . $sql_role . "
                    GROUP BY p.id

                    -- hoan ve > 0

                    UNION
                    SELECT 
                        p.id AS parent_id
                        ,p.name AS parent_name
                        ,'EC_HoanVe' AS parent_type
                        ,'' AS airline_outbound
                        ,'' AS airline_inbound
                        , 0 AS total_quantity
                        , SUM(IFNULL(p.tongtienhang,0))  AS subtotal_amount
                        , SUM(IFNULL(p.tongtienkhach,0)) AS total_bought_price
                        ,'' AS flight_type
                        ,'' AS ticket_type
                        ,'' AS booking_description
                        ,'' AS departure
                        ,'' AS departure_date
                        ,'' AS arrival
                        ,'' AS arrival_date
                        ,p.tinhtrang AS parent_status
                        ,'tinhtranghoanve_list' AS parent_status_list
                        ,'' AS ticket_class_outbound
                        ,'' AS ticket_class_inbound
                        ,'' AS is_ticket_exported
                        ,'' AS is_agent
                        , p.assigned_user_id AS user_id
                        , (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
                        , '' AS recheck_status
                        , '' AS country
                        , 0 AS receipt_amount
                        ,DATE_FORMAT(p.ngayhachtoan, '%d-%m-%Y') AS date_ticket_issue
                        ,0 AS discount_amt
                        ,'' AS contact_mobile
                    FROM ec_hoanve p
                    INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id
                    WHERE p.deleted=0
                    AND p.tinhtrang='1' 
                    AND p.ngayhachtoan>='" . date('Y-m-d', strtotime($post_fdate)) . "'
                    AND p.ngayhachtoan<='" . date('Y-m-d', strtotime($post_tdate)) . "'
                    " . str_replace('bk', 'p', $sql_role) . "
                    GROUP BY p.id
                    HAVING SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) > 0
                ) AS hv_t";

            $sql .= "
                GROUP BY hv_t.parent_id
                ORDER BY total_quantity DESC 
            ";
        }

        // if ($GLOBALS['current_user']->user_name == 'hungnh') {
        //     pr($sql);
        // }

        $res    = $db->query($sql);
        $i      = 0;

        $total_quantity     = 0;
        $subtotal_amount    = 0;
        $total_bought_price = 0;
        $total_profit       = 0;
        $total_receipt      = 0;
        
        $html = $xls = '';
        while ($row = $db->fetchByAssoc($res)) {

            $profit_amount = $row['subtotal_amount'] - $row['total_bought_price'] - $row['discount_amt'];

            if ($row['total_bought_price'] > $row['subtotal_amount'] && $row['parent_type']){
                $bg_class = 'error1';
            } elseif ($row['receipt_amount'] < $row['subtotal_amount'] && $row['parent_type'] == 'EC_Flight_Bookings'){
                $bg_class = 'error2';
            } elseif ($row['subtotal_amount'] < $row['receipt_amount']){
                $bg_class = 'sales_smaller_receipt';
            } elseif ($row['total_bought_price'] == $row['subtotal_amount']){
                $bg_class = 'equal';
            } else $bg_class = 'normal';

            $html .= '<tr class="' . $bg_class . '" >';

            if ($this->_is_allow_recheck) {
                $html .= '<td class="text-center booking-ids hide-mobile">';
                if ($row['parent_type'] == 'EC_Flight_Bookings') {
                    $html .= '<span style="display: none;" class="loading"></span>';
                    $html .= '<input type="checkbox" class="booking-ids vertical-middle" data-triptype="' . $row['flight_type'] . '" data-mobile="' . $row['contact_mobile'] . '" id="' . $row['parent_id'] . '" value="' . $row['parent_id'] . '">';
                }
                $html .= '</td>';
            }

            $html .= '
                    <td class="text-center hide-mobile">' . ($i + 1) . '</td>
                    <td class="text-center"><a target="_blank" title="Xem chi tiết" href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '">' . $row['parent_name'] . '</a></td>
                    <td class="text-center total_quantity">' . format_number($row['total_quantity']) . '</td>
                    <td class="text-start booking_description hide-mobile">' . $row['booking_description'] . ($row['not_from_web'] ? (!empty($row['booking_description']) ? '<br>' : '') . '<b>(Tạo bởi booker)</b>' : '') . '</td>
                    <td class="text-end hide-mobile">' . format_number($row['subtotal_amount']) . '</td>
                    <td class="text-end total_bought_price hide-mobile">' . format_number($row['total_bought_price']) . '</td>
                    <td class="text-end">' . format_number($profit_amount) . '</label></td>';

            // $html .= '<td class="text-center hide-mobile">' . ($row['country'] == "VN" ? "" : $row['country']) . '</label></td>';

            if ($row['parent_type'] == 'EC_Flight_Bookings') {
                $html .= '<td class="text-end hide-mobile">
                            <a title="Click vào để xem chi tiết phiếu thu" href="index.php?action=index&module=EC_Receipt_Voucher&query=true&clear_query=true&searchFormTab=basic_search&booking_name_basic=' . $row['parent_name'] . '" target="_blank">
                                ' . format_number($row['receipt_amount']) . '
                            </a>
                        </td>';
            } else {
                $html .= '<td class="text-end hide-mobile">' . format_number($row['receipt_amount']) . '</td>';
            }

            // Xem chi tiết nhân viên
            // $html .= '<td class="text-center"><a href="index.php?module=EC_Flight_Bookings&action=ticketreport&from_date=' . $post_fdate . '&to_date=' . $post_tdate . '&uid=' . $row['user_id'] . '" target="_bank" title="Xem chi tiết nhân viên ' . $user_list[$row['user_id']] . '">' . $user_list[$row['user_id']] . '</a></td>';
            $user_id_detail = isset($row['user_id']) ? $row['user_id'] : 0;
            $html .= '<td class="text-start row-employees"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $user_id_detail . '" target="_bank" title="Xem chi tiết nhân viên ' . $user_list[$row['user_id']] . '">' . $user_list[$row['user_id']] . '</a></td>';

            // $html .= '<td class="text-center">' . $this->getRecheckInfo($row['parent_id']) . '<div class="rc-message"></div></td>';
            $html .= '<td class="text-center bk_date_entered hide-mobile">' . $row['bk_date_entered'] . '</td>';
            $html .= '<td class="text-center bk_date_ticket_issue hide-mobile">' . $row['bk_date_ticket_issue'] . '</td>';

            $html .= '</tr>';

            // Xuat excel
            $xls .= " <tr height=17 style='height:12.75pt'>
			<td height=17 class=xl6624108 style='height:12.75pt;border-top:none'>" . ($i + 1) . "</td>
			<td class=xl6724108 style='border-top:none;border-left:none'>" . $row['parent_name'] . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . format_number($row['total_quantity']) . "</td>
			<td class=xl6824108 style='border-top:none;border-left:none'>" . format_number($row['subtotal_amount']) . "</td>
			<td class=xl6824108 style='border-top:none;border-left:none'>" . format_number($row['total_bought_price']) . "</td> 
            <td class=xl6824108 style='border-top:none;border-left:none'>" . format_number($row['receipt_amount']) . "</td>";
            if ($row['flight_type'] != '') {
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . ($row['flight_type'] == 0 ? $row['airline_outbound'] . ' - ' . $row['airline_inbound'] : $row['airline_outbound']) . "</td>";
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . $app_list_strings['bk_flight_type_list'][$row['flight_type']] . "</td>";
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . ($row['flight_type'] == 0 ? $row['ticket_class_outbound'] . ' - ' . $row['ticket_class_inbound'] : $row['ticket_class_outbound']) . "</td>";
            } else {
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . $row['airline_outbound'] . "</td>";
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>&nbsp;</td>";
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>&nbsp;</td>";
            }

            $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . $row['departure'] . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . (isset($row['departure_date']) && !empty($row['departure_date']) ? date('d-m-Y H:i', strtotime($row['departure_date'])) : '') . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . $row['arrival'] . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . (isset($row['arrival_date']) && !empty($row['arrival_date']) ? date('d-m-Y H:i', strtotime($row['arrival_date'])) : '') . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . $app_list_strings[$row['parent_status_list']][$row['parent_status']] . "</td>
			<td class=xl6624108 style='border-top:none;border-left:none'>" . $user_list[$row['user_id']] . "</td>";

            if ($row['is_agent'] != '') {
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . ($row['is_agent'] == 1 ? 'Đại lý' : $this->getReceiptVoucher($row['parent_id'])) . "</td>";
            } else {
                $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>&nbsp;</td>";
            }
            $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . $row['country'] . "</td>";
            $xls .= "<td class=xl6624108 style='border-top:none;border-left:none'>" . $row['date_ticket_issue'] . "</td>";
            $xls .= "</tr>";

            $total_quantity += $row['total_quantity'];
            $subtotal_amount += $row['subtotal_amount'];
            $total_bought_price += $row['total_bought_price'];
            $total_profit += $profit_amount;
            $total_receipt += $row['receipt_amount'];
            $i++;
        } // end while

        // $html = '<tr class="total">';

        // if($this->_is_allow_recheck){
        //     $html .= '<td style="padding:6px 10px;background:url(custom/themes/Sugar/images/salesstatistics/grad-11-blocksubhead.gif) repeat-x scroll center bottom #FFFFFF;">&nbsp;</td>';
        // }

        $html_total = '<tr class="total">
            <td class="text-center fw-semibold color-red total_quantity">' . format_number($total_quantity) . '</td>
            <td class="text-center fw-semibold color-red subtotal_amount">' . format_number($subtotal_amount) . '</td>
            <td class="text-center fw-semibold color-red total_bought_price">' . format_number($total_bought_price) . '</td>
            <td class="text-center fw-semibold color-red total_profit">' . format_number($total_profit) . '</td>
            <td class="text-center fw-semibold color-red total_receipt">' . format_number($total_receipt) . '</td>
        </tr>';

        $html .= '<tr class="footer-tr">
            <td colspan="2" class="hide-mobile">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text-center fw-semibold color-red total_quantity">' . format_number($total_quantity) . '</td>
            <td class="notes hide-mobile">&nbsp;</td>
            <td class="text-center fw-semibold color-red subtotal_amount hide-mobile">' . format_number($subtotal_amount) . '</td>
            <td class="text-center fw-semibold color-red total_bought_price hide-mobile">' . format_number($total_bought_price) . '</td>
            <td class="text-center fw-semibold color-red total_profit">' . format_number($total_profit) . '</td>
            <td class="text-center fw-semibold color-red total_receipt hide-mobile">' . format_number($total_receipt) . '</td>
            <td class="employees">&nbsp;</td>
            <td class="date_created hide-mobile">&nbsp;</td>
            <td class="date_issue hide-mobile">&nbsp;</td>
        </tr>';

        $xls .= "<tr height=17 style='height:12.75pt'>
				  <td height=17 class=xl6924108 style='height:12.75pt;border-top:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6524108 style='border-top:none;border-left:none'>" . format_number($total_quantity) . "</td>
                  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl7024108 align=right style='border-top:none;border-left:none'>" . format_number($subtotal_amount) . "</td>
				  <td class=xl7024108 align=right style='border-top:none;border-left:none'>" . format_number($total_bought_price) . "</td>
				  <td class=xl7024108 align=right style='border-top:none;border-left:none'>" . format_number($total_receipt) . "</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				  <td class=xl6924108 style='border-top:none;border-left:none'>&nbsp;</td>
				</tr>";

        $arr['xls'] = $xls;
        $arr['html'] = $html;
        $arr['html_total'] = $html_total;
        return $arr;
    }

    // Lấy thông tin phiếu thu
    function getReceiptVoucher($booking_id)
    {
        global $db, $app_list_strings;
        $rv_status = '<label style="color:red; font-weight:bold;">Chưa lập</label>';
        $sql = "SELECT rv_status FROM ec_receipt_voucher WHERE booking_id = '" . $booking_id . "' AND deleted = 0 ";
        $res = $db->query($sql);
        $row = $db->fetchByAssoc($res);
        if (!empty($row)) {
            $rv_status = ($row['rv_status'] == '1') ? $app_list_strings['receipt_voucher_status_list'][$row['rv_status']] : '<label style="color:blue;">' . $app_list_strings['receipt_voucher_status_list'][$row['rv_status']] . '</label>';
        }
        return $rv_status;
    }

    // Tính tổng tiền ký quỹ theo từng hãng
    function getTheTotalMarginByAircode($aircode, $post_fdate, $post_tdate)
    {
        global $db;
        $total = 0;
        $sql = "SELECT SUM(total) FROM (
					SELECT p.id, SUM(IFNULL(p.amount,0)) AS total
					FROM ec_payment_voucher p
					WHERE p.deleted=0 AND p.pv_status='3'
					AND p.ec_payment_types_id_c IN (
						SELECT id
						FROM ec_payment_types
						WHERE deleted=0 AND aircode='" . $aircode . "'
					) 
					AND DATE(p.ngayhachtoan)>='" . date('Y-m-d', strtotime($post_fdate)) . "'
					AND DATE(p.ngayhachtoan)<='" . date('Y-m-d', strtotime($post_tdate)) . "'
					
					UNION
					SELECT p.id, SUM(IFNULL(c.sotienhang,0)) AS total
					FROM ec_chitiethoanve c
					LEFT JOIN ec_hoanve p ON c.hoanve_id=p.id AND p.deleted=0
					WHERE c.deleted=0 AND p.tinhtrang IN ('0','1') AND c.airline_code='" . $aircode . "'
					AND p.ngayhachtoan>='" . date('Y-m-d', strtotime($post_fdate)) . "'
					AND p.ngayhachtoan<='" . date('Y-m-d', strtotime($post_tdate)) . "'
				) AS t ";

        $total += $db->getOne($sql);
        return $total;
    }

    // Lấy số tiền ký quỹ đầu kỳ theo từng hãng
    function getTheOpeningMarginByAircode($aircode, $post_fdate)
    {
        global $db;
        $nam = date('Y', strtotime($post_fdate)) == date('Y') ? '' : date('Y', strtotime($post_fdate));
        $sql = "SELECT SUM(kyqui) - SUM(giamua) FROM (
					SELECT id, SUM(IFNULL(dunodau,0)) - SUM(IFNULL(ducodau,0)) AS kyqui, 0 AS giamua
					FROM ec_chitiettaikhoan" . $nam . "
					WHERE deleted=0 AND parent_type='Accounts'
					AND sotaikhoan='144' AND parent_id=(
						SELECT id FROM accounts 
						WHERE deleted=0 
						AND ticker_symbol='" . $aircode . "' LIMIT 1
					) GROUP BY parent_id
					
					UNION
					SELECT p.id, SUM(IFNULL(p.amount,0)) AS kyqui, 0 AS giamua
					FROM ec_payment_voucher p
					WHERE p.deleted=0 AND p.pv_status='3'
					AND p.ec_payment_types_id_c IN (
						SELECT id
						FROM ec_payment_types
						WHERE deleted=0 AND aircode='" . $aircode . "'
					) 
					AND DATE(p.ngayhachtoan)>='" . date('Y-01-01') . "'
					AND DATE(p.ngayhachtoan)<'" . date('Y-m-d', strtotime($post_fdate)) . "'
					
					UNION
					SELECT p.id, 0 AS kyqui, SUM(IFNULL(c.total_bought_price,0)) AS giamua	  
					FROM ec_booking_details c
					LEFT JOIN ec_flight_bookings p ON c.booking_id=p.id AND p.deleted=0
					LEFT JOIN ec_booking_itineraries i ON i.booking_id=p.id AND i.deleted=0
					WHERE c.deleted=0 AND p.booking_status IN ('7','8') 
					AND c.direction=i.direction AND i.airline_code='" . $aircode . "'
					AND p.date_ticket_issue>='" . date('Y-01-01') . "' 
					AND p.date_ticket_issue<'" . date('Y-m-d', strtotime($post_fdate)) . "'
					
					UNION
					SELECT id, 0 AS kyqui, SUM(IFNULL(bought_amount,0)) AS giamua
					FROM ec_receipt_voucher 
					WHERE deleted=0 AND rv_status='1' 
					AND loai_thu IN (" . $this->_loai_thu_str . ") AND aircode='" . $aircode . "'
					AND DATE(ngayhachtoan)>='" . date('Y-01-01') . "'
					AND DATE(ngayhachtoan)<'" . date('Y-m-d', strtotime($post_fdate)) . "'
					
					UNION
					SELECT p.id, SUM(IFNULL(c.sotienhang,0)) AS kyqui, SUM(IFNULL(c.sotienkhach,0)) AS giamua
					FROM ec_chitiethoanve c 
					LEFT JOIN ec_hoanve p ON c.hoanve_id=p.id AND p.deleted=0
					WHERE c.deleted=0 AND p.tinhtrang IN ('0','1') AND c.airline_code='" . $aircode . "'
					AND p.ngayhachtoan>='" . date('Y-01-01') . "'
					AND p.ngayhachtoan<'" . date('Y-m-d', strtotime($post_fdate)) . "'
				) AS t ";

        $total = 0;
        $total += $db->getOne($sql);
        return $total;
    }

    // Lấy thông tin người recheck
    function getRecheckInfo($booking_id)
    {
        global $db;
        $sql = "SELECT w.description, u.user_name
				FROM ec_working_process w
				LEFT JOIN users u ON w.assigned_user_id = u.id AND u.deleted = 0
				WHERE w.deleted = 0
				AND w.parent_type = 'EC_Flight_Bookings'
				AND w.parent_id = '" . $booking_id . "'
				AND w.recheck > 0 ";
        $res = $db->query($sql);
        // $row_count = $db->getRowCount($res);
        $row_count = $db->countRows($res);
        $str = '';
        $i = 0;
        while ($row = $db->fetchByAssoc($res)) {
            $str .= '<div style="font-size: 12.5px; text-align: left;">' . $row['description'] . '</div>' . '<div style="text-align: right; font-style: italic; margin-bottom: 2px;">--(' . $row['user_name'] . ')</div>';
            if ($i != ($row_count - 1))
                $str .= "\n";
            $i++;
        }
        return $str;
    }
}
