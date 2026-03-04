<?php
if (!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewbookingqtyreport extends SugarView
{
	function display()
	{
		global $current_user;
		$user_title = $current_user->title;

		// Bảo trì
		// if ($current_user->user_name != 'hungnh') {
		// 	echo '<p class="alert alert-warning fw-semibold">Báo cáo doanh số booking đang bảo trì. Vui lòng quay lại sau!</p>';
		// 	exit();
		// }

		if (
			is_admin($current_user) ||
			$user_title == 'QuanLy'
		) {
			$smarty = new Sugar_Smarty();
			$this->generateSearchResult($smarty);
			$this->populateRadioBtn($smarty);
			$smarty->display('modules/EC_TongHop/tpls/view_bookingqytreport.tpl');
		} else {
			header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không có quyền xem báo cáo này. Vui lòng liên hệ quản trị viên để được cấp quyền."));
			exit;
		}
	}

	function generateSearchResult($smarty)
	{
		if (!isset($_POST['from_date']) || empty($_POST['from_date'])) {
			$_POST['from_date'] = date('d-m-Y');
		}

		if (!isset($_POST['to_date']) || empty($_POST['to_date'])) {
			$_POST['to_date'] = date('d-m-Y');
		}

		if (isset($_POST['from_date'])) {
			$smarty->assign('from_date', $_POST['from_date']);
		}

		if (isset($_POST['to_date'])) {
			$smarty->assign('to_date', $_POST['to_date']);
		}

		// $html = $this->genBKSale_old($_POST['from_date'], $_POST['to_date']);
		// $smarty->assign('rpt_body', $html);

		$html = $this->genBKSale_compare($_POST['from_date'], $_POST['to_date']);
		$smarty->assign('rpt_body_compare', $html);
	}

	function populateRadioBtn($smartyobj)
	{
		//Radio option
		$option_today = '<input type="radio" value="today" id="today" class="rd_time form-check-input" name="optionRadio" ' . ($_POST['optionRadio'] == 'today' ? 'checked' : '') . ' fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">';
		$smartyobj->assign('RADIO_TODAY', $option_today);
	
		$option_yesterday = '<input type="radio" value="yesterday" id="yesterday" class="rd_time form-check-input" name="optionRadio" ' . ($_POST['optionRadio'] == 'yesterday' ? 'checked' : '') . ' fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">';
		$smartyobj->assign('RADIO_YESTERDAY', $option_yesterday);

		$option_daybefore = '<input type="radio" value="daybefore" id="daybefore" class="rd_time form-check-input" name="optionRadio" ' . ($_POST['optionRadio'] == 'daybefore' ? 'checked' : '') . ' fromdate="' . date('d-m-Y', strtotime('-2 days')) . '" todate="' . date('d-m-Y', strtotime('-2 days')) . '">';
		$smartyobj->assign('RADIO_DAYBEFORE', $option_daybefore);

		$option_current_week = '<input type="radio" value="current_week" id="current_week" class="rd_time form-check-input" name="optionRadio" ' . ($_POST['optionRadio'] == 'current_week' ? 'checked' : '') . ' fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">';
		$smartyobj->assign('RADIO_CURRENTWEEK', $option_current_week);

		$option_previous_week = '<input type="radio" value="previous_week" id="previous_week" class="rd_time form-check-input" name="optionRadio" ' . ($_POST['optionRadio'] == 'previous_week' ? 'checked' : '') . ' fromdate="' . date('d-m-Y', strtotime('monday previous week')) . '" todate="' . date('d-m-Y', strtotime('sunday previous week')) . '">';
		$smartyobj->assign('RADIO_PREVIOUSWEEK', $option_previous_week);

		$smartyobj->assign('CURRENT_FROMDATE', date('d-m-Y', strtotime('first day of this month')));
		$smartyobj->assign('CURRENT_TODATE', date('d-m-Y', strtotime('last day of this month')));
		$smartyobj->assign('PREVIOUS_FROMDATE', date('d-m-Y', strtotime('first day of last month')));
		$smartyobj->assign('PREVIOUS_TODATE', date('t-m-Y', strtotime('last day of last month')));
		$smartyobj->assign('PREVIOUSYEAR_FROMDATE', date('d-m-Y', strtotime('-1 year')));
		$smartyobj->assign('PREVIOUSYEAR_TODATE', date('d-m-Y', strtotime('-1 year')));

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
			'<option ' . ($_POST['date_select'] == 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
			'<option ' . ($_POST['date_select'] == 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
			'<option ' . ($_POST['date_select'] == 'quarter_this' ? 'selected' : '') . ' value="quarter_this" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
			'<option ' . ($_POST['date_select'] == 'quarter_previous' ? 'selected' : '') . ' value="quarter_previous" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
			'<option ' . ($_POST['date_select'] == 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
			'<option ' . ($_POST['date_select'] == 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
		);
		$smartyobj->assign('DATE_OPTION', implode('', $arr_date));
		$smartyobj->assign('CURRENT_QUARTER_FROMDATE', $quater_fromdate);
		$smartyobj->assign('CURRENT_QUARTER_TODATE', $quater_todate);
		$smartyobj->assign('PREVIOUS_QUARTER_FROMDATE', date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))));
		$smartyobj->assign('PREVIOUS_QUARTER_TODATE', date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))));
	}

	function genBKSale_old($from_date, $to_date)
	{
		try {
			$sql = '
		SELECT last_name, user_name, user_id,
			SUM(bk_confirmed) AS bk_confirmed,
			SUM(bk_printed) AS bk_printed,
			SUM(bk_completed) AS bk_completed,
			SUM(bk_cancelled) AS bk_cancelled,
			SUM(total) AS total,
			SUM(total_sales) AS total_sales,
			SUM(total_ticket) AS total_ticket,
			SUM(my_bk) AS my_bk,
			SUM(com_my_bk) AS com_my_bk,
			SUM(khach_hang_bk) AS khach_hang_bk,
			SUM(com_khach_hang_bk) AS com_khach_hang_bk,
			SUM(tham_khao_bk) AS tham_khao_bk,
			SUM(com_tham_khao_bk) AS com_tham_khao_bk,
			SUM(bk_1_3_ticket) AS bk_1_3_ticket,
			SUM(com_1_3ticket_qty) AS com_1_3ticket_qty,
			SUM(com_1_3ticket) AS com_1_3ticket,
			SUM(bk_1to3ticket_sales) AS bk_1to3ticket_sales,
			SUM(bk_4_8_ticket) AS bk_4_8_ticket,
			SUM(com_4_8ticket_qty) AS com_4_8ticket_qty,
			SUM(com_4_8ticket) AS com_4_8ticket,
			SUM(bk_4to8ticket_sales) AS bk_4to8ticket_sales,
			SUM(prior_bk) AS prior_bk,
			SUM(com_prior_bk) AS com_prior_bk,
			SUM(com_prior_ticket) AS com_prior_ticket,
			SUM(prior_bk_sales) AS prior_bk_sales,

			SUM(bk_inter_ticket) AS bk_inter_ticket,
			SUM(com_inter_ticket_qty) AS com_inter_ticket_qty,
			SUM(com_inter_ticket) AS com_inter_ticket,
			SUM(bk_inter_ticket_sales) AS bk_inter_ticket_sales,

			SUM(inbound) AS c_inbound,
			SUM(missed) AS c_missed,
			SUM(inbound_bk) AS c_inbound_bk
		FROM (
			-- FIRST QUERY: Main bookings > 1440 minutes
			SELECT
				u.last_name, u.user_name, u.id AS user_id,
				COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
				COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
				COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
				COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
				COUNT(bk.id) AS total,
				SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
				SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi")), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi"))) AS my_bk,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND bk.booking_status IN (3, 7, 8)), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND booking_status IN (3, 7, 8))) AS com_my_bk,
				
				-- KHACH HANG BOOK
				IF(bk.contact_name NOT IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao") 
				AND bk.contact_name IS NOT NULL 
				AND bk.contact_name != ""
				AND NOT EXISTS(SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")), 1, 0) AS khach_hang_bk,

				IF(bk.contact_name NOT IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao") 
				AND bk.contact_name IS NOT NULL 
				AND bk.contact_name != "" 
				AND bk.booking_status IN (3, 7, 8)
				AND NOT EXISTS(SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")), 1, 0) AS com_khach_hang_bk,
				-- THAM KHAO
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Tham Khao")), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Tham Khao"))) AS tham_khao_bk,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Tham Khao") AND bk.booking_status IN (3, 7, 8)), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Tham Khao") AND booking_status IN (3, 7, 8))) AS com_tham_khao_bk,
				
				SUM(IF(bk.total_qty <= 3, 1, 0)) AS bk_1_3_ticket,
				SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), 1, 0)) AS com_1_3ticket_qty,
				SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_1_3ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty <= 3, bk.total_amount - bk.total_bought_amount, 0)) AS bk_1to3ticket_sales,
				SUM(IF(bk.total_qty >= 4 AND bk.total_qty <= 8, 1, 0)) AS bk_4_8_ticket,
				SUM(IF(bk.total_qty >= 4 AND bk.total_qty <= 8 AND bk.booking_status IN (3, 7, 8), 1, 0)) AS com_4_8ticket_qty,
				SUM(IF(bk.total_qty >= 4 AND bk.total_qty <= 8 AND bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_4_8ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty >= 4 AND bk.total_qty <= 8, bk.total_amount - bk.total_bought_amount, 0)) AS bk_4to8ticket_sales,
				0 AS prior_bk,
				0 AS com_prior_bk,
				0 AS com_prior_ticket,
				0 AS prior_bk_sales,

				SUM(IFNULL((SELECT IF(SUM(quantity), 1, 0) FROM ec_booking_details WHERE booking_id = bk.id AND bk.ticket_type = 2 AND deleted = 0), 0)) AS bk_inter_ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_amount - bk.total_bought_amount, 0)) AS bk_inter_ticket_sales,

				0 AS inbound,
				0 AS missed,
				0 AS inbound_bk,

				(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_flight_bookings bk
				LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
			WHERE u.title = "Bot" 
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN  "' . date('Y-m-d', strtotime($from_date)) . '" AND  "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59"
			AND bk.deleted = 0 
			GROUP BY bk.id
			HAVING (TIMESTAMPDIFF(MINUTE, bk_date_entered, min_dep_time) > 1440)
			
			UNION
			-- SECOND QUERY: Luggage adjustments
			SELECT 
				IF(u.title = "Bot", u.last_name, "Chưa xác định") AS last_name,
				IF(u.title = "Bot", u.user_name, "") AS user_name,
				IF(u.title = "Bot", u.id, "BK_UNK") AS user_id,
				0 AS bk_confirmed,
				0 AS bk_printed,
				0 AS bk_completed,
				0 AS bk_cancelled,
				0 AS total,
				- (SUM(IFNULL(bk_psg.luggage_purchase, 0)) + SUM(IFNULL(bk_psg.luggage_purchase_inbound, 0))) AS total_sales,
				0 AS total_ticket,
				0 AS my_bk,
				0 AS com_my_bk,
				0 AS khach_hang_bk,
				0 AS com_khach_hang_bk,
				0 AS tham_khao_bk,
				0 AS com_tham_khao_bk,
				0 AS bk_1_3_ticket,
				0 AS com_1_3ticket_qty,
				0 AS com_1_3ticket,
				- SUM(IF(bk.id = (SELECT DISTINCT i.booking_id FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0 AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440 AND bk.booking_status IN (3, 7, 8) AND bk.total_qty <= 3), IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0)) AS bk_1to3ticket_sales,
				0 AS bk_4_8_ticket,
				0 AS com_4_8ticket_qty,
				0 AS com_4_8ticket,
				- SUM(IF(bk.id = (SELECT DISTINCT i.booking_id FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0 AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440 AND bk.booking_status IN (3, 7, 8) AND bk.total_qty >= 4 AND bk.total_qty <= 9), IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0)) AS bk_4to8ticket_sales, 
				0 AS prior_bk,
				0 AS com_prior_bk,
				0 AS com_prior_ticket,
				- (SUM(IF(TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), (SELECT MIN(departure_date) FROM ec_booking_itineraries WHERE booking_id = bk.id AND deleted = 0)) <= 1440, IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0))) AS prior_bk_sales,
				
				0 AS bk_inter_ticket,
				0 AS com_inter_ticket_qty,
				0 AS com_inter_ticket,
				-SUM(IF(bk.id = (SELECT DISTINCT i.booking_id FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0 AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440 AND bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2), IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0)) AS bk_inter_ticket_sales,

				0 AS inbound,
				0 AS missed,
				0 AS inbound_bk,

				(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_booking_passengers bk_psg
			INNER JOIN ec_flight_bookings bk ON bk.id = bk_psg.booking_id AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN  "' . date('Y-m-d', strtotime($from_date)) . '" AND  "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59" AND bk.booking_status IN (3, 7, 8)
			INNER JOIN users u ON bk.created_by = u.id 
			WHERE (bk_psg.add_type IS NULL OR bk_psg.add_type = "") AND bk_psg.deleted = 0
			GROUP BY user_id
			HAVING (TIMESTAMPDIFF(MINUTE, bk_date_entered, min_dep_time) > 1440)

			UNION
			-- THIRD QUERY: Prior bookings <= 1440 minutes
			SELECT 
				u.last_name, u.user_name, u.id AS user_id,
				COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
				COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
				COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
				COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
				COUNT(bk.id) AS total,
				SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
				SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi")), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi"))) AS my_bk,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND bk.booking_status IN (3, 7, 8)), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi") AND booking_status IN (3, 7, 8))) AS com_my_bk,
				
				-- KHACH HANG BOOK
				IF(bk.contact_name NOT IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao") 
				AND bk.contact_name IS NOT NULL 
				AND bk.contact_name != ""
				AND NOT EXISTS(SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")), 1, 0) AS khach_hang_bk,

				IF(bk.contact_name NOT IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao") 
				AND bk.contact_name IS NOT NULL 
				AND bk.contact_name != "" 
				AND bk.booking_status IN (3, 7, 8)
				AND NOT EXISTS(SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")), 1, 0) AS com_khach_hang_bk,
				-- THAM KHAO
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Tham Khao")), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Tham Khao"))) AS tham_khao_bk,
				SUM(IFNULL((SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = "contact_name" AND before_value_string IN ("Tham Khao") AND bk.booking_status IN (3, 7, 8)), 0) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ("Tham Khao") AND booking_status IN (3, 7, 8))) AS com_tham_khao_bk,

				0 AS bk_1_3_ticket,
				0 AS com_1_3ticket_qty,
				0 AS com_1_3ticket,
				0 AS bk_1to3ticket_sales,
				0 AS bk_4_8_ticket,
				0 AS com_4_8ticket_qty,
				0 AS com_4_8ticket,
				0 AS bk_4to8ticket_sales,
				COUNT(bk.id) AS prior_bk,
				SUM(IF(bk.booking_status IN (3, 7, 8), 1, 0)) AS com_prior_bk,
				SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_prior_ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount), 0)) AS prior_bk_sales,
				
				SUM(IFNULL((SELECT IF(SUM(quantity), 1, 0) FROM ec_booking_details WHERE booking_id = bk.id AND bk.ticket_type = 2 AND deleted = 0), 0)) AS bk_inter_ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
				SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_amount - bk.total_bought_amount - (SELECT (IFNULL(luggage_purchase, 0)) + (IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id ORDER BY bk.date_entered DESC LIMIT 1), 0)) AS bk_inter_ticket_sales,

				0 AS inbound,
				0 AS missed,
				0 AS inbound_bk,

				(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_flight_bookings bk
				LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
			WHERE u.title = "Bot" 
				AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN  "' . date('Y-m-d', strtotime($from_date)) . '" AND  "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59"
				AND bk.deleted = 0
			GROUP BY bk.id
			HAVING (TIMESTAMPDIFF(MINUTE, bk_date_entered, min_dep_time) <= 1440)
			
			UNION
			-- FOURTH QUERY: Unknown bookings
			SELECT
				"Booking chưa xác định" AS last_name, 
				"" AS user_name, 
				"BK_UNK" AS user_id,
				COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
				COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
				COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
				COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
				COUNT(bk.id) AS total,
				SUM(IF(bk.booking_status = 8, bk.total_amount - bk.total_bought_amount - bk.luggage_fee, 0)) AS total_sales,
				SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
				0 AS my_bk,
				0 AS com_my_bk,
				0 AS khach_hang_bk,
				0 AS com_khach_hang_bk,
				0 AS tham_khao_bk,
				0 AS com_tham_khao_bk,
				0 AS bk_1_3_ticket,
				0 AS com_1_3ticket_qty,
				0 AS com_1_3ticket,
				0 AS bk_1to3ticket_sales,
				0 AS bk_4_8_ticket,
				0 AS com_4_8ticket_qty,
				0 AS com_4_8ticket,
				0 AS bk_4to8ticket_sales,
				0 AS prior_bk,
				0 AS com_prior_bk,
				0 AS com_prior_ticket,
				0 AS prior_bk_sales,

				0 AS bk_inter_ticket,
				0 AS com_inter_ticket_qty,
				0 AS com_inter_ticket,
				0 AS bk_inter_ticket_sales,

				0 AS inbound,
				0 AS missed,
				0 AS inbound_bk,

				(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
				DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			FROM ec_flight_bookings bk
				LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
			WHERE DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN  "' . date('Y-m-d', strtotime($from_date)) . '" AND  "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59"
				AND (
					(u.title = "Bot" AND LOWER(bk.contact_name) IN ("tim chuyen bay", "callnow", "call now"))
					OR u.title <> "Bot"
				)
				AND bk.deleted = 0 
			GROUP BY bk.id
			HAVING (TIMESTAMPDIFF(MINUTE, bk_date_entered, min_dep_time) > 1440)

			UNION
			-- FIFTH QUERY: Calls
			SELECT 
				u.last_name, u.user_name, u.id AS user_id,
				0 AS bk_confirmed,
				0 AS bk_printed,
				0 AS bk_completed,
				0 AS bk_cancelled,
				0 AS total,
				0 AS total_sales,
				0 AS total_ticket,
				0 AS my_bk,
				0 AS com_my_bk,
				0 AS khach_hang_bk,
				0 AS com_khach_hang_bk,
				0 AS tham_khao_bk,
				0 AS com_tham_khao_bk,
				0 AS bk_1_3_ticket,
				0 AS com_1_3ticket_qty,
				0 AS com_1_3ticket,
				0 AS bk_1to3ticket_sales,
				0 AS bk_4_8_ticket,
				0 AS com_4_8ticket_qty,
				0 AS com_4_8ticket,
				0 AS bk_4to8ticket_sales,
				0 AS prior_bk,
				0 AS com_prior_bk,
				0 AS com_prior_ticket,
				0 AS prior_bk_sales,
				0 AS bk_inter_ticket,
				0 AS com_inter_ticket_qty,
				0 AS com_inter_ticket,
				0 AS bk_inter_ticket_sales,
				
				COUNT(IF(c.direction = "inbound", c.id, NULL)) AS inbound,
				COUNT(IF(c.direction = "missed", c.id, NULL)) AS missed,
				COUNT(IF(c.direction = "inbound" AND c.booking_id IS NOT NULL AND c.booking_id <> "", c.id, NULL)) AS inbound_bk,
				
				"" AS min_dep_time,
				"" AS bk_date_entered
			FROM calls c
			LEFT JOIN users u ON c.call_sources = u.last_name AND c.deleted = 0
			WHERE u.title = "Bot" 
			AND DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN  "' . date('Y-m-d', strtotime($from_date)) . '" AND  "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59"
			AND c.deleted = 0
			GROUP BY last_name
		) AS tmp
		GROUP BY user_id
		ORDER BY total_sales DESC';

			// if($GLOBALS['current_user']->user_name == 'admin'){
			// 	pr($sql);
			// }

			$res = $this->bean->db->query($sql);

			// $debug_data = array();
			// while ($row = $this->bean->db->fetchByAssoc($res)) {
			// 	$debug_data[] = $row;
			// }
			// echo "<pre>";
			// print_r($debug_data);
			// echo "</pre>";
			// exit;

			$html = '<tbody><form id="booking_search" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=ListView" target="_blank">';
			$i = 0;

			$total_completed = $total_cancelled = $total = $total_sale = $total_sale_qty = 0;
			$total_sale_ticket = $total_my_bk = $total_prior_bk = $total_1_3ticket_bk = 0;
			$total_4_8ticket_bk = $total_com_mybk = 0;
			$total_comprior = $total_com1_3ticket = $total_com4_8ticket = 0;
			$total_inter_bk = $total_com_inter_ticket_qty = $total_com_inter_ticket = $total_inter_ticket_sales = 0;
			$total_ticket_prior_bk = $total_sale_prior_bk = $total_ticket_1_3bk = $total_sale_1_3bk = $total_ticket_4_8bk = $total_sale_4_8bk = 0;
			$total_ticket_inter = $total_sale_inter = 0;
			$total_khach_hang_bk = 0;
			$total_com_khach_hang_bk = 0;
			$total_inbound = $total_missed = 0;
			$total_tham_khao_bk = 0;
			$total_com_tham_khao_bk = 0;
			$sales = array();
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$total_sale += $row['total_sales'];
				$total_sale_qty += ($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']);
				$total_sale_ticket += ($row['total_ticket']);
				$total_prior_bk += $row['prior_bk'];
				$total_1_3ticket_bk += $row['bk_1_3_ticket'];
				$total_4_8ticket_bk += $row['bk_4_8_ticket'];

				$total_tham_khao_bk += $row['tham_khao_bk'];
				$total_com_tham_khao_bk += $row['com_tham_khao_bk'];
				$total_khach_hang_bk += $row['khach_hang_bk'];
				$total_com_khach_hang_bk += $row['com_khach_hang_bk'];

				$total_com_mybk += $row['com_my_bk'];
				$total_comprior += $row['com_prior_bk'];
				$total_com1_3ticket += $row['com_1_3ticket_qty'];
				$total_com4_8ticket += $row['com_4_8ticket_qty'];

				$total_inter_bk += $row['bk_inter_ticket'];
				$total_com_inter_ticket_qty += $row['com_inter_ticket_qty'];
				$total_com_inter_ticket += $row['com_inter_ticket'];
				$total_inter_ticket_sales += $row['bk_inter_ticket_sales'];

				$total_ticket_prior_bk += $row['com_prior_ticket'];
				$total_sale_prior_bk += $row['prior_bk_sales'];

				$total_ticket_1_3bk += $row['com_1_3ticket'];
				$total_sale_1_3bk += $row['bk_1to3ticket_sales'];
				$total_ticket_4_8bk += $row['com_4_8ticket'];
				$total_sale_4_8bk += $row['bk_4to8ticket_sales'];

				$total_ticket_inter += $row['com_inter_ticket'];
				$total_sale_inter += $row['bk_inter_ticket_sales'];

				if (!empty($row['user_name'])) {
					$total_completed += $row['bk_completed'];
					$total_cancelled += $row['bk_cancelled'];
					$total += $row['total'];
					$total_my_bk += $row['my_bk'];

					$total_ticket = '<a href="#" onclick="' . (empty($row['user_name']) ? 'document.getElementById(\'contact_name_advanced_OPER\').setAttribute(\'name\', \'contact_name_advanced_OPER\'); document.getElementById(\'contact_name_advanced\').setAttribute(\'name\', \'contact_name_advanced\');document.getElementById(\'created_by_name_advanced\').removeAttribute(\'name\'); ' : 'document.getElementById(\'contact_name_advanced_OPER\').removeAttribute(\'name\'); document.getElementById(\'contact_name_advanced\').removeAttribute(\'name\'); document.getElementById(\'created_by_name_advanced\').setAttribute(\'name\', \'created_by_name_advanced\'); document.getElementById(\'created_by_name_advanced\').value = \'' . $row['user_name'] . '\';') . 'document.getElementById(\'booking_status_advanced1\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced2\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced3\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_search\').submit(); return false;">' . ($row['total_ticket']) . '</a>';
				} else {
					$total_ticket = $row['total_ticket'];
				}

				$sales[$row['user_id']] = $row['total_sales'];

				if ($row['tham_khao_bk'] == 0) {
					$tham_khao_bk = 0;
				} else {
					$tham_khao_bk = format_number($row['tham_khao_bk']);
				}

				if ($row['com_tham_khao_bk'] == 0) {
					$com_tham_khao_bk = 0;
				} else {
					$com_tham_khao_bk = format_number($row['com_tham_khao_bk']);
				}

				if ($row['khach_hang_bk'] == 0) {
					$khach_hang_bk = 0;
				} else {
					$khach_hang_bk = format_number($row['khach_hang_bk']);
				}

				if ($row['com_khach_hang_bk'] == 0) {
					$com_khach_hang_bk = 0;
				} else {
					$com_khach_hang_bk = format_number($row['com_khach_hang_bk']);
				}

				// Booking booker
				if ($row['my_bk'] == 0) {
					$my_bk = 0;
				} else {
					$my_bk = format_number($row['my_bk']);
				}

				if ($row['com_my_bk'] == 0) {
					$com_my_bk = 0;
				} else {
					$com_my_bk = format_number($row['com_my_bk']);
				}

				// Vé cận
				if ($row['prior_bk'] == 0) {
					$prior_bk = 0;
				} else {
					$prior_bk = format_number($row['prior_bk']);
				}

				if ($row['com_prior_bk'] == 0) {
					$com_prior_bk = 0;
				} else {
					$com_prior_bk = format_number($row['com_prior_bk']);
				}

				$prior_bk_sales = format_number($row['prior_bk_sales']);

				$denominator_total = ($row['total'] == 0 || empty($row['total'])) ? 1 : $row['total'];

				// CALLS
				$total_inbound += (int) $row['c_inbound'];
				$total_missed += (int) $row['c_missed'];

				$html .= '
				<tr>
					<td class="text-start fw-semibold">' . str_replace(".", " ", $row['last_name']) . '</td>

					<td colspan="2" class="text-start">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total text-start">' . format_number($row['total_sales']) . '</div>
							<div class="hide-mobile total_percent text-end">&nbsp;&nbsp;($SALE_PER' . $row['user_id'] . ')</div>
						</div>
					</td>

					<td class="text-center total_ticket">' . $total_ticket . '</td>
					<td class="text-center fw-semibold color-blue">' . format_number($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) . '</td>
					
					<td colspan="2">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total_percent text-start">(' . format_number(($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) / $denominator_total * 100) . '%)</div>
							<div class="hide-mobile total text-end color-red fw-semibold">&nbsp;&nbsp;' . format_number($row['total']) . '</div>
						</div>
					</td>
					
					<td class="text-end">
						<span class="show_detail_bk show_detail" type="show_booker_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $my_bk . '&nbsp;/&nbsp;' . $com_my_bk . '</span>
					</td>
					
					<td class="text-end">
						<span class="show_detail_bk show_detail" type="show_khachhang_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $khach_hang_bk . '&nbsp;/&nbsp;' . $com_khach_hang_bk . '</span>
					</td>
					<td class="text-end">
						<span class="show_detail_bk show_detail" type="show_thamkhao_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $tham_khao_bk . '&nbsp;/&nbsp;' . $com_tham_khao_bk . '</span>
					</td>

					<td class="text-end c_inbound">
						<span class="show_detail_bk show_detail" sname="' . $row['last_name'] . '" type="show_detail_call" direction="inbound" user="' . $row['user_id'] . '">' . $row['c_inbound'] . ' / ' . $row['c_inbound_bk'] . '</span>
					</td>
					<td class="text-end c_missed">
						<span class="show_detail_bk show_detail" sname="' . $row['last_name'] . '" type="show_detail_call" direction="missed" user="' . $row['user_id'] . '">' . $row['c_missed'] . '</span>
					</td>
					
					<td class="text-end"><span class="show_detail_bk show_detail" sname="' . $row['last_name'] . '" type="show_prior_bk" user="' . $row['user_id'] . '">' . $prior_bk . '&nbsp;/&nbsp;' . $com_prior_bk . '</span></td>
					<td colspan="2">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="com_prior_ticket text-start">(' . format_number($row['com_prior_ticket']) . '&nbsp;vé)</div>
							<div class="prior_bk_sales text-end">' . $prior_bk_sales . '</div>
						</div>
					</td>

					<td class="text-end"><span class="show_detail_bk show_detail" sname="' . $row['last_name'] . '" type="show_3ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_1_3_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_1_3ticket_qty']) . '</span></td>
					<td colspan="2">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="com_1_3ticket text-start">(' . format_number($row['com_1_3ticket']) . '&nbsp;vé)</div>
							<div class="bk_1to3ticket_sales text-end">' . format_number($row['bk_1to3ticket_sales']) . '</div>
						</div>
					</td>

					<td class="text-end"><span class="show_detail_bk show_detail" sname="' . $row['last_name'] . '" type="show_4to8ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_4_8_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_4_8ticket_qty']) . '</span></td>
					<td colspan="2">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="com_4_8ticket text-start">(' . format_number($row['com_4_8ticket']) . '&nbsp;vé)</div>
							<div class="new_4to8ticket_sales text-end">' . format_number($row['bk_4to8ticket_sales']) . '</div>
						</div>
					</td>

					<td class="text-end"><span class="show_detail_bk show_detail inter" sname="' . $row['last_name'] . '" type="show_inter_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_inter_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_inter_ticket_qty']) . '</span></td>
					<td colspan="2" class="inter">
						<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="com_inter_ticket text-start">(' . format_number($row['com_inter_ticket']) . '&nbsp;vé)</div>
							<div class="new_inter_ticket_sales text-end">' . format_number($row['bk_inter_ticket_sales']) . '</div>
						</div>
					</td>

					<td class="text-end bk_cancelled">' . format_number($row['bk_cancelled']) . '</td>
					<td class="text-end bk_cancelled_percent">' . round($row['bk_cancelled'] / $denominator_total * 100, 1) . '%</td>
				</tr>';
				$i++;
			}

			foreach ($sales as $flight => $value) {
				$denominator = ($total_sale == 0 || empty($total_sale)) ? 1 : $total_sale;
				$html = str_replace('$SALE_PER' . $flight, format_number($value / $denominator * 100) . '%', $html);
			}

			$html .= '<input type="hidden" name="searchFormTab" value="advanced_search">';
			$html .= '<input type="hidden" name="module" value="EC_Flight_Bookings">';
			$html .= '<input type="hidden" name="action" value="ListView">';
			$html .= '<input type="hidden" name="query" value="true">';
			$html .= '<input type="hidden" name="date_entered_advanced" value="' . $_POST['from_date'] . '">';
			$html .= '<input type="hidden" name="date_entered_advanced_upperbound" value="' . $_POST['to_date'] . '">';
			$html .= '<input type="hidden" name="created_by_name_advanced" id="created_by_name_advanced" />';
			$html .= '<input type="hidden" name="contact_name_advanced_OPER" id="contact_name_advanced_OPER" value="IN"/>';
			$html .= '<input type="hidden" name="contact_name_advanced" id="contact_name_advanced" value="tim chuyen bay, call now, callnow"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced1" value="8"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced2" value="7"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced3" value="3"/>';

			$html .= '<input type="hidden" name="from" value="bkqtyreport"/>';
			$html .= '</form></tbody>';

			$html .= '<tfoot>
						<tr class="footer-tr">
							<td colspan="1" class="text-center fw-semibold">Tổng cộng</td>
							<td colspan="2" class="text-end color-red fw-semibold">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_sale">' . format_number($total_sale) . '</div>
									<div class="hide-mobile total_sale_percent">&nbsp;&nbsp;(100%)</div>
								</div>
							</td>
							<td class="text-end color-red fw-semibold total_sale_ticket">' . $total_sale_ticket . '</td>
							<td class="text-end total_sale_qty">' . $total_sale_qty . '</td>
							<td colspan="2" class="text-end total__booking-today"><span class="show_detail_total show_detail" title="Chi tiết booking trong ngày">' . format_number($total) . '</span></td>
							<td class="text-end total_my_bk-today">' . format_number($total_my_bk) . '&nbsp;/&nbsp;' . format_number($total_com_mybk) . '</td>
							<td class="text-end">' . format_number($total_khach_hang_bk) . '&nbsp;/&nbsp;' . format_number($total_com_khach_hang_bk) . '</td>
							<td class="text-end">' . format_number($total_tham_khao_bk) . '&nbsp;/&nbsp;' . format_number($total_com_tham_khao_bk) . '</td>

							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_inbound . '</td>
							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_missed . '</td>

							<td class="text-end total_prior_bk-today">' . format_number($total_prior_bk) . '&nbsp;/&nbsp' . format_number($total_comprior) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_prior_bk">' . format_number($total_ticket_prior_bk) . '</div>
									<div class="total_sale_prior_bk">' . format_number($total_sale_prior_bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_1_3">' . format_number($total_1_3ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com1_3ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_1_3bk">' . format_number($total_ticket_1_3bk) . '</div>
									<div class="total_sale_1_3bk">' . format_number($total_sale_1_3bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_4_8">' . format_number($total_4_8ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com4_8ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_4_8bk">' . format_number($total_ticket_4_8bk) . '</div>
									<div class="total_sale_4_8bk">' . format_number($total_sale_4_8bk) . '</div>
								</div>
							</td>

							<td class="text-end" style="background-color: #8BE8E5;">' . format_number($total_inter_bk) . '&nbsp;/&nbsp;' . format_number($total_com_inter_ticket_qty) . '</td>
							<td colspan="2" style="background-color: #8BE8E5;" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_inter">' . format_number($total_ticket_inter) . '</div>
									<div class="total_sale_inter">' . format_number($total_sale_inter) . '</div>
								</div>
							</td>
							
							<td style="text-align: right; background-color: #E94560; color: #fff">' . format_number($total_cancelled) . '</td>
							<td style="text-align: right; background-color: #E94560; color: #fff">' . round($total_cancelled / ($total > 0 ? $total : 1) * 100, 1) . '%</td>
						</tr>
					</tfoot>';

			return $html;
		} catch (Exception $e) {
			error_log("Exception in genBKSale: " . $e->getMessage());
			return '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	}

	function genBKSale_compare($from_date, $to_date)
	{
		// Chuẩn hoá khoảng current theo đúng format bạn đang dùng
		$cur_from_day = date('Y-m-d', strtotime($from_date));
		$cur_to_day   = date('Y-m-d', strtotime($to_date));

		$cur_from = $cur_from_day;               // 'YYYY-mm-dd'
		$cur_to   = $cur_to_day . ' 23:59:59';   // 'YYYY-mm-dd 23:59:59'
		$sel = $_POST['optionRadio'] ?? ($_POST['date_select'] ?? 'today');

		// --- tính prev_from / prev_to ---
		switch ($sel) {
			// NGÀY & TUẦN: dịch nguyên cửa sổ -7 ngày
			case 'today':
			case 'yesterday':
			case 'daybefore':
			case 'this_week':
			case 'previous_week':
				$prev_from = date('Y-m-d', strtotime("$cur_from_day -7 days"));
				$prev_to   = date('Y-m-d', strtotime("$cur_to_day -7 days")) . ' 23:59:59';
				break;
			// THÁNG: tính theo ranh giới tháng trước (tránh lỗi 31 → 01)
			case 'this_month':
			case 'previous_month':
				$base      = strtotime("$cur_from_day -1 month");
				$prev_from = date('Y-m-01', $base);
				$prev_to   = date('Y-m-t',  $base) . ' 23:59:59';
				break;
			// QUÝ: tìm đầu quý hiện tại rồi lùi 1 quý
			case 'quarter_this':
			case 'quarter_previous': {
					$ts = strtotime($cur_from_day);
					$y  = (int)date('Y', $ts);
					$m  = (int)date('n', $ts);
					$qStartMonth = (int)(floor(($m - 1) / 3) * 3 + 1);
					$curQStart   = strtotime(sprintf('%04d-%02d-01', $y, $qStartMonth));
					$prevQStart  = strtotime('-3 months', $curQStart);
					$prev_from   = date('Y-m-d', $prevQStart);
					$prev_to     = date('Y-m-d', strtotime('+3 months -1 day', $prevQStart)) . ' 23:59:59';
					break;
				}
				// NĂM: 01-01 .. 31-12 của năm trước
			case 'this_year':
			case 'previous_year':
				$y0        = (int)date('Y', strtotime($cur_from_day)) - 1;
				$prev_from = sprintf('%04d-01-01', $y0);
				$prev_to   = sprintf('%04d-12-31 23:59:59', $y0);
				break;

			// MẶC ĐỊNH: cửa sổ liền kề cùng độ dài ngay trước current
			default:
				$days = (int)((strtotime($cur_to_day) - strtotime($cur_from_day)) / 86400) + 1; // inclusive
				$prev_end_day   = date('Y-m-d', strtotime("$cur_from_day -1 day"));
				$prev_start_day = date('Y-m-d', strtotime("$prev_end_day -" . ($days - 1) . " days"));
				$prev_from = $prev_start_day;
				$prev_to   = $prev_end_day . ' 23:59:59';
				break;
		}

		try {
			$sql = "
				SELECT
					period,
					last_name,
					user_name,
					user_id,
					SUM(bk_confirmed) AS bk_confirmed,
					SUM(bk_printed) AS bk_printed,
					SUM(bk_completed) AS bk_completed,
					SUM(bk_cancelled) AS bk_cancelled,
					SUM(total) AS total,
					SUM(total_sales) AS total_sales,
					SUM(total_ticket) AS total_ticket,
					SUM(my_bk) AS my_bk,
					SUM(com_my_bk) AS com_my_bk,
					SUM(khach_hang_bk) AS khach_hang_bk,
					SUM(com_khach_hang_bk) AS com_khach_hang_bk,
					SUM(tham_khao_bk) AS tham_khao_bk,
					SUM(com_tham_khao_bk) AS com_tham_khao_bk,
					SUM(bk_1_3_ticket) AS bk_1_3_ticket,
					SUM(com_1_3ticket_qty) AS com_1_3ticket_qty,
					SUM(com_1_3ticket) AS com_1_3ticket,
					SUM(bk_1to3ticket_sales) AS bk_1to3ticket_sales,
					SUM(bk_4_8_ticket) AS bk_4_8_ticket,
					SUM(com_4_8ticket_qty) AS com_4_8ticket_qty,
					SUM(com_4_8ticket) AS com_4_8ticket,
					SUM(bk_4to8ticket_sales) AS bk_4to8ticket_sales,
					SUM(prior_bk) AS prior_bk,
					SUM(com_prior_bk) AS com_prior_bk,
					SUM(com_prior_ticket) AS com_prior_ticket,
					SUM(prior_bk_sales) AS prior_bk_sales,
					SUM(bk_inter_ticket) AS bk_inter_ticket,
					SUM(com_inter_ticket_qty) AS com_inter_ticket_qty,
					SUM(com_inter_ticket) AS com_inter_ticket,
					SUM(bk_inter_ticket_sales) AS bk_inter_ticket_sales,
					SUM(inbound) AS c_inbound,
					SUM(missed) AS c_missed,
					SUM(inbound_bk) AS c_inbound_bk
				FROM
				(
					-- BLOCK 1: Main bookings > 1440 
					SELECT
						CASE
							WHEN
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}' THEN
							'current'
							ELSE
							'previous'
						END AS period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
						SUM(IF(bk.booking_status IN (3, 7, 8),(SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
						SUM(
							IFNULL((
								SELECT COUNT(DISTINCT parent_id)
								FROM ec_flight_bookings_audit
								WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
							), 0) 
							+ 
							(SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi'))
						) AS my_bk,
						SUM(
							IFNULL(
							(
								SELECT COUNT(DISTINCT parent_id)
								FROM ec_flight_bookings_audit
								WHERE parent_id = bk.id AND field_name = 'contact_name'
								AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
								AND bk.booking_status IN (3, 7, 8)
							), 0) 
							+ 
							(
								SELECT COUNT(DISTINCT id)
								FROM ec_flight_bookings
								WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi') AND booking_status IN (3, 7, 8)
							)
						) AS com_my_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS khach_hang_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND bk.booking_status IN (3, 7, 8)
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS com_khach_hang_bk,
						SUM(
							IFNULL(
							(SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Tham Khao')),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Tham Khao'))
						) AS tham_khao_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Tham Khao')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Tham Khao')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_tham_khao_bk,
						SUM(IF(bk.total_qty <= 3, 1, 0)) AS bk_1_3_ticket,
						SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), 1, 0)) AS com_1_3ticket_qty,
						SUM(IF(bk.total_qty <= 3 AND bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_1_3ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty <= 3, bk.total_amount - bk.total_bought_amount, 0)) AS bk_1to3ticket_sales,
						SUM(IF(bk.total_qty >= 4 AND bk.total_qty <= 8, 1, 0)) AS bk_4_8_ticket,
						SUM(
							IF(
							bk.total_qty >= 4
							AND bk.total_qty <= 8
							AND bk.booking_status IN (3, 7, 8),
							1,
							0
							)
						) AS com_4_8ticket_qty,
						SUM(
							IF(
							bk.total_qty >= 4
							AND bk.total_qty <= 8
							AND bk.booking_status IN (3, 7, 8),
							bk.total_qty,
							0
							)
						) AS com_4_8ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.total_qty >= 4 AND bk.total_qty <= 8, bk.total_amount - bk.total_bought_amount, 0)) AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						SUM(IFNULL((SELECT IF(SUM(quantity), 1, 0) FROM ec_booking_details WHERE booking_id = bk.id AND bk.ticket_type = 2 AND deleted = 0), 0)) AS bk_inter_ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8)
							AND bk.ticket_type = 2,
							bk.total_amount - bk.total_bought_amount,
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE u.title = 'Bot'
					AND (
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$prev_from}'
						AND '{$prev_to}'
						OR DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}'
						AND '{$cur_to}'
					)
					AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
						
					-- BLOCK 2: Luggage adjustments
					UNION ALL
					SELECT
						CASE
							WHEN
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}' THEN
							'current'
							ELSE
							'previous'
						END AS period,
						IF(u.title = 'Bot', u.last_name, 'Chưa xác định') AS last_name,
						IF(u.title = 'Bot', u.user_name, '') AS user_name,
						IF(u.title = 'Bot', u.id, 'BK_UNK') AS user_id,
						0 AS bk_confirmed,
						0 AS bk_printed,
						0 AS bk_completed,
						0 AS bk_cancelled,
						0 AS total,
						- SUM(IF(bk.booking_status IN (3, 7, 8), IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0), 0)) AS total_sales,
						0 AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT i.booking_id
								FROM ec_booking_itineraries i
								WHERE i.booking_id = bk.id
								AND i.deleted = 0
								AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
								AND bk.booking_status IN (3, 7, 8)
								AND bk.total_qty <= 3
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT i.booking_id
								FROM ec_booking_itineraries i
								WHERE i.booking_id = bk.id
								AND i.deleted = 0
								AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
								AND bk.booking_status IN (3, 7, 8)
								AND bk.total_qty >= 4
								AND bk.total_qty <= 9
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						- SUM(
							IF(
							TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), (SELECT MIN(departure_date) FROM ec_booking_itineraries WHERE booking_id = bk.id AND deleted = 0)) <= 1440 
							AND bk.booking_status IN (3, 7, 8),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						- SUM(
							IF(
							bk.id = (
								SELECT DISTINCT
									i.booking_id
								FROM
									ec_booking_itineraries i
								WHERE
									i.booking_id = bk.id
									AND i.deleted = 0
									AND TIMESTAMPDIFF(MINUTE, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), i.departure_date) > 1440
									AND bk.booking_status IN (3, 7, 8)
									AND bk.ticket_type = 2
							),
							IFNULL(bk_psg.luggage_purchase, 0) + IFNULL(bk_psg.luggage_purchase_inbound, 0),
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_booking_passengers bk_psg
					JOIN ec_flight_bookings bk ON bk.id = bk_psg.booking_id AND bk_psg.deleted = 0
					JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE (bk_psg.add_type IS NULL OR bk_psg.add_type = '')
					AND u.title = 'Bot'
					-- AND bk.booking_status IN (3, 7, 8)
					AND (
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$prev_from}'
						AND '{$prev_to}'
						OR DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}'
						AND '{$cur_to}'
					)
					GROUP BY period, user_id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
					
					--  BLOCK 3: Prior bookings <=1440
					UNION ALL
					SELECT
						CASE
							WHEN
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}' THEN
							'current'
							ELSE
							'previous'
						END AS period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_amount - bk.total_bought_amount, 0)) AS total_sales,
						SUM(IF(bk.booking_status IN (3, 7, 8), (SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0), 0)) AS total_ticket,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
							),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi'))
						) AS my_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_my_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS khach_hang_bk,
						IF(
							bk.contact_name NOT IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')
							AND bk.contact_name IS NOT NULL
							AND bk.contact_name <> ''
							AND bk.booking_status IN (3, 7, 8)
							AND NOT EXISTS (SELECT 1 FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Panda Po', 'Bao Gia Khach', 'Khach Hang Hoi', 'Tham Khao')),
							1,
							0
						) AS com_khach_hang_bk,
						SUM(
							IFNULL(
							(SELECT COUNT(DISTINCT parent_id) FROM ec_flight_bookings_audit WHERE parent_id = bk.id AND field_name = 'contact_name' AND before_value_string IN ('Tham Khao')),
							0
							) + (SELECT COUNT(DISTINCT id) FROM ec_flight_bookings WHERE id = bk.id AND contact_name IN ('Tham Khao'))
						) AS tham_khao_bk,
						SUM(
							IFNULL(
							(
								SELECT
									COUNT(DISTINCT parent_id)
								FROM
									ec_flight_bookings_audit
								WHERE
									parent_id = bk.id
									AND field_name = 'contact_name'
									AND before_value_string IN ('Tham Khao')
									AND bk.booking_status IN (3, 7, 8)
							),
							0
							) + (
							SELECT
								COUNT(DISTINCT id)
							FROM
								ec_flight_bookings
							WHERE
								id = bk.id
								AND contact_name IN ('Tham Khao')
								AND booking_status IN (3, 7, 8)
							)
						) AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						COUNT(bk.id) AS prior_bk,
						SUM(IF(bk.booking_status IN (3, 7, 8), 1, 0)) AS com_prior_bk,
						SUM(IF(bk.booking_status IN (3, 7, 8), bk.total_qty, 0)) AS com_prior_ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount), 0)) AS prior_bk_sales,
						SUM(
							IFNULL(
							(
								SELECT
									IF(SUM(quantity), 1, 0)
								FROM
									ec_booking_details
								WHERE
									booking_id = bk.id
									AND bk.ticket_type = 2
									AND deleted = 0
							),
							0
							)
						) AS bk_inter_ticket,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, 1, 0)) AS com_inter_ticket_qty,
						SUM(IF(bk.booking_status IN (3, 7, 8) AND bk.ticket_type = 2, bk.total_qty, 0)) AS com_inter_ticket,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8)
							AND bk.ticket_type = 2,
							bk.total_amount - bk.total_bought_amount - (
								SELECT
									(IFNULL(luggage_purchase, 0)) + (IFNULL(luggage_purchase_inbound, 0))
								FROM
									ec_booking_passengers
								WHERE
									deleted = 0
									AND booking_id = bk.id
								ORDER BY
									bk.date_entered DESC
									LIMIT 1
							),
							0
							)
						) AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE u.title = 'Bot'
						AND (
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$prev_from}' AND '{$prev_to}'
							OR 
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}'
						)
						AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) <= 1440 
					
					-- BLOCK 4: Unknown bookings >1440 
					UNION ALL
					SELECT
						CASE
							WHEN
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}' THEN
							'current'
							ELSE
							'previous'
						END AS period,
						'Booking chưa xác định' AS last_name,
						'' AS user_name,
						'BK_UNK' AS user_id,
						COUNT(IF(bk.booking_status = 3, bk.id, NULL)) AS bk_confirmed,
						COUNT(IF(bk.booking_status = 7, bk.id, NULL)) AS bk_printed,
						COUNT(IF(bk.booking_status = 8, bk.id, NULL)) AS bk_completed,
						COUNT(IF(bk.booking_status = 4, bk.id, NULL)) AS bk_cancelled,
						COUNT(bk.id) AS total,
						SUM(IF(bk.booking_status = 8, bk.total_amount - bk.total_bought_amount - bk.luggage_fee, 0)) AS total_sales,
						SUM(
							IF(
							bk.booking_status IN (3, 7, 8),
							(SELECT SUM(quantity) FROM ec_booking_details WHERE booking_id = bk.id AND deleted = 0),
							0
							)
						) AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,
						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						0 AS bk_inter_ticket_sales,
						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk,
						(SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.booking_id = bk.id AND i.deleted = 0) AS min_dep_time,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM ec_flight_bookings bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
					WHERE
						(
							(u.title = 'Bot' AND LOWER(bk.contact_name) IN ('tim chuyen bay', 'callnow', 'call now'))
							OR u.title <> 'Bot'
						)
						AND (
							DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$prev_from}' AND '{$prev_to}'
							OR DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}'
						)
						AND bk.deleted = 0
					GROUP BY period, bk.id
					HAVING TIMESTAMPDIFF(MINUTE, ts_local, min_dep_time) > 1440 
					
					-- BLOCK 5: Calls
					UNION ALL
					SELECT
						CASE
							WHEN
							DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}' THEN
							'current'
							ELSE
							'previous'
						END AS period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
						0 AS bk_confirmed,
						0 AS bk_printed,
						0 AS bk_completed,
						0 AS bk_cancelled,
						0 AS total,
						0 total_sales,
						0 AS total_ticket,
						0 AS my_bk,
						0 AS com_my_bk,
						0 AS khach_hang_bk,
						0 AS com_khach_hang_bk,
						0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS bk_1_3_ticket,
						0 AS com_1_3ticket_qty,
						0 AS com_1_3ticket,

						0 AS bk_1to3ticket_sales,
						0 AS bk_4_8_ticket,
						0 AS com_4_8ticket_qty,
						0 AS com_4_8ticket,
						0 AS bk_4to8ticket_sales,
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,
						0 AS bk_inter_ticket,
						0 AS com_inter_ticket_qty,
						0 AS com_inter_ticket,
						0 AS bk_inter_ticket_sales,
						SUM(CASE WHEN c.direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN c.direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk,
						-- COUNT(IF(c.direction = 'inbound', c.id, NULL)) AS inbound,
						-- COUNT(IF(c.direction = 'missed', c.id, NULL)) AS missed,
						-- COUNT(IF(c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '', c.id, NULL)) AS inbound_bk,
						NULL AS min_dep_time,
						DATE_ADD(c.date_entered, INTERVAL 7 HOUR) AS ts_local
					FROM
						calls c
						LEFT JOIN users u ON c.call_sources = u.last_name
						AND c.deleted = 0
					WHERE
						u.title = 'Bot'
						AND (
							DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN '{$prev_from}' AND '{$prev_to}'
							OR 
							DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN '{$cur_from}' AND '{$cur_to}'
						)
						AND c.deleted = 0
					GROUP BY period, u.id
				) agg
				GROUP BY period, user_id
				ORDER BY
				CASE
					WHEN period = 'current' THEN
						1
					ELSE
						2
				END,
				total_sales DESC";

			// if($GLOBALS['current_user']->user_name == 'admin'){
			// pr($sql);
			// }

			$res = $this->bean->db->query($sql);

			$html = '<tbody><form id="booking_search" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=ListView" target="_blank">';
			$i = 0;
			$mark_current = false;
			$mark_previous = false;

			$total_completed = $total_cancelled = $total = $total_sale = $total_sale_qty = 0;
			$total_sale_ticket = $total_my_bk = $total_prior_bk = $total_1_3ticket_bk = 0;
			$total_4_8ticket_bk = $total_com_mybk = 0;
			$total_comprior = $total_com1_3ticket = $total_com4_8ticket = 0;
			$total_inter_bk = $total_com_inter_ticket_qty = $total_com_inter_ticket = $total_inter_ticket_sales = 0;
			$total_ticket_prior_bk = $total_sale_prior_bk = $total_ticket_1_3bk = $total_sale_1_3bk = $total_ticket_4_8bk = $total_sale_4_8bk = 0;
			$total_ticket_inter = $total_sale_inter = 0;
			$total_khach_hang_bk = 0;
			$total_com_khach_hang_bk = 0;
			$total_inbound = $total_missed = 0;
			$total_tham_khao_bk = 0;
			$total_com_tham_khao_bk = 0;
			$sales = array();

			// cộng dồn theo nhóm (period)
			$sub = [
				'total_sales' => 0,
				'total_ticket' => 0,
				'sale_qty' => 0,
				'total' => 0,
				'my_bk' => 0,
				'com_my_bk' => 0,
				'khach_hang_bk' => 0,
				'com_khach_hang_bk' => 0,
				'tham_khao_bk' => 0,
				'com_tham_khao_bk' => 0,
				'c_inbound' => 0,
				'c_missed' => 0,
				'c_inbound_bk' => 0,
				'prior_bk' => 0,
				'com_prior_bk' => 0,
				'com_prior_ticket' => 0,
				'prior_bk_sales' => 0,
				'bk_1_3_ticket' => 0,
				'com_1_3ticket_qty' => 0,
				'com_1_3ticket' => 0,
				'bk_1to3ticket_sales' => 0,
				'bk_4_8_ticket' => 0,
				'com_4_8ticket_qty' => 0,
				'com_4_8ticket' => 0,
				'bk_4to8ticket_sales' => 0,
				'bk_inter_ticket' => 0,
				'com_inter_ticket_qty' => 0,
				'com_inter_ticket' => 0,
				'bk_inter_ticket_sales' => 0,
				'bk_cancelled' => 0,
			];
			$periodNow = null;
			$range = [
				'current'  => ['from'=> $cur_from_day, 'to'=> $cur_to_day],
				'previous' => ['from'=> $prev_from,    'to'=> date('Y-m-d', strtotime($prev_to))],
			];

			// in subtotal của 1 period
			$printSubtotal = function (string $period, array $s, array $range) use (&$html) {
				$percent = $s['total'] ? round(($s['sale_qty'] / $s['total']) * 100, 1) : 0;
				$from_date = $range[$period]['from'];
    			$to_date   = $range[$period]['to'];

				$html .= '
					<tr class="bg-light fw-semibold subtotal ' . $period . '">
						<td class="text-start">Tổng</td>

						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total">' . format_number($s['total_sales']) . '</div>
							<div class="hide-mobile total_percent text-end">&nbsp;</div>
							</div>
						</td>

						<td class="text-center">' . format_number($s['total_ticket']) . '</td>
						<td class="text-center color-blue">' . format_number($s['sale_qty']) . '</td>

						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="total_percent text-start">(' . format_number($percent) . '%)</div>
							<div class="hide-mobile total text-end color-red fw-semibold show_detail_total show_detail" from_date="' . $from_date . '" to_date="' . $to_date . '" title="Chi tiết booking trong ngày">' . format_number($s['total']) . '</div>
							</div>
						</td>

						<td class="text-end">' . $s['my_bk'] . '&nbsp;/&nbsp;' . $s['com_my_bk'] . '</td>
						<td class="text-end">' . $s['khach_hang_bk'] . '&nbsp;/&nbsp;' . $s['com_khach_hang_bk'] . '</td>
						<td class="text-end">' . $s['tham_khao_bk'] . '&nbsp;/&nbsp;' . $s['com_tham_khao_bk'] . '</td>

						<td class="text-end">' . $s['c_inbound'] . ' / ' . $s['c_inbound_bk'] . '</td>
						<td class="text-end">' . $s['c_missed'] . '</td>

						<td class="text-end">' . $s['prior_bk'] . '&nbsp;/&nbsp;' . $s['com_prior_bk'] . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_prior_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['prior_bk_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_1_3_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_1_3ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_1_3ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_1to3ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_4_8_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_4_8ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_4_8ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_4to8ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_inter_ticket']) . '&nbsp;/&nbsp;' . format_number($s['com_inter_ticket_qty']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_inter_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['bk_inter_ticket_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['bk_cancelled']) . '</td>
						<td class="text-end">' . ($s['total'] ? round($s['bk_cancelled'] / $s['total'] * 100, 1) : 0) . '%</td>
					</tr>';
			};

			// reset mảng cộng dồn nhóm
			$resetSub = function (array &$s) {
				foreach ($s as $k => $v) $s[$k] = 0;
			};

			while ($row = $this->bean->db->fetchByAssoc($res)) {
				if ($periodNow !== null && $row['period'] !== $periodNow) {
					$printSubtotal($periodNow, $sub, $range);
					$resetSub($sub);
				}
				$periodNow = $row['period'];

				$total_sale += $row['total_sales'];
				$total_sale_qty += ($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']);

				$sub['total_sales']          += (float)$row['total_sales'];
				$sub['total_ticket']         += (float)$row['total_ticket'];
				$sub['sale_qty']             += (int)$row['bk_confirmed'] + (int)$row['bk_printed'] + (int)$row['bk_completed'];
				$sub['total']                += (int)$row['total'];

				$sub['my_bk']                += (int)$row['my_bk'];
				$sub['com_my_bk']            += (int)$row['com_my_bk'];
				$sub['khach_hang_bk']        += (int)$row['khach_hang_bk'];
				$sub['com_khach_hang_bk']    += (int)$row['com_khach_hang_bk'];
				$sub['tham_khao_bk']         += (int)$row['tham_khao_bk'];
				$sub['com_tham_khao_bk']     += (int)$row['com_tham_khao_bk'];

				$sub['c_inbound']            += (int)$row['c_inbound'];
				$sub['c_missed']             += (int)$row['c_missed'];
				$sub['c_inbound_bk']         += (int)$row['c_inbound_bk'];

				$sub['prior_bk']             += (int)$row['prior_bk'];
				$sub['com_prior_bk']         += (int)$row['com_prior_bk'];
				$sub['com_prior_ticket']     += (int)$row['com_prior_ticket'];
				$sub['prior_bk_sales']       += (float)$row['prior_bk_sales'];

				$sub['bk_1_3_ticket']        += (int)$row['bk_1_3_ticket'];
				$sub['com_1_3ticket_qty']    += (int)$row['com_1_3ticket_qty'];
				$sub['com_1_3ticket']        += (int)$row['com_1_3ticket'];
				$sub['bk_1to3ticket_sales']  += (float)$row['bk_1to3ticket_sales'];

				$sub['bk_4_8_ticket']        += (int)$row['bk_4_8_ticket'];
				$sub['com_4_8ticket_qty']    += (int)$row['com_4_8ticket_qty'];
				$sub['com_4_8ticket']        += (int)$row['com_4_8ticket'];
				$sub['bk_4to8ticket_sales']  += (float)$row['bk_4to8ticket_sales'];

				$sub['bk_inter_ticket']      += (int)$row['bk_inter_ticket'];
				$sub['com_inter_ticket_qty'] += (int)$row['com_inter_ticket_qty'];
				$sub['com_inter_ticket']     += (int)$row['com_inter_ticket'];
				$sub['bk_inter_ticket_sales'] += (float)$row['bk_inter_ticket_sales'];

				$sub['bk_cancelled']         += (int)$row['bk_cancelled'];

				if (!empty($row['user_name'])) {
					$total_completed += $row['bk_completed'];
					$total_cancelled += $row['bk_cancelled'];
					$total += $row['total'];
					$total_my_bk += $row['my_bk'];

					$total_ticket = '<a href="#" onclick="' . (empty($row['user_name']) ? 'document.getElementById(\'contact_name_advanced_OPER\').setAttribute(\'name\', \'contact_name_advanced_OPER\'); document.getElementById(\'contact_name_advanced\').setAttribute(\'name\', \'contact_name_advanced\');document.getElementById(\'created_by_name_advanced\').removeAttribute(\'name\'); ' : 'document.getElementById(\'contact_name_advanced_OPER\').removeAttribute(\'name\'); document.getElementById(\'contact_name_advanced\').removeAttribute(\'name\'); document.getElementById(\'created_by_name_advanced\').setAttribute(\'name\', \'created_by_name_advanced\'); document.getElementById(\'created_by_name_advanced\').value = \'' . $row['user_name'] . '\';') . 'document.getElementById(\'booking_status_advanced1\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced2\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_status_advanced3\').setAttribute(\'name\', \'booking_status_advanced[]\'); document.getElementById(\'booking_search\').submit(); return false;">' . ($row['total_ticket']) . '</a>';
				} else {
					$total_ticket = $row['total_ticket'];
				}

				$sales[$row['user_id']] = $row['total_sales'];

				if ($row['tham_khao_bk'] == 0) {
					$tham_khao_bk = 0;
				} else {
					$tham_khao_bk = format_number($row['tham_khao_bk']);
				}

				if ($row['com_tham_khao_bk'] == 0) {
					$com_tham_khao_bk = 0;
				} else {
					$com_tham_khao_bk = format_number($row['com_tham_khao_bk']);
				}

				if ($row['khach_hang_bk'] == 0) {
					$khach_hang_bk = 0;
				} else {
					$khach_hang_bk = format_number($row['khach_hang_bk']);
				}

				if ($row['com_khach_hang_bk'] == 0) {
					$com_khach_hang_bk = 0;
				} else {
					$com_khach_hang_bk = format_number($row['com_khach_hang_bk']);
				}

				// Booking booker
				if ($row['my_bk'] == 0) {
					$my_bk = 0;
				} else {
					$my_bk = format_number($row['my_bk']);
				}

				if ($row['com_my_bk'] == 0) {
					$com_my_bk = 0;
				} else {
					$com_my_bk = format_number($row['com_my_bk']);
				}

				// Vé cận
				if ($row['prior_bk'] == 0) {
					$prior_bk = 0;
				} else {
					$prior_bk = format_number($row['prior_bk']);
				}

				if ($row['com_prior_bk'] == 0) {
					$com_prior_bk = 0;
				} else {
					$com_prior_bk = format_number($row['com_prior_bk']);
				}

				$prior_bk_sales = format_number($row['prior_bk_sales']);

				$denominator_total = ($row['total'] == 0 || empty($row['total'])) ? 1 : $row['total'];

				// CALLS
				$total_inbound += (int) $row['c_inbound'];
				$total_missed += (int) $row['c_missed'];

				if ($row['period'] == 'previous' && !$mark_previous) {
					$mark_previous = true;

					$html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Cùng kỳ</span> 
									<span class="form-label text-dark fw-semibold">
										Từ ngày: <span class="text-danger">' . date('d-m-Y', strtotime($prev_from)) . '</span>
										Đến ngày <span class="text-danger">' . date('d-m-Y', strtotime($prev_to)) . '</span>
									</span> 
								</td>
							</tr>';
				} else if ($row['period'] == 'current' && !$mark_current) {
					$mark_current = true;

					$html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">Dữ liệu</span> 
									<span class="form-label text-dark fw-semibold">
										Từ ngày: <span class="text-danger">' . date('d-m-Y', strtotime($from_date)) . '</span>
										Đến ngày <span class="text-danger">' . date('d-m-Y', strtotime($to_date)) . '</span>
									</span> 
								</td>
							</tr>';
				}

				$html .= '
						<tr>
							<td class="text-start fw-semibold">' . str_replace(".", " ", $row['last_name']) . '</td>

							<td colspan="2" class="text-start">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total text-start">' . format_number($row['total_sales']) . '</div>
									<div class="hide-mobile total_percent text-end">&nbsp;&nbsp;($SALE_PER' . $row['user_id'] . ')</div>
								</div>
							</td>

							<td class="text-center total_ticket">' . $total_ticket . '</td>
							<td class="text-center fw-semibold color-blue">' . format_number($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) . '</td>
							
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_percent text-start">(' . format_number(($row['bk_confirmed'] + $row['bk_printed'] + $row['bk_completed']) / $denominator_total * 100) . '%)</div>
									<div class="hide-mobile total text-end color-red fw-semibold">&nbsp;&nbsp;' . format_number($row['total']) . '</div>
								</div>
							</td>
							
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '"  type="show_booker_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $my_bk . '&nbsp;/&nbsp;' . $com_my_bk . '</span>
							</td>
							
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" type="show_khachhang_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $khach_hang_bk . '&nbsp;/&nbsp;' . $com_khach_hang_bk . '</span>
							</td>
							<td class="text-end">
								<span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" type="show_thamkhao_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . $tham_khao_bk . '&nbsp;/&nbsp;' . $com_tham_khao_bk . '</span>
							</td>

							<td class="text-end c_inbound">
								<span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="inbound" user="' . $row['user_id'] . '">' . $row['c_inbound'] . ' / ' . $row['c_inbound_bk'] . '</span>
							</td>
							<td class="text-end c_missed">
								<span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="missed" user="' . $row['user_id'] . '">' . $row['c_missed'] . '</span>
							</td>
							
							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_prior_bk" user="' . $row['user_id'] . '">' . $prior_bk . '&nbsp;/&nbsp;' . $com_prior_bk . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_prior_ticket text-start">(' . format_number($row['com_prior_ticket']) . '&nbsp;vé)</div>
									<div class="prior_bk_sales text-end">' . $prior_bk_sales . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_3ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_1_3_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_1_3ticket_qty']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_1_3ticket text-start">(' . format_number($row['com_1_3ticket']) . '&nbsp;vé)</div>
									<div class="bk_1to3ticket_sales text-end">' . format_number($row['bk_1to3ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_4to8ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_4_8_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_4_8ticket_qty']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_4_8ticket text-start">(' . format_number($row['com_4_8ticket']) . '&nbsp;vé)</div>
									<div class="new_4to8ticket_sales text-end">' . format_number($row['bk_4to8ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail inter" from_date="' . $range[$row['period']]['from'] . '" to_date="' . $range[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_inter_bk" user="' . $row['user_id'] . '">' . format_number($row['bk_inter_ticket']) . '&nbsp;/&nbsp;' . format_number($row['com_inter_ticket_qty']) . '</span></td>
							<td colspan="2" class="inter">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_inter_ticket text-start">(' . format_number($row['com_inter_ticket']) . '&nbsp;vé)</div>
									<div class="new_inter_ticket_sales text-end">' . format_number($row['bk_inter_ticket_sales']) . '</div>
								</div>
							</td>

							<td class="text-end bk_cancelled">' . format_number($row['bk_cancelled']) . '</td>
							<td class="text-end bk_cancelled_percent">' . round($row['bk_cancelled'] / $denominator_total * 100, 1) . '%</td>
						</tr>';
				$i++;
			}

			if ($periodNow !== null) {
				$printSubtotal($periodNow, $sub, $range); // in subtotal của current hoặc previous cuối
			}

			foreach ($sales as $flight => $value) {
				$denominator = ($total_sale == 0 || empty($total_sale)) ? 1 : $total_sale;
				$html = str_replace('$SALE_PER' . $flight, format_number($value / $denominator * 100) . '%', $html);
			}

			$html .= '<input type="hidden" name="searchFormTab" value="advanced_search">';
			$html .= '<input type="hidden" name="module" value="EC_Flight_Bookings">';
			$html .= '<input type="hidden" name="action" value="ListView">';
			$html .= '<input type="hidden" name="query" value="true">';
			$html .= '<input type="hidden" name="date_entered_advanced" value="' . $_POST['from_date'] . '">';
			$html .= '<input type="hidden" name="date_entered_advanced_upperbound" value="' . $_POST['to_date'] . '">';
			$html .= '<input type="hidden" name="created_by_name_advanced" id="created_by_name_advanced" />';
			$html .= '<input type="hidden" name="contact_name_advanced_OPER" id="contact_name_advanced_OPER" value="IN"/>';
			$html .= '<input type="hidden" name="contact_name_advanced" id="contact_name_advanced" value="tim chuyen bay, call now, callnow"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced1" value="8"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced2" value="7"/>';
			$html .= '<input type="hidden" name="booking_status_advanced[]" id="booking_status_advanced3" value="3"/>';

			$html .= '<input type="hidden" name="from" value="bkqtyreport"/>';
			$html .= '</form></tbody>';

			$html .= '<tfoot class="d-none">
						<tr class="footer-tr bg-secondary">
							<td colspan="1" class="text-center fw-semibold">Tổng cộng</td>
							<td colspan="2" class="text-end color-red fw-semibold">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_sale">' . format_number($total_sale) . '</div>
									<div class="hide-mobile total_sale_percent">&nbsp;&nbsp;(100%)</div>
								</div>
							</td>
							<td class="text-end color-red fw-semibold total_sale_ticket">' . $total_sale_ticket . '</td>
							<td class="text-end total_sale_qty">' . $total_sale_qty . '</td>
							<td colspan="2" class="text-end total__booking-today"><span class="show_detail_total show_detail" title="Chi tiết booking trong ngày">' . format_number($total) . '</span></td>
							<td class="text-end total_my_bk-today">' . format_number($total_my_bk) . '&nbsp;/&nbsp;' . format_number($total_com_mybk) . '</td>
							<td class="text-end">' . format_number($total_khach_hang_bk) . '&nbsp;/&nbsp;' . format_number($total_com_khach_hang_bk) . '</td>
							<td class="text-end">' . format_number($total_tham_khao_bk) . '&nbsp;/&nbsp;' . format_number($total_com_tham_khao_bk) . '</td>

							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_inbound . '</td>
							<td style="text-align: right; background-color: #068FFF;  color: #fff; ">' . $total_missed . '</td>

							<td class="text-end total_prior_bk-today">' . format_number($total_prior_bk) . '&nbsp;/&nbsp' . format_number($total_comprior) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_prior_bk">' . format_number($total_ticket_prior_bk) . '</div>
									<div class="total_sale_prior_bk">' . format_number($total_sale_prior_bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_1_3">' . format_number($total_1_3ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com1_3ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_1_3bk">' . format_number($total_ticket_1_3bk) . '</div>
									<div class="total_sale_1_3bk">' . format_number($total_sale_1_3bk) . '</div>
								</div>
							</td>

							<td class="text-end total_ticket_4_8">' . format_number($total_4_8ticket_bk) . '&nbsp;/&nbsp;' . format_number($total_com4_8ticket) . '</td>
							<td colspan="2" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_4_8bk">' . format_number($total_ticket_4_8bk) . '</div>
									<div class="total_sale_4_8bk">' . format_number($total_sale_4_8bk) . '</div>
								</div>
							</td>

							<td class="text-end" style="background-color: #8BE8E5;">' . format_number($total_inter_bk) . '&nbsp;/&nbsp;' . format_number($total_com_inter_ticket_qty) . '</td>
							<td colspan="2" style="background-color: #8BE8E5;" class="text-end">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_ticket_inter">' . format_number($total_ticket_inter) . '</div>
									<div class="total_sale_inter">' . format_number($total_sale_inter) . '</div>
								</div>
							</td>
							
							<td style="text-align: right; background-color: #E94560; color: #fff">' . format_number($total_cancelled) . '</td>
							<td style="text-align: right; background-color: #E94560; color: #fff">' . round($total_cancelled / ($total > 0 ? $total : 1) * 100, 1) . '%</td>
						</tr>
					</tfoot>';

			return $html;
		} catch (Exception $e) {
			error_log("Exception in genBKSale: " . $e->getMessage());
			return '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
		}
	}
}
