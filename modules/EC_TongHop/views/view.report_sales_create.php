<?php
if (!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewreport_sales_create extends SugarView
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
			$this->populateContent($smarty);
			$smarty->display('modules/EC_TongHop/tpls/report_sales_create.tpl');
		} else {
			header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không có quyền xem báo cáo này. Vui lòng liên hệ quản trị viên để được cấp quyền."));
			exit;
		}
	}

	function populateContent($smarty)
	{
		global $db, $current_user;

		$smarty->assign('MODULE_NAME', $this->bean->object_name);
		$smarty->assign('MODULE_ACTION', 'report_sales_issue');

		$from_date          = isset($_REQUEST['from_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['from_date']) : date('Y-m-d');
		$to_date            = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9\-]/', '', $_REQUEST['to_date']) : date('Y-m-d');
		$fr_date_arr        = explode('-', $from_date); // 0=>day, 1=>month, 2=>year
		$to_date_arr        = explode('-', $to_date);

		// From date
		if (!empty($from_date) && checkdate((int)$fr_date_arr[1], (int)$fr_date_arr[0], (int)$fr_date_arr[2])) {
			$from_date_sql = date('Y-m-d', strtotime($from_date));
			$from_date_value = $from_date;
		} else {
			$from_date_sql = date('Y-m-d');
			$from_date_value = date('d-m-Y');
		}
		$from_date_sql_yesterday = date('Y-m-d', strtotime($from_date_sql . ' -1 day'));
		$from_date_sql_daybefore = date('Y-m-d', strtotime($from_date_sql . ' -2 day'));
		$smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date_value)));

		// To date
		if (!empty($to_date) && checkdate((int)$to_date_arr[1], (int)$to_date_arr[0], (int)$to_date_arr[2])) {
			$to_date_sql = date('Y-m-d', strtotime($to_date));
			$to_date_value = $to_date;
		} else {
			$to_date_sql = date('Y-m-d');
			$to_date_value = date('d-m-Y');
		}
		$to_date_sql_yesterday   = date('Y-m-d', strtotime($to_date_sql . ' -1 day'));
		$to_date_sql_daybefore   = date('Y-m-d', strtotime($to_date_sql . ' -2 day'));
		$smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date_value)));

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
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'today' ? 'selected' : '') . ' value="today" fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">Hôm nay</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'yesterday' ? 'selected' : '') . ' value="yesterday" fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_week' ? 'selected' : '') . ' value="this_week" fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_week' ? 'selected' : '') . ' value="previous_week" fromdate="' . date('d-m-Y', strtotime('monday previous week')) . '" todate="' . date('d-m-Y', strtotime('sunday previous week')) . '">Tuần trước</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_month' ? 'selected' : '') . ' value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_month' ? 'selected' : '') . ' value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quarter_this' ? 'selected' : '') . ' value="quarter_this" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'quarter_previous' ? 'selected' : '') . ' value="quarter_previous" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'this_year' ? 'selected' : '') . ' value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
			'<option ' . (isset($_POST['date_select']) && (string)$_POST['date_select'] === 'previous_year' ? 'selected' : '') . ' value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
		);
		$smarty->assign('DATE_OPTION', implode('', $arr_date));

		$sel = ($_POST['date_select'] ?? 'today');
		// Helpers
		$shiftDays = function ($day, $deltaDays) {
			return date('Y-m-d', strtotime("$day $deltaDays"));
		};
		$shiftRange = function ($from, $to, $step) {
			$days = (strtotime($to) - strtotime($from)) / 86400 + 1;
			$offset = -$days * $step;

			return [
				'from' => date('Y-m-d', strtotime("$from $offset days")),
				'to'   => date('Y-m-d', strtotime("$to   $offset days")),
			];
		};
		$mkRange = function ($fromDay, $toDay) {
			return ['from' => $fromDay, 'to' => $toDay];
		};
		$mkWeekRange = function ($base, $shiftWeek = 0) {
			$monday = date('Y-m-d', strtotime("monday this week $shiftWeek week", strtotime($base)));
			$sunday = date('Y-m-d', strtotime("sunday this week $shiftWeek week", strtotime($base)));
			return ['from' => $monday, 'to' => $sunday];
		};
		$mkMonthRange = function ($base, $shiftMonth = 0) {
			$from = date('Y-m-01', strtotime("$base $shiftMonth month"));
			$to   = date('Y-m-t',  strtotime("$base $shiftMonth month"));
			return ['from' => $from, 'to' => $to];
		};
		$mkQuarterRange = function ($base, $shiftQuarter = 0) {
			$ts = strtotime("$base " . ($shiftQuarter * 3) . " months");

			$year    = (int)date('Y', $ts);
			$month   = (int)date('n', $ts);
			$quarter = (int)ceil($month / 3);

			$startMonth = ($quarter - 1) * 3 + 1;

			$from = date('Y-m-d', strtotime("$year-$startMonth-01"));
			$to   = date('Y-m-t', strtotime("$from +2 months"));

			return ['from' => $from, 'to' => $to];
		};
		$mkYearRange = function ($base, $shiftYear = 0) {
			$year = (int)date('Y', strtotime("$base $shiftYear year"));
			return [
				'from' => "$year-01-01",
				'to'   => "$year-12-31",
			];
		};

		$sel = ($_POST['date_select'] ?? 'today');
		$title_current = 'Hiện tại: mốc thời gian hôm nay, hôm qua và hôm trước';
		$title_prev1 = 'Cùng kỳ: so sánh với mốc thời gian hiện tại ở trên (07 ngày)';
		$title_prev2 = 'Cùng kỳ kế tiếp: so sánh với mốc thời gian hiện tại ở trên (14 ngày)';
		switch ($sel) {
			case 'today':
			case 'yesterday': {
					$ranges = [
						// ===== HÔM NAY =====
						'today_current' => $mkRange($from_date_sql, $to_date_sql),
						'today_prev1'   => $mkRange(
							$shiftDays($from_date_sql, '-7 days'),
							$shiftDays($to_date_sql,   '-7 days')
						),
						'today_prev2'   => $mkRange(
							$shiftDays($from_date_sql, '-14 days'),
							$shiftDays($to_date_sql,   '-14 days')
						),

						// ===== HÔM QUA =====
						'yesterday_current' => $mkRange($from_date_sql_yesterday, $to_date_sql_yesterday),
						'yesterday_prev1'   => $mkRange(
							$shiftDays($from_date_sql_yesterday, '-7 days'),
							$shiftDays($to_date_sql_yesterday,   '-7 days')
						),
						'yesterday_prev2'   => $mkRange(
							$shiftDays($from_date_sql_yesterday, '-14 days'),
							$shiftDays($to_date_sql_yesterday,   '-14 days')
						),

						// ===== HÔM KIA =====
						'daybefore_current' => $mkRange($from_date_sql_daybefore, $to_date_sql_daybefore),
						'daybefore_prev1'   => $mkRange(
							$shiftDays($from_date_sql_daybefore, '-7 days'),
							$shiftDays($to_date_sql_daybefore,   '-7 days')
						),
						'daybefore_prev2'   => $mkRange(
							$shiftDays($from_date_sql_daybefore, '-14 days'),
							$shiftDays($to_date_sql_daybefore,   '-14 days')
						),
					];
					break;
				}
			case 'this_week':
			case 'previous_week': {
					$weekAnchor = $from_date_sql;

					$ranges = [
						// ===== CURRENT =====
						'today_current'     => $mkWeekRange($weekAnchor, 0),
						'yesterday_current' => $mkWeekRange($weekAnchor, 0),
						'daybefore_current' => $mkWeekRange($weekAnchor, 0),

						// ===== PREV 1 =====
						'today_prev1'     => $mkWeekRange($weekAnchor, -1),
						'yesterday_prev1' => $mkWeekRange($weekAnchor, -1),
						'daybefore_prev1' => $mkWeekRange($weekAnchor, -1),

						// ===== PREV 2 =====
						'today_prev2'     => $mkWeekRange($weekAnchor, -2),
						'yesterday_prev2' => $mkWeekRange($weekAnchor, -2),
						'daybefore_prev2' => $mkWeekRange($weekAnchor, -2),
					];
					$title_current = 'Hiện tại: Tuần đang chọn';
					$title_prev1 = 'Cùng kỳ: so sánh với tuần đang chọn hiện tại ở trên';
					$title_prev2 = 'Cùng kỳ kế tiếp: so sánh với tuần cùng kỳ ở trên';
					break;
				}
			case 'this_month':
			case 'previous_month': {
					$monthAnchor = $from_date_sql;

					$ranges = [
						// ===== CURRENT =====
						'today_current'     => $mkMonthRange($monthAnchor, 0),
						'yesterday_current' => $mkMonthRange($monthAnchor, 0),
						'daybefore_current' => $mkMonthRange($monthAnchor, 0),

						// ===== PREV 1 =====
						'today_prev1'     => $mkMonthRange($monthAnchor, -1),
						'yesterday_prev1' => $mkMonthRange($monthAnchor, -1),
						'daybefore_prev1' => $mkMonthRange($monthAnchor, -1),

						// ===== PREV 2 =====
						'today_prev2'     => $mkMonthRange($monthAnchor, -2),
						'yesterday_prev2' => $mkMonthRange($monthAnchor, -2),
						'daybefore_prev2' => $mkMonthRange($monthAnchor, -2),
					];
					$title_current = 'Hiện tại: Tháng đang chọn';
					$title_prev1 = 'Cùng kỳ: so sánh với tháng đang chọn hiện tại ở trên';
					$title_prev2 = 'Cùng kỳ kế tiếp: so sánh với tháng cùng kỳ ở trên';
					break;
				}
			case 'quarter_this':
			case 'quarter_previous': {
					$quarterAnchor = $from_date_sql;
					$offset = ($sel === 'quarter_previous') ? -1 : 0;

					$ranges = [
						// ===== CURRENT =====
						'today_current'     => $mkQuarterRange($quarterAnchor, $offset),
						'yesterday_current' => $mkQuarterRange($quarterAnchor, $offset),
						'daybefore_current' => $mkQuarterRange($quarterAnchor, $offset),

						// ===== PREV 1 =====
						'today_prev1'     => $mkQuarterRange($quarterAnchor, $offset - 1),
						'yesterday_prev1' => $mkQuarterRange($quarterAnchor, $offset - 1),
						'daybefore_prev1' => $mkQuarterRange($quarterAnchor, $offset - 1),

						// ===== PREV 2 =====
						'today_prev2'     => $mkQuarterRange($quarterAnchor, $offset - 2),
						'yesterday_prev2' => $mkQuarterRange($quarterAnchor, $offset - 2),
						'daybefore_prev2' => $mkQuarterRange($quarterAnchor, $offset - 2),
					];
					$title_current = 'Hiện tại: Quý đang chọn';
					$title_prev1 = 'Cùng kỳ: so sánh với quý đang chọn hiện tại ở trên';
					$title_prev2 = 'Cùng kỳ kế tiếp: so sánh với quý cùng kỳ ở trên';
					break;
				}
			case 'this_year':
			case 'previous_year': {
					$yearAnchor = $from_date_sql;
					$offset = ($sel === 'previous_year') ? -1 : 0;

					$ranges = [
						// ===== CURRENT =====
						'today_current'     => $mkYearRange($yearAnchor, $offset),
						'yesterday_current' => $mkYearRange($yearAnchor, $offset),
						'daybefore_current' => $mkYearRange($yearAnchor, $offset),

						// ===== PREV 1 =====
						'today_prev1'     => $mkYearRange($yearAnchor, $offset - 1),
						'yesterday_prev1' => $mkYearRange($yearAnchor, $offset - 1),
						'daybefore_prev1' => $mkYearRange($yearAnchor, $offset - 1),

						// ===== PREV 2 =====
						'today_prev2'     => $mkYearRange($yearAnchor, $offset - 2),
						'yesterday_prev2' => $mkYearRange($yearAnchor, $offset - 2),
						'daybefore_prev2' => $mkYearRange($yearAnchor, $offset - 2),
					];
					$title_current = 'Hiện tại: Năm đang chọn';
					$title_prev1 = 'Cùng kỳ: so sánh với năm đang chọn hiện tại ở trên';
					$title_prev2 = 'Cùng kỳ kế tiếp: so sánh với năm cùng kỳ ở trên';
					break;
				}
			default: {
					$anchorFrom = $from_date_sql;
					$anchorTo   = $to_date_sql;

					$current = $mkRange($anchorFrom, $anchorTo);
					$prev1   = $shiftRange($anchorFrom, $anchorTo, 1);
					$prev2   = $shiftRange($anchorFrom, $anchorTo, 2);

					$ranges = [
						// ===== CURRENT =====
						'today_current'     => $current,
						'yesterday_current' => $current,
						'daybefore_current' => $current,

						// ===== PREV 1 =====
						'today_prev1'     => $prev1,
						'yesterday_prev1' => $prev1,
						'daybefore_prev1' => $prev1,

						// ===== PREV 2 =====
						'today_prev2'     => $prev2,
						'yesterday_prev2' => $prev2,
						'daybefore_prev2' => $prev2,
					];
					break;
				}
		}

		// pr($ranges);

		$select_period = "CASE
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}' THEN 'today_current'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'   THEN 'today_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'   THEN 'today_prev2'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}' THEN 'yesterday_current'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'   THEN 'yesterday_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'   THEN 'yesterday_prev2'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}' THEN 'daybefore_current'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'   THEN 'daybefore_prev1'
                            WHEN DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'   THEN 'daybefore_prev2'
                            ELSE 'unknown'
                        END AS period";
		$where_period = "AND (
                            DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_current']['from']}' AND '{$ranges['today_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev1']['from']}'   AND '{$ranges['today_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['today_prev2']['from']}'   AND '{$ranges['today_prev2']['to']}'

                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_current']['from']}' AND '{$ranges['yesterday_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev1']['from']}'   AND '{$ranges['yesterday_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['yesterday_prev2']['from']}'   AND '{$ranges['yesterday_prev2']['to']}'

                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_current']['from']}' AND '{$ranges['daybefore_current']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev1']['from']}'   AND '{$ranges['daybefore_prev1']['to']}'
                            OR DATE(DATE_ADD(bk.date_entered_bk, INTERVAL 7 HOUR)) BETWEEN '{$ranges['daybefore_prev2']['from']}'   AND '{$ranges['daybefore_prev2']['to']}'
                        )";

		try {
			// get thông tin doanh số theo ngày tạo
			$sql = "SELECT
                    t.period,
                    CASE
                        WHEN t.period IN ('today_current', 'yesterday_current', 'daybefore_current')
                            THEN 'current'
                        WHEN t.period IN ('today_prev1', 'yesterday_prev1', 'daybefore_prev1')
                            THEN 'prev1'
                        WHEN t.period IN ('today_prev2', 'yesterday_prev2', 'daybefore_prev2')
                            THEN 'prev2'
                    END AS period_group,
                    CASE
                        WHEN t.period = 'today_current' THEN '{$ranges['today_current']['from']}'
                        WHEN t.period = 'today_prev1'   THEN '{$ranges['today_prev1']['from']}'
                        WHEN t.period = 'today_prev2'   THEN '{$ranges['today_prev2']['from']}'
                        WHEN t.period = 'yesterday_current' THEN '{$ranges['yesterday_current']['from']}'
                        WHEN t.period = 'yesterday_prev1'   THEN '{$ranges['yesterday_prev1']['from']}'
                        WHEN t.period = 'yesterday_prev2'   THEN '{$ranges['yesterday_prev2']['from']}'
                        WHEN t.period = 'daybefore_current' THEN '{$ranges['daybefore_current']['from']}'
                        WHEN t.period = 'daybefore_prev1'   THEN '{$ranges['daybefore_prev1']['from']}'
                        WHEN t.period = 'daybefore_prev2'   THEN '{$ranges['daybefore_prev2']['from']}'
                    END AS from_date,
                    CASE
                        WHEN t.period = 'today_current' THEN '{$ranges['today_current']['to']}'
                        WHEN t.period = 'today_prev1'   THEN '{$ranges['today_prev1']['to']}'
                        WHEN t.period = 'today_prev2'   THEN '{$ranges['today_prev2']['to']}'
                        WHEN t.period = 'yesterday_current' THEN '{$ranges['yesterday_current']['to']}'
                        WHEN t.period = 'yesterday_prev1'   THEN '{$ranges['yesterday_prev1']['to']}'
                        WHEN t.period = 'yesterday_prev2'   THEN '{$ranges['yesterday_prev2']['to']}'
                        WHEN t.period = 'daybefore_current' THEN '{$ranges['daybefore_current']['to']}'
                        WHEN t.period = 'daybefore_prev1'   THEN '{$ranges['daybefore_prev1']['to']}'
                        WHEN t.period = 'daybefore_prev2'   THEN '{$ranges['daybefore_prev2']['to']}'
                    END AS to_date,
					t.last_name,
					t.user_name,
					t.user_id,
                    SUM(t.total_qty)             AS total_qty,
                    SUM(t.total_qty_com)         AS total_qty_com,
					SUM(t.booker_bk) 			 AS booker_bk,
					SUM(t.booker_bk_com) 	  	 AS booker_bk_com,
					SUM(t.khach_hang_bk) 		 AS khach_hang_bk,
					SUM(t.com_khach_hang_bk) 	 AS com_khach_hang_bk,
                    SUM(t.tham_khao_bk) 		 AS tham_khao_bk,
                    SUM(t.com_tham_khao_bk) 	 AS com_tham_khao_bk,
                    SUM(t.total_ticket_qty)      AS total_ticket_qty,

                    SUM(t.total_profit)      	 AS total_profit,
                    SUM(t.total_profit_inter)    AS total_profit_inter,

                    SUM(t.prior_bk)      		 AS prior_bk,
                    SUM(t.com_prior_bk)      	 AS com_prior_bk,
                    SUM(t.com_prior_ticket)   	 AS com_prior_ticket,
                    SUM(t.prior_bk_sales)      	 AS prior_bk_sales,

                    SUM(t.1_3_bk)      		 	 AS 1_3_bk,
                    SUM(t.com_1_3_bk)      	 	 AS com_1_3_bk,
                    SUM(t.com_1_3_ticket)   	 AS com_1_3_ticket,
                    SUM(t.1_3_bk_sales)      	 AS 1_3_bk_sales,

                    SUM(t.4_8_bk)      		 	 AS 4_8_bk,
                    SUM(t.com_4_8_bk)      	 	 AS com_4_8_bk,
                    SUM(t.com_4_8_ticket)   	 AS com_4_8_ticket,
                    SUM(t.4_8_bk_sales)      	 AS 4_8_bk_sales,

                    SUM(t.inter_bk)      		 AS inter_bk,
                    SUM(t.com_inter_bk)      	 AS com_inter_bk,
                    SUM(t.com_inter_ticket)   	 AS com_inter_ticket,
                    SUM(t.inter_bk_sales)      	 AS inter_bk_sales,

                    SUM(t.inbound) AS inbound,
                    SUM(t.missed) AS missed,
                    SUM(t.inbound_bk) AS inbound_bk
                FROM (
                    -- Block 1: Doanh số booking
                    SELECT
                        $select_period,
						u.last_name,
						u.user_name,
						u.id AS user_id,
                        0 AS total_qty,
                        COUNT(bk.id) AS total_qty_com,
                        0 AS booker_bk,
                        0 AS booker_bk_com,
                        0 AS khach_hang_bk,
                        0 AS com_khach_hang_bk,
                        0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
                        SUM(bk.ticket_qty) AS total_ticket_qty,
                        SUM(bk.total_profit) AS total_profit,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_profit ELSE 0 END) AS total_profit_inter,

						-- Booking cận
						0 AS prior_bk,
						SUM(CASE WHEN bk.is_prior = 1 THEN 1 ELSE 0 END) AS com_prior_bk,
						SUM(CASE WHEN bk.is_prior = 1 THEN bk.ticket_qty ELSE 0 END) AS com_prior_ticket,
                        SUM(CASE WHEN bk.is_prior = 1 THEN bk.total_profit ELSE 0 END) AS prior_bk_sales,

                        -- Booking theo số vé
						0 AS 1_3_bk,
						SUM(CASE WHEN bk.ticket_qty BETWEEN 1 AND 3 AND bk.is_prior = 0 THEN 1 ELSE 0 END) AS com_1_3_bk,
						SUM(CASE WHEN bk.ticket_qty BETWEEN 1 AND 3 AND bk.is_prior = 0 THEN bk.ticket_qty ELSE 0 END) AS com_1_3_ticket,
                        SUM(CASE WHEN bk.ticket_qty BETWEEN 1 AND 3 AND bk.is_prior = 0 THEN bk.total_profit ELSE 0 END) AS 1_3_bk_sales,

						0 AS 4_8_bk,
						SUM(CASE WHEN bk.ticket_qty BETWEEN 4 AND 8 AND bk.is_prior = 0 THEN 1 ELSE 0 END) AS com_4_8_bk,
						SUM(CASE WHEN bk.ticket_qty BETWEEN 4 AND 8 AND bk.is_prior = 0 THEN bk.ticket_qty ELSE 0 END) AS com_4_8_ticket,
                        SUM(CASE WHEN bk.ticket_qty BETWEEN 4 AND 8 AND bk.is_prior = 0 THEN bk.total_profit ELSE 0 END) AS 4_8_bk_sales,

						-- Booking quốc tế
                        0 AS inter_bk,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN 1 ELSE 0 END) AS com_inter_bk,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.ticket_qty ELSE 0 END) AS com_inter_ticket,
                        SUM(CASE WHEN bk.ticket_type = 2 THEN bk.total_profit ELSE 0 END) AS inter_bk_sales,

                        0 AS inbound,
                        0 AS missed,
                        0 AS inbound_bk
                    FROM ec_revenue bk
					LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
                    WHERE bk.deleted = 0
                    $where_period
                    GROUP BY period, bk.booking_id

                    -- Block 2: Cuộc gọi
                    UNION ALL
					SELECT
						" . str_replace('bk.date_entered_bk', 'c.date_entered', $select_period) . ",
						u.last_name,
						u.user_name,
						u.id AS user_id,
						0 AS total_qty,
						0 AS total_qty_com,
						0 AS booker_bk,
                        0 AS booker_bk_com,
						0 AS khach_hang_bk,
                        0 AS com_khach_hang_bk,
                        0 AS tham_khao_bk,
						0 AS com_tham_khao_bk,
						0 AS total_ticket_qty,
						0 AS total_profit,
						0 AS total_profit_inter,

						-- Booking cận
						0 AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
                        0 AS prior_bk_sales,

                        -- Booking theo số vé
						0 AS 1_3_bk,
						0 AS com_1_3_bk,
						0 AS com_1_3_ticket,
                        0 AS 1_3_bk_sales,

						0 AS 4_8_bk,
						0 AS com_4_8_bk,
						0 AS com_4_8_ticket,
                        0 AS 4_8_bk_sales,

						-- Booking quốc tế
                        0 AS inter_bk,
                        0 AS com_inter_bk,
                        0 AS com_inter_ticket,
                        0 AS inter_bk_sales,

						SUM(CASE WHEN c.direction = 'inbound' THEN 1 ELSE 0 END) AS inbound,
						SUM(CASE WHEN c.direction = 'missed'  THEN 1 ELSE 0 END) AS missed,
						SUM(CASE WHEN c.direction = 'inbound' AND c.booking_id IS NOT NULL AND c.booking_id <> '' THEN 1 ELSE 0 END) AS inbound_bk
					FROM calls c
					LEFT JOIN users u ON c.call_sources = u.last_name AND u.deleted = 0
					WHERE c.deleted = 0
					AND u.title = 'Bot'
					" . str_replace('bk.date_entered_bk', 'c.date_entered', $where_period) . "
					GROUP BY period, u.id

                    -- Block 3: Tham số khác
                    UNION ALL
					SELECT
						period,
						last_name,
						user_name,
						user_id,

						COUNT(bk_id) AS total_qty,
						0 AS total_qty_com,

						SUM(is_booker) AS booker_bk,
						SUM(is_booker_com) AS booker_bk_com,

						SUM(is_customer) AS khach_hang_bk,
						SUM(is_customer_com) AS com_khach_hang_bk,

						SUM(is_reference) AS tham_khao_bk,
						SUM(is_reference_com) AS com_tham_khao_bk,

						0 AS total_ticket_qty,
						0 AS total_profit,
						0 AS total_profit_inter,

						SUM(is_prior) AS prior_bk,
						0 AS com_prior_bk,
						0 AS com_prior_ticket,
						0 AS prior_bk_sales,

						-- booking theo số vé
						SUM(CASE WHEN total_ticket BETWEEN 1 AND 3 AND is_prior = 0 THEN 1 ELSE 0 END) AS 1_3_bk,
						0 AS com_1_3_bk,
						0 AS com_1_3_ticket,
						0 AS 1_3_bk_sales,

						SUM(CASE WHEN total_ticket BETWEEN 4 AND 8 AND is_prior = 0 THEN 1 ELSE 0 END) AS 4_8_bk,
						0 AS com_4_8_bk,
						0 AS com_4_8_ticket,
						0 AS 4_8_bk_sales,

						SUM(CASE WHEN ticket_type = 2 THEN 1 ELSE 0 END) AS inter_bk,
						0 AS com_inter_bk,
						0 AS com_inter_ticket,
						0 AS inter_bk_sales,

						0 AS inbound,
						0 AS missed,
						0 AS inbound_bk

					FROM (
						SELECT
							" . str_replace('bk.date_entered_bk', 'bk.date_entered', $select_period) . ",
							u.last_name,
							u.user_name,
							u.id AS user_id,
							bk.id AS bk_id,
							bk.ticket_type,
							bk.is_prior,
							IFNULL(SUM(d.quantity), 0) AS total_ticket,

							-- booker
							CASE
								WHEN bk.contact_name IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi')
								OR aud_booker.parent_id IS NOT NULL
								THEN 1 ELSE 0
							END AS is_booker,

							CASE
								WHEN bk.booking_status = 8
								AND (
									bk.contact_name IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi')
									OR aud_booker.parent_id IS NOT NULL
								)
								THEN 1 ELSE 0
							END AS is_booker_com,

							-- khách hàng
							CASE
								WHEN bk.contact_name IS NOT NULL
								AND bk.contact_name <> ''
								AND bk.contact_name NOT IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi','Tham Khao')
								AND aud_all.parent_id IS NULL
								THEN 1 ELSE 0
							END AS is_customer,

							CASE
								WHEN bk.booking_status = 8
								AND bk.contact_name IS NOT NULL
								AND bk.contact_name <> ''
								AND bk.contact_name NOT IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi','Tham Khao')
								AND aud_all.parent_id IS NULL
								THEN 1 ELSE 0
							END AS is_customer_com,

							-- tham khảo
							CASE WHEN bk.is_reference = 1 THEN 1 ELSE 0 END AS is_reference,
							CASE WHEN bk.is_reference = 1 AND bk.booking_status = 8 THEN 1 ELSE 0 END AS is_reference_com

						FROM ec_flight_bookings bk
						LEFT JOIN users u ON bk.created_by = u.id AND u.deleted = 0
						LEFT JOIN ec_booking_details d ON d.booking_id = bk.id AND d.deleted = 0
						LEFT JOIN ec_flight_bookings_audit aud_booker ON aud_booker.parent_id = bk.id AND aud_booker.field_name = 'contact_name' AND aud_booker.before_value_string IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi')
						LEFT JOIN ec_flight_bookings_audit aud_all ON aud_all.parent_id = bk.id AND aud_all.field_name = 'contact_name' AND aud_all.before_value_string IN ('Panda Po','Bao Gia Khach','Khach Hang Hoi','Tham Khao')
						WHERE bk.deleted = 0
						" . str_replace('bk.date_entered_bk', 'bk.date_entered', $where_period) . "
						GROUP BY bk.id
					) x
					GROUP BY period, user_id
                ) t
                GROUP BY period, user_id
                ORDER BY
                    CASE t.period
                        WHEN 'today_current' THEN 1
                        WHEN 'yesterday_current' THEN 2
                        WHEN 'daybefore_current' THEN 3
                        WHEN 'today_prev1'   THEN 4
                        WHEN 'yesterday_prev1'   THEN 5
                        WHEN 'daybefore_prev1'   THEN 6
                        WHEN 'today_prev2'   THEN 7
                        WHEN 'yesterday_prev2'   THEN 8
                        WHEN 'daybefore_prev2'   THEN 9
                        ELSE 10
                    END,
					total_profit DESC
            ";

			// pr($sql);

			$res = $this->bean->db->query($sql);

			$html = '<tbody><form id="booking_search" name="search_form" method="POST" action="index.php?module=EC_TongHop&action=ListView" target="_blank">';
			$i = 0;
			$mark_current = false;
			$mark_previous1 = false;
			$mark_previous2 = false;

			// cộng dồn theo nhóm (period)
			$sub = [
				'total_profit' => 0,
				'total_ticket_qty' => 0,
				'total_qty' => 0,
				'total_qty_com' => 0,
				'booker_bk' => 0,
				'booker_bk_com' => 0,
				'khach_hang_bk' => 0,
				'com_khach_hang_bk' => 0,
				'tham_khao_bk' => 0,
				'com_tham_khao_bk' => 0,
				'inbound' => 0,
				'missed' => 0,
				'inbound_bk' => 0,
				'prior_bk' => 0,
				'com_prior_bk' => 0,
				'com_prior_ticket' => 0,
				'prior_bk_sales' => 0,
				'1_3_bk' => 0,
				'com_1_3_bk' => 0,
				'com_1_3_ticket' => 0,
				'1_3_bk_sales' => 0,
				'4_8_bk' => 0,
				'com_4_8_bk' => 0,
				'com_4_8ticket' => 0,
				'4_8_bk_sales' => 0,

				'inter_bk' => 0,
				'com_inter_bk' => 0,
				'com_inter_ticket' => 0,
				'inter_bk_sales' => 0,
			];
			$periodNow = null;

			// in subtotal của 1 period
			$printSubtotal = function (string $period, array $s, array $range) use (&$html) {
				$percent = $s['total_qty'] > 0 ? round(($s['total_qty_com'] / $s['total_qty']) * 100, 1) : 0;
				$from_date = $range[$period]['from'];
				$to_date   = $range[$period]['to'];

				// ngày
				$text_date = 'Từ ngày ' . date('d-m-Y', strtotime($from_date)) . ' Đến ngày ' . date('d-m-Y', strtotime($to_date));
				if (strtotime($from_date) === strtotime($to_date)) {
					$text_date = 'Ngày: ' . date('d-m-Y', strtotime($from_date));
				}

				$html .= '
					<tr class="bg-light fw-semibold subtotal ' . $period . '">
						<td class="text-start">Tổng</td>

						<td colspan="2">
							<div class="s_total_profit text-end" title="Tổng doanh số ' . $text_date . '">' . format_number($s['total_profit']) . '</div>
						</td>

						<td class="text-center s_total_ticket_qty" title="Tổng số vé ' . $text_date . '">' . format_number($s['total_ticket_qty']) . '</td>
						<td class="text-center color-blue s_total_qty_com" title="Tổng booking hoàn tất ' . $text_date . '">' . format_number($s['total_qty_com']) . '</td>

						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
								<div class="total_percent text-start" title="Tỉ lệ giữa BK OK trên tổng BK ' . $text_date . '">(' . format_number($percent) . '%)</div>
								<div class="hide-mobile total text-end color-red fw-semibold show_detail_total show_detail" from_date="' . $from_date . '" to_date="' . $to_date . '" title="Tổng số lượng booking ' . $text_date . '">' . format_number($s['total_qty']) . '</div>
							</div>
						</td>

						<td class="text-end" title="Tổng số lượng BK do booker đặt / Đã hoàn tất ' . $text_date . '">' . $s['booker_bk'] . '&nbsp;/&nbsp;' . $s['booker_bk_com'] . '</td>
						<td class="text-end" title="Tổng số lượng BK do khách hàng đặt / Đã hoàn tất ' . $text_date . '">' . $s['khach_hang_bk'] . '&nbsp;/&nbsp;' . $s['com_khach_hang_bk'] . '</td>
						<td class="text-end" title="Tổng số lượng BK tham khảo / Đã hoàn tất ' . $text_date . '">' . $s['tham_khao_bk'] . '&nbsp;/&nbsp;' . $s['com_tham_khao_bk'] . '</td>

						<td class="text-end" title="Tổng số lượng cuộc gọi đến / Đã tạo BK từ cuộc gọi đến ' . $text_date . '">' . $s['inbound'] . ' / ' . $s['inbound_bk'] . '</td>
						<td class="text-end" title="Tổng số lượng cuộc gọi nhỡ ' . $text_date . '">' . $s['missed'] . '</td>

						<td class="text-end" title="Tổng số lượng BK cận / Đã hoàn tất ' . $text_date . '">' . $s['prior_bk'] . '&nbsp;/&nbsp;' . $s['com_prior_bk'] . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_prior_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['prior_bk_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['1_3_bk']) . '&nbsp;/&nbsp;' . format_number($s['com_1_3_bk']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_1_3_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['1_3_bk_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['4_8_bk']) . '&nbsp;/&nbsp;' . format_number($s['com_4_8_bk']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_4_8ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['4_8_bk_sales']) . '</div>
							</div>
						</td>

						<td class="text-end">' . format_number($s['inter_bk']) . '&nbsp;/&nbsp;' . format_number($s['com_inter_bk']) . '</td>
						<td colspan="2">
							<div class="d-flex align-items-center justify-content-between gap-1">
							<div class="text-start">(' . format_number($s['com_inter_ticket']) . ' vé)</div>
							<div class="text-end">' . format_number($s['inter_bk_sales']) . '</div>
							</div>
						</td>
					</tr>';
			};

			// reset mảng cộng dồn nhóm
			$resetSub = function (array &$s) {
				foreach ($s as $k => $v) $s[$k] = 0;
			};
			$flag_date = date('Y-m-d', strtotime('+1 day'));
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				if ($periodNow !== null && $row['period'] !== $periodNow) {
					$printSubtotal($periodNow, $sub, $ranges);
					$resetSub($sub);
				}
				$periodNow = $row['period'];

				$sub['total_profit']          += (float)$row['total_profit'];
				$sub['total_ticket_qty']         += (float)$row['total_ticket_qty'];
				$sub['total_qty']             += (int)$row['total_qty'];
				$sub['total_qty_com']             += (int)$row['total_qty_com'];

				$sub['booker_bk']                += (int)$row['booker_bk'];
				$sub['booker_bk_com']            += (int)$row['booker_bk_com'];
				$sub['khach_hang_bk']        += (int)$row['khach_hang_bk'];
				$sub['com_khach_hang_bk']    += (int)$row['com_khach_hang_bk'];
				$sub['tham_khao_bk']         += (int)$row['tham_khao_bk'];
				$sub['com_tham_khao_bk']     += (int)$row['com_tham_khao_bk'];

				$sub['inbound']            += (int)$row['inbound'];
				$sub['missed']             += (int)$row['missed'];
				$sub['inbound_bk']         += (int)$row['inbound_bk'];

				$sub['prior_bk']             += (int)$row['prior_bk'];
				$sub['com_prior_bk']         += (int)$row['com_prior_bk'];
				$sub['com_prior_ticket']     += (int)$row['com_prior_ticket'];
				$sub['prior_bk_sales']       += (float)$row['prior_bk_sales'];

				$sub['1_3_bk']        += (int)$row['1_3_bk'];
				$sub['com_1_3_bk']    += (int)$row['com_1_3_bk'];
				$sub['com_1_3_ticket']        += (int)$row['com_1_3_ticket'];
				$sub['1_3_bk_sales']  += (float)$row['1_3_bk_sales'];

				$sub['4_8_bk']        += (int)$row['4_8_bk'];
				$sub['com_4_8_bk']    += (int)$row['com_4_8_bk'];
				$sub['com_4_8ticket']        += (int)$row['com_4_8ticket'];
				$sub['4_8_bk_sales']  += (float)$row['4_8_bk_sales'];

				$sub['inter_bk']      += (int)$row['inter_bk'];
				$sub['com_inter_bk'] += (int)$row['com_inter_bk'];
				$sub['com_inter_ticket']     += (int)$row['com_inter_ticket'];
				$sub['inter_bk_sales'] += (float)$row['inter_bk_sales'];

				// Line title
				if (strpos($row['period_group'], 'current') !== false && !$mark_current) {
					$mark_current = true;

					$html .= '<tr style="background-color: #fff2cc;">
							<td colspan="30">
								<span class="form-label text-dark fw-semibold">' . $title_current . '</span> 
							</td>
						</tr>';
				} else if (strpos($row['period_group'], 'prev1') !== false && !$mark_previous1) {
					$mark_previous1 = true;

					$html .= '<tr style="background-color: #fff2cc;">
							<td colspan="30">
								<span class="form-label text-dark fw-semibold">' . $title_prev1 . '</span> 
							</td>
						</tr>';
				} else if (strpos($row['period_group'], 'prev2') !== false && !$mark_previous2) {
					$mark_previous2 = true;

					$html .= '<tr style="background-color: #fff2cc;">
							<td colspan="30">
								<span class="form-label text-dark fw-semibold">' . $title_prev2 . '</span> 
							</td>
						</tr>';
				}

				// Line ngày
				$from_date_row = 'Từ ngày <span class="form-label fw-semibold text-danger">' . date('d-m-Y', strtotime($row['from_date'])) . '</span> Đến ngày <span class="form-label fw-semibold text-danger">' . date('d-m-Y', strtotime($row['to_date'])) . '</span>';
				$text_date = 'Từ ngày ' . date('d-m-Y', strtotime($row['from_date'])) . ' Đến ngày ' . date('d-m-Y', strtotime($row['to_date']));
				if (strtotime($row['from_date']) === strtotime($row['to_date'])) {
					$from_date_row = '<span class="form-label fw-semibold text-danger">Ngày: ' . date('d-m-Y', strtotime($row['from_date'])) . '</span>';
					$text_date = 'Ngày ' . date('d-m-Y', strtotime($row['from_date']));
				}

				if ($flag_date != $row['from_date']) {
					$html .= '<tr style="background-color: #fff2cc;">
								<td colspan="30">
									<span class="form-label text-dark fw-semibold">' . $from_date_row . '</span> 
								</td>
							</tr>';
				}

				// Tên site vé
				$site_name = str_replace(".", " ", $row['last_name']);
				$denominator_total_qty = ($row['total_qty'] == 0 || empty($row['total_qty'])) ? 1 : $row['total_qty'];

				$html .= '
						<tr>
							<td class="text-start fw-semibold" title="Site vé ' . $site_name . '">' . $site_name . '</td>
							<td colspan="2" class="text-end">
								<div class="total text-end total_profit" title="Doanh số của site ' . $site_name . ' ' . $text_date . '">' . format_number($row['total_profit']) . '</div>
							</td>

							<td class="text-center total_ticket_qty" title="Tổng số vé của site ' . $site_name . ' ' . $text_date . '">' . format_number($row['total_ticket_qty']) . '</td>
							<td class="text-center fw-semibold color-blue total_qty_com" title="Tổng booking hoàn tất của site ' . $site_name . ' ' . $text_date . '">' . format_number($row['total_qty_com']) . '</td>
							
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="total_qty_com_percent text-start" title="Tỉ lệ giữa BK OK trên tổng BK của site ' . $site_name . ' ' . $text_date . '">(' . format_number(($row['total_qty_com']) / $denominator_total_qty * 100) . '%)</div>
									<div class="hide-mobile total_qty text-end color-red fw-semibold" title="Tổng số lượng booking của site ' . $site_name . ' ' . $text_date . '">&nbsp;&nbsp;' . format_number($row['total_qty']) . '</div>
								</div>
							</td>
							
							<td class="text-end" title="Số lượng BK Booker đặt / Đã hoàn tất của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail show_booker_bk" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '"  type="show_booker_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . format_number($row['booker_bk'] ?? 0) . ' / ' . format_number($row['booker_bk_com'] ?? 0) . '</span>
							</td>
							
							<td class="text-end" title="Số lượng BK Khách hàng đặt / Đã hoàn tất của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail show_khachhang_bk" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" type="show_khachhang_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . format_number($row['khach_hang_bk'] ?? 0) . '&nbsp;/&nbsp;' . format_number($row['com_khach_hang_bk'] ?? 0) . '</span>
							</td>
							<td class="text-end" title="Số lượng BK tham khảo / Đã hoàn tất của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail show_thamkhao_bk" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" type="show_thamkhao_bk" sname="' . $row['last_name'] . '" user="' . $row['user_id'] . '">' . format_number($row['tham_khao_bk'] ?? 0) . '&nbsp;/&nbsp;' . format_number($row['com_tham_khao_bk'] ?? 0) . '</span>
							</td>

							<td class="text-end inbound" title="Số lượng cuộc gọi đến / Đã tạo BK từ cuộc gọi đến của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail show_detail_call_inbound" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="inbound" user="' . $row['user_id'] . '">' . $row['inbound'] . ' / ' . $row['inbound_bk'] . '</span>
							</td>
							<td class="text-end missed" title="Tổng số lượng cuộc gọi nhỡ của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail show_detail_call_missed" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_detail_call" direction="missed" user="' . $row['user_id'] . '">' . $row['missed'] . '</span>
							</td>
							
							<td class="text-end prior_bk" title="Số lượng BK cận / Đã hoàn tất của site ' . $site_name . ' ' . $text_date . '">
								<span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_prior_bk" user="' . $row['user_id'] . '">' . format_number($row['prior_bk'] ?? 0) . '&nbsp;/&nbsp;' . format_number($row['com_prior_bk'] ?? 0) . '</span>
							</td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_prior_ticket text-start">(' . format_number($row['com_prior_ticket']) . '&nbsp;vé)</div>
									<div class="prior_bk_sales text-end">' . format_number($row['prior_bk_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_3ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['1_3_bk']) . '&nbsp;/&nbsp;' . format_number($row['com_1_3_bk']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_1_3_ticket text-start">(' . format_number($row['com_1_3_ticket']) . '&nbsp;vé)</div>
									<div class="1_3_bk_sales text-end">' . format_number($row['1_3_bk_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_4to8ticket_bk" user="' . $row['user_id'] . '">' . format_number($row['4_8_bk']) . '&nbsp;/&nbsp;' . format_number($row['com_4_8_bk']) . '</span></td>
							<td colspan="2">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_4_8ticket text-start">(' . format_number($row['com_4_8ticket']) . '&nbsp;vé)</div>
									<div class="new_4to8ticket_sales text-end">' . format_number($row['4_8_bk_sales']) . '</div>
								</div>
							</td>

							<td class="text-end"><span class="show_detail_bk show_detail inter" from_date="' . $ranges[$row['period']]['from'] . '" to_date="' . $ranges[$row['period']]['to'] . '" sname="' . $row['last_name'] . '" type="show_inter_bk" user="' . $row['user_id'] . '">' . format_number($row['inter_bk']) . '&nbsp;/&nbsp;' . format_number($row['com_inter_bk']) . '</span></td>
							<td colspan="2" class="inter">
								<div class="d-flex align-items-center justify-content-between gap-1">
									<div class="com_inter_ticket text-start">(' . format_number($row['com_inter_ticket']) . '&nbsp;vé)</div>
									<div class="new_inter_ticket_sales text-end">' . format_number($row['inter_bk_sales']) . '</div>
								</div>
							</td>
						</tr>';

				$flag_date = $row['from_date'];
				$i++;
			}

			if ($periodNow !== null) {
				$printSubtotal($periodNow, $sub, $ranges); // in subtotal của current hoặc previous cuối
			}

			$smarty->assign('rpt_body_compare', $html);
		} catch (Exception $e) {
			error_log("Exception in genBKSale: " . $e->getMessage());
			$smarty->assign('rpt_body_compare', '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>');
		}
	}
}
