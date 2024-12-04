<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewemployeesalary extends SugarView
{

	function display()
	{
		global $current_user;

		$smarty = new Sugar_Smarty();
		$is_special_user = isAllowedUser();

		// kiểm tra user xem lương chi tiết
		if ($is_special_user && isset($_REQUEST['user_id'])) {
			$user_id = $_REQUEST['user_id'];
		} else {
			$user_id = $current_user->id;
		}

		if (isset($_REQUEST['for']) && $_REQUEST['for'] == 'effortdetail') {
			$this->populateEffortDetail($smarty, $user_id, $_POST['detail_type']);
		} else if (isset($_POST['saveDetail'])) {
			$this->saveDetail();
		} else if (isset($_REQUEST['for']) && $_REQUEST['for'] == 'showdetail') {
			if (is_admin($current_user)) {
				$this->populateDetail($smarty, $user_id);
			} else {
				echo "<center>Tính năng đang bảo trì</center>";
				exit();
			}
		} else if (isset($_POST['excelexport']) && $is_special_user) {
			$this->exportExcel($_POST['month_search'], $_POST['year_search']);
		} else if (is_admin($current_user)) {
			if (isset($_POST['for']) && $_POST['for'] == 'Save') {
				if (isset($_POST['commission_from_amt'])) {
					$this->saveCommission();
				}
				$this->saveSalaryInf();
			}
			if (isset($_POST['for']) && $_POST['for'] == 'SaveAmountDetail') {
				$this->saveAmountDetail();
			}
			if (isset($_POST['approved_btn'])) {
				$this->updateApprovedStatus();
			}
			if (isset($_POST['update_btn'])) {
				$this->updateWorkingDays();
			}
			$this->populateButtons($smarty, $is_special_user);
			$this->populateContent($smarty, $is_special_user);
		} else {
			echo "<center>Tính năng đang bảo trì</center>";
			exit();
		}
		$smarty->display('modules/EC_Employee_Salary/tpls/view_employeesalary.tpl');
	}

	function populateButtons($smartyobj, $is_special_user)
	{
		global $current_user;

		// nút chỉnh sửa
		if (empty($_REQUEST['month_search'])) $_REQUEST['month_search'] = date('n', strtotime('-1 month'));
		if (empty($_REQUEST['year_search'])) $_REQUEST['year_search'] = date('Y', strtotime('-1 month'));
		$approved_inf = $this->checkApproved((int)$_REQUEST['month_search'], (int)$_REQUEST['year_search']);
		if ($is_special_user) {
			if (!$approved_inf['is_approved']) {
				if (!isset($_REQUEST['edit_btn'])) {
					$edit_btn = '<input type="submit" class="btn btn-warning" name="edit_btn" value="Chỉnh sửa">';
					$smartyobj->assign('EDIT_BTN', $edit_btn);
				} else {
					$save_btn = '<input type="submit" class="btn btn-primary" id="save_btn" name="save_btn" value="Lưu">';
					$smartyobj->assign('SAVE_BTN', $save_btn);
				}

				if (is_admin($current_user)) {
					$approved_btn = '<input type="submit" class="btn btn-primary" name="approved_btn" value="Duyệt">';
					$smartyobj->assign('APPROVED_BTN', $approved_btn);

					$request_month = isset($_REQUEST['month_search']) ? $_REQUEST['month_search'] : null;
					$previousMonth = date('m', strtotime('-1 month'));
					if ($request_month === $previousMonth) {
						$update_btn = '<input type="submit" class="btn btn-secondary" name="update_btn" value="Cập nhật">';
						$smartyobj->assign('UPDATE_SALARY', $update_btn);
					}
				}
			}

			// nút xuất excel
			$excel_btn = '<input type="submit" class="btn btn-success" name="excelexport" value="Xuất Excel">';
			$smartyobj->assign('EXCEL_BTN', $excel_btn);

			// phần trích lục bảng lương
			// $smartyobj->assign('EXCERPT_SALARY', '<span class="excerpt_salary">Trích lục bảng lương</span>');
			// $this->generateSearchMonthHTML();
		}
	}

	function populateContent($smartyobj, $is_special_user)
	{
		global $current_user, $app_list_strings;

		for ($m = 1; $m <= 12; $m++) {
			$m_arr[$m] = $m;
		}
		if (empty($_REQUEST['month_search'])) $_REQUEST['month_search'] = date('n', strtotime('-1 month'));
		$smartyobj->assign('MONTH_OPTION', get_select_options_with_id($m_arr, (int)$_REQUEST['month_search']));
		$smartyobj->assign('MONTH', $_REQUEST['month_search']);

		for ($y = 2021; $y <= (date('Y') + 3); $y++) {
			$y_arr[$y] = $y;
		}
		if (empty($_REQUEST['year_search'])) $_REQUEST['year_search'] = date('Y', strtotime('-1 month'));
		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($y_arr, (int)$_REQUEST['year_search']));
		$smartyobj->assign('YEAR', $_REQUEST['year_search']);

		$approved_inf = $this->checkApproved($_REQUEST['month_search'], $_REQUEST['year_search']);

		if ($approved_inf['is_approved']) {
			$smartyobj->assign('STATUS', 'Đã duyệt ngày ' . date('d-m-Y H:i:s', strtotime('+7 hours', strtotime($approved_inf['approved_date']))));
		} else {
			$smartyobj->assign('STATUS', 'Chưa duyệt');
		}

		// bảng lương
		if ((!isset($_REQUEST['edit_btn']) || $approved_inf['is_approved']) || (!is_admin($current_user) && $current_user->title != 'QuanLy') && isset($_REQUEST['edit_btn'])) {
			$data = $this->calculateSalary($_REQUEST['month_search'], $_REQUEST['year_search'], $is_special_user);
			if (is_admin($current_user) || $current_user->title == 'QuanLy') {
				$smartyobj->assign('BACKUP_FUND', '<th style="padding: 5px; background-color: #fff; border: 1px solid #ccc; font-size: 13pt;">Dự phòng</th>');
			} else {
				$smartyobj->assign('BACKUP_FUND', '');
			}
			$smartyobj->assign('SALARY', $data);
			$smartyobj->assign('isEffortDetail', 0);
			$smartyobj->assign('isDetail', 0);
		} else { // chỉnh sửa bảng lương
			$smartyobj->assign('isEdit', 1);
			$data = $this->calculateSalaryEdit($_REQUEST['month_search'], $_REQUEST['year_search']);
			$smartyobj->assign('SALARY_EDIT', $data);
			$smartyobj->assign('REASON_MINUS_OPTION', get_select_options_with_id($app_list_strings['salary_minus_list'], ''));
			$smartyobj->assign('REASON_BONUS_OPTION', get_select_options_with_id(array('Khac' => 'Khác', 'Thuong_DS' => 'Thưởng DS'), ''));

			// bảng tiền phụ cấp
			$data2 = $this->calculateCommission($_REQUEST['month_search'], $_REQUEST['year_search']);
			$smartyobj->assign('COMMISSION', $data2['html']);
			$smartyobj->assign('ROW_COUNT', $data2['count']);

			// bảng tiền phụ cấp
			$data3 = $this->calculateAllowance($_REQUEST['month_search'], $_REQUEST['year_search']);
			$smartyobj->assign('EMPLOYEE_ALLOWANCE', $data3);

			// bảng tiền bảo hiểm
			$data4 = $this->calculateInsurrance($_REQUEST['month_search'], $_REQUEST['year_search']);
			$smartyobj->assign('EMPLOYEE_INSURANCE', $data4);
			$smartyobj->assign('ROW_COUNT_MINUS', 1);
			$smartyobj->assign('CREATED_USER', $current_user->user_name);
		}
	}

	function calculateSalary($month_search, $year_search, $is_special_user)
	{
		global $current_user;

		if (!$is_special_user) {
			$assigned_user_id = ' AND assigned_user_id = "' . $current_user->id . '"';
		} else $assigned_user_id = '';
		$m_search = $month_search . '-' . $year_search;
		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
		$date_search = '01-' . $month;
		$date_end_search = date('Y-m-t', strtotime($date_search));
		$today = date('Y-m-d');
		$end_date = date('Y-n-t', strtotime($date_search));

		// kiểm tra đã có bảng lương hiện tại
		$is_exist = $this->checkExistSalary($month_search, $year_search);
		if ($month == date('m-Y') && !$is_exist) {
			$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '" AND month = ' . date('n', strtotime('-1 month', strtotime('01-' . $month))) . ' AND year = ' . date('Y', strtotime('-1 month', strtotime('01-' . $month)));
			$col_q = ' , 0 AS minus
					   , 0 AS effort
					   , 0 AS sales
					   , 0 AS minus_income
					   , 0 AS ot_days
					   , 0 AS backup_fund
					   , 0 AS is_approved';
		} else {
			$from_date_limit = 'AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '" AND month = ' . $month_search . ' AND year = ' . $year_search;
			$col_q = ' , minus
					   , effort
					   , sales
					   , minus_income
					   , ot_days
					   , backup_fund
					   , is_approved';
		}

		$sql = '
			SELECT 
				CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
				, SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income 
				, SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
				, s.working_days, s.ot_days
				, s.no_paid_days AS no_paid_days
				, IFNULL(l.absence_days, 0) AS leave_days
				, s.minus, s.social_insurance, s.is_approved
				, s.is_online, s.effort, s.extra_amount, s.minus_income
				, ROUND(s.sales) AS sales, ROUND(s.backup_fund) AS backup_fund
				, s.actual_salary, u.his_stt, u.is_paid
				, u.id AS user_id, u.employee_type, u.his_date_start, u.his_date_end
  				FROM (
 					SELECT 
						usr.id, usr.start_working_date, usr.status
						, usr.last_name, usr.first_name
						, usr.employee_type, usr.deleted
						, usr.title, his.with_salary AS with_salary
						, his.description AS history_desc
						, his.status AS his_stt
						, IFNULL(
							(
								SELECT date_start
								FROM ec_workhistory h
								WHERE deleted = 0
								AND status = "Active"
								AND assigned_user_id = usr.id
								AND date_start >= IFNULL(
									( 
										SELECT date_start 
										FROM ec_workhistory 
										WHERE deleted = 0 
										AND status = "InActive" 
										AND assigned_user_id = h.assigned_user_id
										ORDER BY date_start DESC LIMIT 1 
									),  "1970-01-01"
								) 
								ORDER BY date_start LIMIT 1
							)
							, IFNULL(
								(
									SELECT date_start
									FROM ec_workhistory h
									WHERE deleted = 0
									AND status = "Active"
									AND assigned_user_id = usr.id
									AND date_start <= IFNULL(
										( 
											SELECT date_start 
											FROM ec_workhistory 
											WHERE deleted = 0 
											AND status = "InActive" 
											AND assigned_user_id = h.assigned_user_id
											ORDER BY date_start DESC LIMIT 1 
										),  "1970-01-01"
									) 
									ORDER BY date_start LIMIT 1
								)
								, DATE_ADD("' . date('Y-m-01', strtotime($date_search)) . '", INTERVAL 1 DAY)
							)
						) AS his_date_start
						 , (
							SELECT IFNULL(
								date_end
								, IF(
									status = "InActive" OR status = "Absent"
									, DATE_SUB(date_start, INTERVAL 1 DAY)
									, "' . $date_end_search . '"
								)
							)
							FROM ec_workhistory
							WHERE deleted = 0
							AND assigned_user_id = usr.id
							ORDER BY date_start DESC LIMIT 1
						 ) AS his_date_end
						 , IFNULL((
							SELECT IF(his_date_end >= "' . date('Y-m-01', strtotime($date_search)) . '", with_salary, 0)
							FROM ec_workhistory
							WHERE deleted = 0 AND assigned_user_id = usr.id AND with_salary = 1
							ORDER BY date_start DESC
							LIMIT 1
						), 0) AS is_paid
 					FROM users usr
 					INNER JOIN ec_workhistory his
 					ON his.assigned_user_id = usr.id
 					AND his.deleted = 0
 					AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($date_search)) . '"
 					AND LAST_DAY(IFNULL(his.date_end, "' . $date_end_search . '")) >= "' . $date_end_search . '"
 					WHERE usr.deleted = 0
					AND his.date_start IN ( 
						SELECT MAX(sub_his.date_start) FROM ec_workhistory sub_his 
						WHERE DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($date_search)) . '" 
						AND LAST_DAY(IFNULL(his.date_end, "' . $date_end_search . '")) >= "' . $date_end_search . '" 
						AND sub_his.deleted = 0 
						GROUP BY sub_his.assigned_user_id )
					-- GROUP BY usr.id
 				) AS u 
 				LEFT JOIN (
 					SELECT (
					   CASE WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
					   AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" 
					   THEN SUM(IFNULL( absence_days, 0 ))
					   WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
					   THEN DATEDIFF("' . $date_end_search . '", from_date) + 1
					   ELSE DATEDIFF(to_date, "' . date('Y-m-d', strtotime($date_search)) . '") + 1 END 
					) AS absence_days
					, assigned_user_id
 					FROM ec_leaveabsences 
 					WHERE deleted = 0
 					AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
 					OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '") 
 					AND (to_date <= "' . $date_end_search . '"
 					OR from_date <= "' . $date_end_search . '")
 					' . $assigned_user_id . '
 					GROUP BY assigned_user_id
 				) AS l ON l.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT basic_salary, efficient_wage
 					     , gas_allowance, lunch_allowance
 						 , tele_allowance
 						 , responsible_allowance
 						 , seniority_allowance
 						 , other_allowance1
 						 , other_allowance2
 						 , social_insurance, no_paid_days
 						 , assigned_user_id
 						 , working_days, is_online
 						 , actual_salary, extra_amount
 						 ' . $col_q . ' 
 					FROM ec_employee_salary 
 					WHERE deleted = 0 
 					AND year = ' . $year_search . ' AND month <= ' . $month_search . '
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND year = ' . $year_search . ' AND month <= ' . $month_search . '
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					' . $from_date_limit . '
 					' . $assigned_user_id . '
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.his_date_start IS NOT NULL
 				' . str_replace('assigned_user_id', 'u.id', $assigned_user_id) . '
 				GROUP BY u.id
 				ORDER BY (
 					CASE 
 						WHEN u.title LIKE "%QuanLy%" THEN 1
 						WHEN u.title LIKE "%KeToan%" THEN 2
 						WHEN u.title LIKE "%Leader%" THEN 3
 						WHEN u.title LIKE "%Booker%" THEN 4
 					ELSE 5
 					END 
 				), u.his_date_start, u.first_name, u.last_name';

		// if($current_user->user_name == 'admin') {
		// 	pr($sql);
		// }
		// AND IF(' . strtotime(date('Y-m-d')) . ' > ' . strtotime($date_end_search) . ', s.working_days > 4, 1=1)

		$res = $this->bean->db->query($sql);
		$i = 0;
		$total_income = $total_allowance = $total_working_days = $total_term_income = 0;
		$total_amount = $total_effort = $total_sale = $total_insurance = $total_amount = 0;
		$total_minus = $total_backup_fund = $total_tl_amount = $total_minus_income = 0;
		$is_approved = 0;
		$html = '';

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ((
				($row['his_stt'] == 'Active' || $row['his_stt'] == 'Online')
				&& strtotime($row['his_date_start']) <= strtotime($date_end_search)
			) || (
				($row['his_stt'] == 'InActive' || $row['his_stt'] == 'Absent')
				&& date('m-Y', strtotime($row['his_date_end'])) == $month && $row['is_paid']
			)) {
				// ngày công
				$working_days = $row['working_days'];

				// thu nhập tạm tính
				$term_income = ($row['income'] + $row['allowance']) / 26 * $working_days;

				// tính cột BHXH = BHXH(8%) + BHYT(1.5%) + BHTN(1%)
				$insurance = $row['social_insurance'] * 0.08 + $row['social_insurance'] * 0.015 + $row['social_insurance'] * 0.01;
				if ($row['user_id'] == 'ebc40fa1-8878-1a86-000d-5b6949a87e11' && (int)date('m') > 3 && (int)date('Y') > 2023) $insurance = 0;

				$total_bonus = ($row['income'] + $row['allowance']) / 26 * $working_days - $row['minus'] + $row['effort'] + $row['extra_amount'] + $row['sales'] - $insurance - $row['minus_income'];
				// tính cột thực lãnh 
				$total = ($row['income'] + $row['allowance']) / 26 * $working_days - $row['minus'] + $row['effort'] + $row['extra_amount'] + $row['sales'] - $insurance - $row['minus_income'];

				if ($total < 0) $total = 0;
				if ($total == 0) {
					if ($row['actual_salary'] > 0)
						$total = $row['actual_salary'];
				}

				// cột quỹ dự phòng
				if ($is_special_user) {
					$backup_fund = '<td class="text-end">' . format_number($row['backup_fund']) . '</td>';
				} else {
					$backup_fund = '';
				}

				// cột thưởng ds nếu đã duyệt hoặc người được phép mới thấy
				if ($is_special_user || $row['is_approved']) {
					$sales = '<td class="text-end hide-mobile sales">' . format_number($row['sales']) . '</td>';
					$is_approved = 1;
				} else {
					$sales = '<td class="hide-mobile"></td>';
				}

				// nếu bảng lương chưa duyệt thì cột không đạt ds cho admin sửa
				if (!$row['is_approved'] && is_admin($current_user)) {
					$minus_income_html = '<td class="minus_inc_col text-center">
										<input type="text" class="box-input allow-number-only right minusinc_edit" id="minus_inc' . $i . '" user="' . $row['user_id'] . '" month="' . $month_search . '" year="' . $year_search . '" ln="' . $i . '" value="' . format_number($row['minus_income']) . '">
										<div id="minusinc_stt' . $i . '"></div>
									</td>';
				} else {
					$minus_income_html = '<td class="text-end minus_inc_col">' . format_number($row['minus_income']) . '</td>';
				}


				$html .= '
				<tr>
					<td class="text-center hide-mobile">' . ($i + 1) . '</td>
					<td><a href="index.php?module=EC_Employee_Salary&action=employeesalary&for=showdetail&user_id=' . $row['user_id'] . '&month=' . $month_search . '&year=' . $year_search . '" target="_blank">' . $row['full_name'] . '</a></td>
					<td class="text-end"><span class="show_detail luongcung" employee_name="' . $row['full_name'] . '" employee="' . $row['user_id'] . '" type="allowance">' . format_number($row['income'] + $row['allowance']) . '</span></td>
					<td class="text-end working_days">' . (float)$working_days . '</td>
					<td class="text-end term_income hide-mobile">' . format_number($term_income) . '</td>
					' . $minus_income_html  . '
					<td class="text-end hide-mobile minus"><span class="show_detail" employee_name="' . $row['full_name'] . '" employee="' . $row['user_id'] . '" type="minus">' . format_number($row['minus']) . '</span></td>
					<td class="text-end hide-mobile plus"><a href="index.php?module=EC_Employee_Salary&action=employeesalary&for=effortdetail&user_id=' . $row['user_id'] . '&month=' . $month_search . '&year=' . $year_search . '" target="_blank">' . format_number($row['effort'] + $row['extra_amount']) . '</a></td>
					' . $sales . '
					<td class="text-end hide-mobile bhxh">' . format_number($insurance) . '</td>
					<td class="text-end actual_salary" id="final_inc' . $i . '" init_final_inc="' . round($total + $row['minus_income']) . '">' . format_number($total) . '</td>
				</tr>';

				$total_income += ($row['income'] + $row['allowance']);
				$total_allowance += $row['allowance'];
				$total_working_days += $working_days;
				$total_term_income += $term_income;
				$total_minus += $row['minus'];
				$total_effort += $row['effort'] + $row['extra_amount'];
				$total_insurance += $insurance;
				$total_sale += $row['sales'];
				$total_minus_income += $row['minus_income'];
				$total_amount += round($total_bonus);
				$total_tl_amount += round($total);
				$total_backup_fund += $row['backup_fund'];
				$i++;
			}
		}

		// cột quỹ dự phòng
		if ($is_special_user) {
			$backup_fund_html = '<td class="text-end"><b>' . format_number($total_backup_fund) . '</b></td>';
		} else {
			$backup_fund_html = '';
		}

		// cột thưởng DS
		if ($is_special_user || $is_approved) {
			$sale_html = '<td class="text-end hide-mobile"><b>' . format_number($total_sale) . '</b></td>';
		} else {
			$sale_html = '<td class="hide-mobile"></td>';
		}

		$html .= '<tr class="footer-tr">
					<td><b>Tổng</b></td>
					<td class="hide-mobile"></td>
					<td class="text-end total_income"><b>' . format_number($total_income) . '</b></td>
					<td class="text-end total_working_days"><b>' . $total_working_days . '</b></td>
					<td class="text-end hide-mobile"><b>' . format_number($total_term_income) . '</b></td>
					<td class="text-end" id="total_minus_inc"><b>' . format_number($total_minus_income) . '</b></td>
					<td class="text-end hide-mobile"><b>' . format_number($total_minus) . '</b></td>
					<td class="text-end hide-mobile"><b>' . format_number($total_effort) . '</b></td>
					' . $sale_html . '
					<td class="text-end hide-mobile"><b>' . format_number($total_insurance) . '</b></td>
					<td class="text-end" id="total_final_inc"><b>' . format_number($total_tl_amount) . '</b></td>
 				</tr>';

		return $html;
	}

	function calculateSalaryEdit($month_search, $year_search)
	{
		global $current_user;

		// kiểm tra đã có bảng lương hiện tại
		$is_exist = $this->checkExistSalary($month_search, $year_search);

		$m_search 	= $month_search . '-' . $year_search;
		$month 		= str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
		$today 		= date('d-m-Y');
		$start_date 	= date('Y-m-01', strtotime('01-' . $m_search));
		$end_date 	= date('Y-n-t', strtotime('01-' . $m_search));

		if ($month == date('m-Y') && !$is_exist)
			$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '" AND month = ' . date('n', strtotime('-1 month', strtotime('01-' . $month))) . ' AND year = ' . date('Y', strtotime('-1 month', strtotime('01-' . $month)));
		else
			$from_date_limit = ' AND month = ' . $month_search . ' AND year = ' . $year_search;
		$sql = '
		SELECT 
			CONCAT(
				u.last_name
				, " "
				, IFNULL(u.first_name, "")
			) AS full_name
			, SUM(IFNULL(s.basic_salary, 0)) AS basic_salary
			, SUM(IFNULL(s.efficient_wage, 0)) AS efficient_wage
			, SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income
			, SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
			, s.no_paid_days AS no_paid_days
			, IFNULL(l.absence_days, 0) AS leave_days
			, u.id AS user_id, u.his_date_start, u.his_date_end, u.his_stt
			, s.id AS salary_id, s.working_days
			, s.ot_days
			, CONCAT(s.month, "-", s.year) AS salary_start_month
			, s.social_insurance, s.overnight
			, s.delivery, s.minus, s.minus_income
			, s.effort, s.sales, s.actual_salary
		FROM (
			SELECT 
				usr.id, usr.start_working_date, usr.status
				, usr.last_name, usr.first_name
				, usr.employee_type, usr.deleted
				, usr.title, his.with_salary AS with_salary
				, his.description AS history_desc
				, his.status AS his_stt
				, IFNULL(
					(
						SELECT date_start
						FROM ec_workhistory h
						WHERE deleted = 0
						AND status = "Active"
						AND assigned_user_id = usr.id
						AND date_start >= IFNULL(
							( 
								SELECT date_start 
								FROM ec_workhistory 
								WHERE deleted = 0 
								AND status = "InActive" 
								AND assigned_user_id = h.assigned_user_id
								ORDER BY date_start DESC LIMIT 1 
							),  "1970-01-01"
						) 
						ORDER BY date_start LIMIT 1
					)
					, IFNULL(
						(
							SELECT date_start
							FROM ec_workhistory h
							WHERE deleted = 0
							AND status = "Active"
							AND assigned_user_id = usr.id
							AND date_start <= IFNULL(
								( 
									SELECT date_start 
									FROM ec_workhistory 
									WHERE deleted = 0 
									AND status = "InActive" 
									AND assigned_user_id = h.assigned_user_id
									ORDER BY date_start DESC LIMIT 1 
								),  "1970-01-01"
							) 
							ORDER BY date_start LIMIT 1
						)
						, DATE_ADD("' . date('Y-m-01', strtotime($start_date)) . '", INTERVAL 1 DAY)
					)
				) AS his_date_start
				, (
					SELECT 
						IFNULL(
							date_end
							, IF(
								status = "InActive" OR status = "Absent"
								, DATE_SUB(date_start, INTERVAL 1 DAY)
								, "' . $end_date . '"
							)
						)
					FROM ec_workhistory
					WHERE deleted = 0
					AND assigned_user_id = usr.id
					ORDER BY date_start DESC LIMIT 1
				) AS his_date_end
				, his.date_entered
			FROM users usr
			INNER JOIN ec_workhistory his
			ON his.assigned_user_id = usr.id
			AND his.deleted = 0
			AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($start_date)) . '"
			AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
			WHERE usr.deleted = 0
			AND his.date_start IN ( 
					SELECT MAX(sub_his.date_start) FROM ec_workhistory sub_his 
					WHERE DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($start_date)) . '" 
					AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '" 
					AND sub_his.deleted = 0 
					GROUP BY sub_his.assigned_user_id )
			-- GROUP BY usr.id
		) AS u 
		LEFT JOIN (
			SELECT 
				SUM(IFNULL(absence_days, 0)) AS absence_days
				, assigned_user_id
			FROM ec_leaveabsences 
			WHERE deleted = 0
			AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
			OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '") 
			AND (to_date <= "' . $today . '"
			OR from_date <= "' . $today . '")
			GROUP BY assigned_user_id
		) AS l ON l.assigned_user_id = u.id
		LEFT JOIN (
			SELECT basic_salary, efficient_wage, gas_allowance, lunch_allowance
					, tele_allowance, responsible_allowance, seniority_allowance
					, other_allowance1, other_allowance2, social_insurance, no_paid_days
					, assigned_user_id, working_days
					, actual_salary, minus_income
					, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, minus) AS minus
					, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, effort) AS effort
					, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, sales) AS sales
					, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, ot_days) AS ot_days
					, id, month, year, overnight, delivery
			FROM ec_employee_salary WHERE deleted = 0 
			AND CONCAT(year, "-", month, "-01") <= "' . $year_search . "-" . $month_search . "-01" . '"
			AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
				SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
				AND CONCAT(year, "-", month, "-01") <= "' . $end_date . '" 
				ORDER BY date_entered DESC LIMIT 1
			)
			' . $from_date_limit . '
			GROUP BY assigned_user_id
		) AS s ON s.assigned_user_id = u.id
		WHERE u.deleted = 0 
		AND u.his_date_start IS NOT NULL
		GROUP BY u.id
		ORDER BY (
			CASE 
				WHEN u.title LIKE "%QuanLy%" THEN 1
				WHEN u.title LIKE "%KeToan%" THEN 2
				WHEN u.title LIKE "%Leader%" THEN 3
				WHEN u.title LIKE "%Booker%" THEN 4
			ELSE 5
			END 
		), u.his_date_start, u.first_name, u.last_name';

		// if($current_user->user_name == 'hungnh') {
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		$i = 0;
		$html = '';
		while ($row = $this->bean->db->fetchByAssoc($res)) {

			if (($row['his_stt'] == 'Active' && strtotime($row['his_date_start']) <= strtotime($end_date)) || ($row['his_stt'] != 'Active' && date('m-Y', strtotime($row['his_date_start'])) == $month)) {
				// tính số ngày công	
				$working_days = $row['working_days'];
				if (($working_days > date('j') || empty($working_days)) && $month_search == date('n')) {
					$working_days = date('j');
				}

				if ($row['salary_start_month'] == $month_search . "-" . $year_search) {
					$salary_detail = $row['salary_id'];
				} else {
					$salary_detail = '';
				}

				$government = $row['social_insurance'] * 0.08 + $row['social_insurance'] * 0.015 + $row['social_insurance'] * 0.01;
				$total = ($row['income'] + $row['allowance']) / 26 * $working_days - $row['minus'] + $row['effort'] + $row['sales'] - $government;
				if ($total < 0) $total = 0;
				if ($total == 0 && $row['actual_salary'] > 0) {
					$total = $row['actual_salary'];
				}

				// để class cho dòng chẵn
				if ($i % 2 == 0) {
					$row_class = "row-odd";
				} else {
					$row_class = "";
				}

				$html .= '
				<tr class="user_line' . $i . ' ' . $row_class . '">
					<td class="text-center" rowspan="4">' . $i . '</td>
					<td rowspan="4">' . $row['full_name'] . '
						<input type="hidden" name="full_name[]" id="full_name' . $i . '" value="' . $row['full_name'] . '">
						<input type="hidden" name="assigned_user_id[]" id="assigned_user_id' . $i . '" value="' . $row['user_id'] . '">
						<input type="hidden" name="salary_detail[]" id="salary_detail' . $i . '" value="' . $salary_detail . '">
						<input type="hidden" name="working_days[]" id="working_days' . $i . '" value="' . (float)$working_days . '">
					</td>
					<td width="12%" class="text-center fw-bold row-title">Lương CB</td>
					<td width="12%" class="text-center fw-bold row-title">Lương HQ</td>
					<td width="11%" class="text-center fw-bold row-title">Thu nhập</td>
					<td width="12%" class="text-center fw-bold row-title">Giảm trừ TN</td>
					<td width="15%" class="text-center fw-bold row-title">Thực lãnh</td>
				</tr>';

				$html .= '
				<tr class="user_line' . $i . ' ' . $row_class . '">
					<td class="text-end">
						<input type="text" name="basic_salary[]" id="basic_salary' . $i . '" class="box-input allow-number-only" value="' . format_number($row['basic_salary']) . '" oninput="calculateIncome(' . $i . ')" onpaste="setTimeout(function(){calculateIncome(' . $i . ');}, 500);">
					</td>
					<td class="text-end">
						<input type="text" name="efficient_wage[]" id="efficient_wage' . $i . '" class="box-input allow-number-only" value="' . format_number($row['efficient_wage']) . '" oninput="calculateIncome(' . $i . ')" onpaste="setTimeout(function(){calculateIncome(' . $i . ');}, 500);">
					</td>
					<td class="text-end" id="salary' . $i . '">' . format_number($row['income']) . '</td>
					<td class="text-end">
						<input type="text" name="minus_income[]" id="minus_income' . $i . '" class="box-input allow-number-only" value="' . $row['minus_income'] . '" onkeyup="calculateIncome(' . $i . ');" onpaste="setTimeout(function(){calculateIncome(' . $i . ');}, 500);">
					</td>
					<td class="text-end">
						<input name="total[]" id="total' . $i . '" class="allow-number-only box-input" type="text" value="' . format_number(($total - $row['minus_income'])) . '" onkeyup="calculateIncome(' . $i . ', 1);" onpaste="setTimeout(function(){calculateIncome(' . $i . ', 1);}, 500);">
						<input type="hidden" id="cusSalary' . $i . '" name="cusSalary[]" value="0">
					</td>
				</tr>';

				$html .= '
				<tr class="user_line' . $i . ' ' . $row_class . '">
					<td class="text-center fw-bold row-title">Phụ cấp</td>
					<td class="text-center fw-bold row-title">Giảm trừ</td>
					<td class="text-center fw-bold row-title">Nỗ lực</td>
					<td class="text-center fw-bold row-title">Thưởng DS</td>
					<td class="text-center fw-bold row-title">BHXH</td>
				</tr>';

				$html .= '
				<tr class="user_line' . $i . ' ' . $row_class . '">
					<td class="text-end" id="allowance' . $i . '">' . format_number($row['allowance']) . '</td>
					<td class="text-end">
						<span class="show_detail" employee_name="' . $row['full_name'] . '" employee="' . $row['user_id'] . '" type="minus" id="minus' . $i . '" view="Edit">' . format_number($row['minus']) . '</span>
					</td>
					<td class="text-end"><span class="show_detail" employee_name="' . $row['full_name'] . '" employee="' . $row['user_id'] . '" type="bonus" overnight="' . format_number($row['overnight']) . '" delivery="' . format_number($row['delivery']) . '">' . format_number($row['effort']) . '</span></td>
					<td class="text-end" id="sales' . $i . '">' . format_number($row['sales']) . '</td>
					<td class="text-end" id="government' . $i . '">' . format_number($government) . '</td>
				</tr>';

				$i++;
			}
		}

		return $html;
	}

	function calculateCommission($month_search, $year_search)
	{
		$month = $month_search . '-' . $year_search;
		$sql = 'SELECT *, CONCAT(month, "-", year) AS commission_start_month 
 				FROM ec_commission 
 				WHERE deleted = 0 AND month = ' . $month_search . ' 
 				AND year = ' . $year_search;

		$res = $this->bean->db->query($sql);
		$i = -1;
		$html = '';
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ($row['commission_start_month'] == $month) {
				$commission_detail = $row['id'];
			} else {
				$commission_detail = '';
			}

			$html .= '<tr id="commission_line' . ++$i . '">
						<td id="commission_order' . $i . '" class="text-center">' . $i . '</td>
						<td><input type="text" class="allow-number-only box-input" name="commission_from_amt[]" value="' . format_number($row['from_value']) . '"></td>
						<td><input type="text" class="allow-number-only box-input" name="commission_to_amt[]" value="' . format_number($row['to_value']) . '"></td>
						<td><input type="text" class="allow-number-only box-input" name="commission_percentage[]" value="' . format_number($row['percentage']) . '"></td>
						<td class="text-center">
							<button title="Xóa" type="button" class="button-remove-in-edit" onclick="markRowDeleted(\'commission_line\', \'commission_deleted\', \'commission_order\', ' . $i . ')">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
							</button>
							<input type="hidden" name="commission_deleted[]" id="commission_deleted' . $i . '" value="0" class="commission_deleted">
							<input type="hidden" name="commission_detail[]" id="commission_detail' . $i . '" value="' . $commission_detail . '">
							<input type="hidden" name="commission_order[]" value="' . $row['name'] . '">
						</td>
	 		</tr>';
		};

		return array('html' => $html, 'count' => $i += 1);
	}

	function calculateAllowance($month_search, $year_search)
	{
		// kiểm tra đã có bảng lương hiện tại
		$is_exist = $this->checkExistSalary($month_search, $year_search);

		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;

		if ($month == date('m-Y') && !$is_exist)
			$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '" AND month = ' . date('n', strtotime('-1 month', strtotime('01-' . $month))) . ' AND year = ' . date('Y', strtotime('-1 month', strtotime('01-' . $month)));
		else
			$from_date_limit = ' AND month = ' . $month_search . ' AND year = ' . $year_search;

		$start_date = $year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-01';
		$end_date = date('Y-n-t', strtotime($year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-01'));

		$sql = '
			SELECT 
				CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
				, s.gas_allowance, s.lunch_allowance, s.tele_allowance
				, s.responsible_allowance, s.seniority_allowance, s.other_allowance1
				, s.other_allowance2, u.his_date_start, u.his_date_end, u.his_stt
			FROM (
				SELECT 
					usr.id, usr.last_name, usr.first_name
					, usr.employee_type, usr.deleted
					, usr.title, his.with_salary AS with_salary
					, his.status AS his_stt
					, IFNULL(
						(
							SELECT date_start
							FROM ec_workhistory h
							WHERE deleted = 0
							AND status = "Active"
							AND assigned_user_id = usr.id
							AND date_start >= IFNULL(
								( 
									SELECT date_start 
									FROM ec_workhistory 
									WHERE deleted = 0 
									AND status = "InActive" 
									AND assigned_user_id = h.assigned_user_id
									ORDER BY date_start DESC LIMIT 1 
								),  "1970-01-01"
							) 
							ORDER BY date_start LIMIT 1
						)
						, IFNULL(
							(
								SELECT date_start
								FROM ec_workhistory h
								WHERE deleted = 0
								AND status = "Active"
								AND assigned_user_id = usr.id
								AND date_start <= IFNULL(
									( 
										SELECT date_start 
										FROM ec_workhistory 
										WHERE deleted = 0 
										AND status = "InActive" 
										AND assigned_user_id = h.assigned_user_id
										ORDER BY date_start DESC LIMIT 1 
									),  "1970-01-01"
								) 
								ORDER BY date_start LIMIT 1
							)
							, DATE_ADD("' . date('Y-m-01', strtotime($start_date)) . '", INTERVAL 1 DAY)
						)
					) AS his_date_start
					, (
						SELECT IFNULL(
							date_end
							, IF(
								status = "InActive" OR status = "Absent"
								, DATE_SUB(date_start, INTERVAL 1 DAY)
								, "' . $end_date . '"
							)
						)
						FROM ec_workhistory
						WHERE deleted = 0
						AND assigned_user_id = usr.id
						ORDER BY date_start DESC LIMIT 1
					) AS his_date_end
 					FROM users usr
 					INNER JOIN ec_workhistory his
 					ON his.assigned_user_id = usr.id
 					AND his.deleted = 0
 					AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $start_date . '"
 					AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
 					WHERE usr.deleted = 0
					AND his.date_start IN ( 
						SELECT MAX(sub_his.date_start) FROM ec_workhistory sub_his 
						WHERE DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $start_date . '"
						AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
						AND sub_his.deleted = 0 
						GROUP BY sub_his.assigned_user_id )
					-- GROUP BY usr.id
 				) AS u 
 				LEFT JOIN (
 					SELECT * FROM ec_employee_salary WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "' . $year_search . "-" . $month_search . "-01" . '"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "' . $end_date . '" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					' . $from_date_limit . '
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.his_date_start IS NOT NULL
 				GROUP BY u.id
 				ORDER BY (
 					CASE 
 						WHEN u.title LIKE "%QuanLy%" THEN 1
 						WHEN u.title LIKE "%KeToan%" THEN 2
 						WHEN u.title LIKE "%Leader%" THEN 3
 						WHEN u.title LIKE "%Booker%" THEN 4
 					ELSE 5
 					END 
 				), u.his_date_start, u.first_name, u.last_name';

		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		$i = -1;
		$html = '';
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if (($row['his_stt'] == 'Active' && strtotime($row['his_date_start']) <= strtotime($end_date)) || ($row['his_stt'] != 'Active' && date('m-Y', strtotime($row['his_date_start'])) == $month)) {
				$total_allowance = (int)$row['gas_allowance'] + (int)$row['lunch_allowance'] + (int)$row['tele_allowance'] + (int)$row['responsible_allowance'] + (int)$row['seniority_allowance'] + (int)$row['other_allowance1'] + (int)$row['other_allowance2'];
				$html .= '<tr>
					<td class="text-center">' . ++$i . '</td>
					<td>' . $row['full_name'] . '</td>
					<td><input name="gas_allowance[]" id="gas_allowance' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['gas_allowance']) . '"></td>
					<td><input name="lunch_allowance[]" id="lunch_allowance' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['lunch_allowance']) . '"></td>
					<td><input name="tele_allowance[]" id="tele_allowance' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['tele_allowance']) . '"></td>
					<td><input name="responsible_allowance[]" id="responsible_allowance' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['responsible_allowance']) . '"></td>
					<td><input name="seniority_allowance[]" id="seniority_allowance' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['seniority_allowance']) . '"></td>
					<td><input name="other_allowance1[]" id="other_allowance1' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['other_allowance1']) . '"></td>
					<td><input name="other_allowance2[]" id="other_allowance2' . $i . '" class="allow-number-only box-input" type="text" onpaste="setTimeout(function(){calculateTotalAllowance(' . $i . ');}, 500);"oninput="calculateTotalAllowance(' . $i . ')" value="' . format_number($row['other_allowance2']) . '"></td>
					<td class="text-end" id="total_allowance' . $i . '">' . format_number($total_allowance) . '</td>
				</tr>';
			}
		}

		return $html;
	}

	function calculateInsurrance($month_search, $year_search)
	{
		// kiểm tra đã có bảng lương hiện tại
		$is_exist = $this->checkExistSalary($month_search, $year_search);

		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;

		if ($month == date('m-Y') && !$is_exist)
			$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '" AND month = ' . date('n', strtotime('-1 month', strtotime('01-' . $month))) . ' AND year = ' . date('Y', strtotime('-1 month', strtotime('01-' . $month)));
		else
			$from_date_limit = ' AND month = ' . $month_search . ' AND year = ' . $year_search;

		$start_date = $year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-01';
		$end_date = date('Y-n-t', strtotime($year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-01'));

		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , s.social_insurance, u.his_stt, u.his_date_start
 				FROM (
					SELECT usr.id, usr.start_working_date, usr.status
 						 , usr.last_name, usr.first_name
 						 , usr.employee_type, usr.deleted
 						 , usr.title, his.with_salary AS with_salary
 						 , his.description AS history_desc
 						 , his.status AS his_stt
 						 , IFNULL(
							(
								SELECT date_start
								FROM ec_workhistory h
								WHERE deleted = 0
								AND status = "Active"
								AND assigned_user_id = usr.id
								AND date_start >= IFNULL(
									( 
										SELECT date_start 
										FROM ec_workhistory 
										WHERE deleted = 0 
										AND status = "InActive" 
										AND assigned_user_id = h.assigned_user_id
										ORDER BY date_start DESC LIMIT 1 
									),  "1970-01-01"
								) 
								ORDER BY date_start LIMIT 1
							)
							, IFNULL(
								(
									SELECT date_start
									FROM ec_workhistory h
									WHERE deleted = 0
									AND status = "Active"
									AND assigned_user_id = usr.id
									AND date_start <= IFNULL(
										( 
											SELECT date_start 
											FROM ec_workhistory 
											WHERE deleted = 0 
											AND status = "InActive" 
											AND assigned_user_id = h.assigned_user_id
											ORDER BY date_start DESC LIMIT 1 
										),  "1970-01-01"
									) 
									ORDER BY date_start LIMIT 1
								)
								, DATE_ADD("' . date('Y-m-01', strtotime($start_date)) . '", INTERVAL 1 DAY)
							)
						) AS his_date_start
						, (
							SELECT IFNULL(
								date_end
								, IF(
									status = "InActive" OR status = "Absent"
									, DATE_SUB(date_start, INTERVAL 1 DAY)
									, "' . $end_date . '"
								)
							)
							FROM ec_workhistory
							WHERE deleted = 0
							AND assigned_user_id = usr.id
							ORDER BY date_start DESC LIMIT 1
						 ) AS his_date_end
 					FROM users usr
 					INNER JOIN ec_workhistory his
 					ON his.assigned_user_id = usr.id
 					AND his.deleted = 0
 					AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($start_date)) . '"
 					AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
 					WHERE usr.deleted = 0
					AND his.date_start IN ( 
						SELECT MAX(sub_his.date_start) FROM ec_workhistory sub_his 
						WHERE DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($start_date)) . '" 
						AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '" 
						AND sub_his.deleted = 0 
						GROUP BY sub_his.assigned_user_id )
					-- GROUP BY usr.id
				) AS u 
 				LEFT JOIN (
 					SELECT * FROM ec_employee_salary WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "' . $end_date . '"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "' . $end_date . '" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					' . $from_date_limit . '
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.his_date_start IS NOT NULL AND LENGTH(u.his_date_start) > 0
 				GROUP BY u.id
 				ORDER BY (
 					CASE 
 						WHEN u.title LIKE "%QuanLy%" THEN 1
 						WHEN u.title LIKE "%KeToan%" THEN 2
 						WHEN u.title LIKE "%Leader%" THEN 3
 						WHEN u.title LIKE "%Booker%" THEN 4
 					ELSE 5
 					END 
 				), u.his_date_start, u.first_name, u.last_name';

		// if($GLOBALS['current_user']->user_name == 'nponline') {
		// 	echo $sql; exit;
		// }

		$res = $this->bean->db->query($sql);
		$i = -1;
		$html = '';
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if (($row['his_stt'] == 'Active' && strtotime($row['his_date_start']) <= strtotime($end_date)) || ($row['his_stt'] != 'Active' && date('m-Y', strtotime($row['his_date_start'])) == $month)) {
				$html .= '<tr>
					<td class="text-center">' . ++$i . '</td>
					<td>' . $row['full_name'] . '</td>
					<td><input name="social_insurance[]" id="insurance_rate' . $i . '" class="allow-number-only box-input text-end" type="text" onpaste="setTimeout(function(){calculateTotalInsurance(' . $i . ');}, 500);" oninput="calculateTotalInsurance(' . $i . ')" value="' . $row['social_insurance'] . '"></td>
					<td class="text-end" id="com_social_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.175) . '</td>
					<td class="text-end" name="com_health_insurance[]" id="com_health_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.03) . '</td>
					<td class="text-end" name="com_accident_insurance[]" id="com_accident_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.01) . '</td>
					<td class="text-end" name="emp_social_insurance[]" id="emp_social_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.08) . '</td>
					<td class="text-end" name="emp_health_insurance[]" id="emp_health_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.015) . '</td>
					<td class="text-end" name="emp_accident_insurance[]" id="emp_accident_insurance' . $i . '">' . format_number($row['social_insurance'] * 0.01) . '</td>
				</tr>';
			}
		}

		return $html;
	}

	function saveCommission()
	{
		for ($i = 0; $i < count($_POST['commission_deleted']); $i++) {
			$commission = new EC_Commission;
			$commission->id 			= $_POST['commission_detail'][$i];
			$commission->name 			= $_POST['commission_order'][$i];
			$commission->month 			= $_REQUEST['month_search'];
			$commission->year 			= $_REQUEST['year_search'];
			$commission->from_value 		= $_POST['commission_from_amt'][$i];
			$commission->to_value 		= $_POST['commission_to_amt'][$i];
			$commission->percentage 		= $_POST['commission_percentage'][$i];
			$commission->deleted 		= $_POST['commission_deleted'][$i];
			if ($commission->deleted == 1) {
				$commission->mark_deleted($commission->id);
			} else {
				$commission->save();
			}
		}
	}

	function saveSalaryInf()
	{
		for ($i = 0; $i < count($_POST['basic_salary']); $i++) {
			$salary_id = $this->checkExistUserSalary($_POST['assigned_user_id'][$i], $_POST['month'], $_POST['year']);

			// nếu ấn định lương thực lãnh thì các giá trị khác = 0
			if ($_POST['cusSalary'][$i] == 1) {
				$_POST['basic_salary'][$i] 			= 0;
				$_POST['efficient_wage'][$i] 			= 0;
				$_POST['minus'][$i] 				= 0;
				$_POST['gas_allowance'][$i] 			= 0;
				$_POST['lunch_allowance'][$i] 		= 0;
				$_POST['tele_allowance'][$i] 			= 0;
				$_POST['responsible_allowance'][$i] 	= 0;
				$_POST['seniority_allowance'][$i] 		= 0;
				$_POST['other_allowance1'][$i] 		= 0;
				$_POST['other_allowance2'][$i] 		= 0;
				$_POST['social_insurance'][$i] 		= 0;
			}

			$salary = BeanFactory::newBean('EC_Employee_Salary');
			$salary->id 					= $salary_id;
			$salary->assigned_user_id 		= $_POST['assigned_user_id'][$i];
			$salary->name 					= $_POST['full_name'][$i];
			$salary->month 				= $_POST['month'];
			$salary->year 					= $_POST['year'];
			$salary->basic_salary 			= $_POST['basic_salary'][$i];
			$salary->efficient_wage 			= $_POST['efficient_wage'][$i];
			if (isset($_POST['minus'])) {
				$salary->minus 			= $_POST['minus'][$i];
			}
			$salary->minus_income 			= $_POST['minus_income'][$i];

			// phụ cấp
			$salary->gas_allowance 			= $_POST['gas_allowance'][$i];
			$salary->lunch_allowance 		= $_POST['lunch_allowance'][$i];
			$salary->tele_allowance 			= $_POST['tele_allowance'][$i];
			$salary->responsible_allowance 	= $_POST['responsible_allowance'][$i];
			$salary->seniority_allowance 		= $_POST['seniority_allowance'][$i];
			$salary->other_allowance1 		= $_POST['other_allowance1'][$i];
			$salary->other_allowance2 		= $_POST['other_allowance2'][$i];

			// BHXH
			$salary->social_insurance = $_POST['social_insurance'][$i];

			if ($_POST['cusSalary'][$i] == 1) {
				$salary->working_days 	= 0;
				//  $salary->actual_salary 		= $_POST['total'][$i];
			}
			$salary->actual_salary 			= $_POST['total'][$i];
			$salary->save();

			// cập nhật thông tin lương và phụ cấp cho nhân viên chỉ khi nhập đúng tháng hiện tại
			// nhập khác tháng thì không cập nhật
			if ($_POST['month'] . '-' . $_POST['year'] >= date('m-Y', strtotime('-1 month')) && $_POST['cusSalary'][$i] == 0) {
				$u = BeanFactory::getBean('Users', $_POST['assigned_user_id'][$i]);
				$u->basic_salary 			= str_replace(',', '', $_POST['basic_salary'][$i]);
				$u->efficient_wage 			= str_replace(',', '', $_POST['efficient_wage'][$i]);
				$u->gas_allowance 			= str_replace(',', '', $_POST['gas_allowance'][$i]);
				$u->lunch_allowance 		= str_replace(',', '', $_POST['lunch_allowance'][$i]);
				$u->tele_allowance 			= str_replace(',', '', $_POST['tele_allowance'][$i]);
				$u->responsible_allowance 	= str_replace(',', '', $_POST['responsible_allowance'][$i]);
				$u->seniority_allowance 	= str_replace(',', '', $_POST['seniority_allowance'][$i]);
				$u->other_allowance1 		= str_replace(',', '', $_POST['other_allowance1'][$i]);
				$u->other_allowance2 		= str_replace(',', '', $_POST['other_allowance2'][$i]);
				$u->save2();
			}

			// nếu đã ấn tính lương thực lãnh thì xoá các dòng chi tiết về phụ cấp, nỗ lực, thưởng ds của nhân viên trong tháng đó
			if ($_POST['cusSalary'][$i] == 1) {
				$date = '01-' . str_pad($_POST['month'], 2, 0, STR_PAD_LEFT) . '-' . $_POST['year'];
				$sql = '
					UPDATE ec_salary_details 
					SET deleted = 1 
					WHERE assigned_user_id = "' . $_POST['assigned_user_id'][$i] . '"
					AND voucher_date >= "' . date('Y-m-d', strtotime($date)) . '"
					AND voucher_date <= "' . date('Y-m-t', strtotime($date)) . '"
				';
				$this->bean->db->query($sql);
			}
		}
	}

	function checkExistUserSalary($user_id, $month, $year)
	{
		global $db;

		$sql = 'SELECT id FROM ec_employee_salary 
 				WHERE deleted = 0 AND assigned_user_id = "' . $user_id . '"
 				AND month = "' . $month . '" AND year = "' . $year . '"';
		$res = $db->query($sql);
		$row = $db->fetchByAssoc($res);

		return $row['id'];
	}

	// hiện danh sách chi tiết nỗ lực
	function populateEffortDetail($smartyobj, $user_id, $type = 0)
	{
		$user = new User;
		$user->retrieve($user_id);

		$effort_type = array(
			0 => 'Tất cả',
			// 1 => 'Cú đêm',
			2 => 'Giao vé',
			3 => 'Booking xử lý',
			4 => 'Thưởng thêm',
		);

		$smartyobj->assign('isEffortDetail', 1);
		$smartyobj->assign('MONTH', $_REQUEST['month']);
		$smartyobj->assign('YEAR', $_REQUEST['year']);
		$smartyobj->assign('EMPLOYEE_NAME', $user->last_name . ' ' . $user->first_name);
		$smartyobj->assign('EMPLOYEE', $user_id);
		$smartyobj->assign('EFFORT_OPTION', get_select_options_with_id($effort_type, (int)$type));

		$fdate = $_REQUEST['year'] . '-' . str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT) . '-01';
		$tdate = date('Y-m-t', strtotime($_REQUEST['year'] . '-' . str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT) . '-01'));

		$sql = '';

		// cú đêm
		if ($type == 0 || $type == 1 || $type == 3) {
			$sql .= '
			SELECT (
				CASE	
					WHEN t.start_process_time >= DATE_FORMAT( t.start_process_time, "%Y-%m-%d 00:00:00" ) 
						AND t.start_process_time < DATE_FORMAT( t.start_process_time, "%Y-%m-%d 07:31:00" ) 
						AND transfer_time < DATE_FORMAT( t.start_process_time, "%Y-%m-%d 07:31:00" ) 
						AND t.is_paid = 1
						AND t.booking_status = 8
					THEN qty * 20000 
					WHEN t.start_process_time >= DATE_FORMAT( t.start_process_time, "%Y-%m-%d 21:00:00" ) 
						AND t.start_process_time <= DATE_FORMAT( t.start_process_time, "%Y-%m-%d 23:59:59" ) 
						AND transfer_time < DATE_FORMAT( DATE_ADD( t.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" ) AND t.is_paid = 1 
						AND t.booking_status = 8
					THEN qty * 20000
					ELSE 2000 END
				) AS amount
				, t.qty, t.assigned_user_id, t.booking_id, t.booking_name, t.type
				, t.start_process_time AS date_entered, t.transfer_time, t.bonus
			FROM (
				SELECT 
					SUM( dt.quantity ) * 20000 AS amount
					, SUM( dt.quantity ) AS qty
					, b.assigned_user_id
					, b.id AS booking_id
					, b.name AS booking_name
					, "BK" as type
					, b.booking_status
					, DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered 
					, DATE_ADD(
					(
						SELECT p.date_entered FROM ec_working_process p 
						WHERE p.deleted = 0 
						AND p.paid > 0 AND p.parent_id = b.id 
						AND p.description IS NOT NULL 
						AND LENGTH(p.description) > 0
						GROUP BY p.parent_id
					)
					, INTERVAL 7 HOUR) AS transfer_time 
					, DATE_ADD(
					(
						SELECT p.date_entered FROM ec_working_process p 
						WHERE p.deleted = 0 
						AND p.called > 0 AND p.parent_id = b.id 
						GROUP BY p.parent_id
					)
					, INTERVAL 7 HOUR) AS start_process_time 
					, 0 AS bonus, b.is_paid
				FROM
					ec_booking_details dt 
					INNER JOIN ec_flight_bookings b ON b.id = dt.booking_id AND b.deleted = 0
				WHERE
					dt.deleted = 0 
					AND b.assigned_user_id = "' . $user_id . '"
					AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
					AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59"
				GROUP BY b.id
				HAVING start_process_time >= "' . $fdate . ' 00:00:00" 
					AND start_process_time <= "' . $tdate . ' 23:59:59" 
					AND start_process_time NOT BETWEEN DATE_FORMAT( start_process_time, "%Y-%m-%d 07:31:00") AND DATE_FORMAT( start_process_time, "%Y-%m-%d 20:59:59")
			) AS t';

			// if($GLOBALS['current_user']->user_name == 'nponline') {
			// 	echo $sql; exit;
			// }

			if ($type == 1) $sql .= ' GROUP BY t.booking_id HAVING amount / qty >= 20000';
			if ($type == 3) $sql .= ' GROUP BY t.booking_id HAVING amount / qty < 20000';
		}

		// giao vé
		if ($type == 0 || $type == 2) {
			if (!empty($sql)) $sql .= ' UNION ';
			$sql .= '
				SELECT SUM(p.ticket_delivery) * 20000 AS amount, 0 AS qty
						, p.assigned_user_id
						, b.id AS booking_id
						, b.name AS booking_name 
						, "BK" as type 
						, DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered
						, "" AS transfer_time
						, 0 AS bonus
				FROM ec_working_process p 
				INNER JOIN ec_flight_bookings b 
				ON b.id = p.parent_id AND b.deleted = 0
				WHERE p.deleted = 0 AND p.ticket_delivery = 1 
				AND b.booking_status IN (3, 7, 8)
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
				AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
				AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
				AND p.assigned_user_id = "' . $user_id . '"
				GROUP BY b.id';

			// Giao sữa
			$sql .= ' UNION
				SELECT SUM(p.ticket_delivery) * 20000 AS amount, 0 AS qty
					, p.assigned_user_id
					, rv.id AS booking_id
					, rv.name AS booking_name  
					, "PT" as type
					, DATE_ADD(rv.date_entered, INTERVAL 7 HOUR) AS date_entered
					, "" AS transfer_time
					, 0 AS bonus
				FROM ec_working_process p 
				INNER JOIN ec_receipt_voucher rv 
				ON rv.id = p.parent_id AND rv.deleted = 0
				WHERE p.deleted = 0 AND p.ticket_delivery = 1 
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
				AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
				AND DATE_ADD(rv.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
				AND DATE_ADD(rv.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
				AND p.assigned_user_id = "' . $user_id . '"
				GROUP BY rv.id';
		}

		// thưởng thêm trong tháng
		if ($type == 0 || $type == 4) {
			if (!empty($sql)) $sql .= ' UNION ';
			$sql .=	'
				SELECT 0 AS amount, 0 AS qty
						, assigned_user_id
						, "" AS booking_id
						, description AS booking_name
						, "BK" as type
						, DATE_FORMAT(voucher_date, "%Y-%m-%d %H:%i:%s") AS date_entered
						, "" AS transfer_time
						, bonus_amount AS bonus				
				FROM ec_salary_details
				WHERE deleted = 0
				AND type = "bonus"
				AND voucher_date >= "' . $fdate . '"
				AND voucher_date <= "' . $tdate . '"
				AND assigned_user_id = "' . $user_id . '"

				UNION 
				SELECT 
					0 AS amount, 0 AS qty
					, assigned_user_id, "" AS booking_id
					, "Thưởng thêm thu nhập" AS booking_name
					, "" as type
					, DATE_FORMAT(date_entered, "%Y-%m-%d %H:%i:%s") AS date_entered
					, "" AS transfer_time
					, extra_amount AS bonus	
				FROM ec_employee_salary
				WHERE deleted = 0 AND assigned_user_id = "' . $user_id . '"
				AND month = "' . $_REQUEST['month'] . '" AND year = "' . $_REQUEST['year'] . '"
				ORDER BY date_entered';
		}

		if ($type != 0) $sql .= ' ORDER BY date_entered';

		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		$i = 0;
		$total_qty = $total_night = $total_delivery = $total_bonus = $total_exec_booking = 0;
		$html = '';
		$bk_profit_arr = array();
		while ($row = $this->bean->db->fetchByAssoc($res)) {

			// Cú đêm
			// <td class="text-end">'
			// 	.((!empty($row['qty']) 
			// 	&& ($row['amount'] / $row['qty']) >= 20000 
			// 	&& ($type == 0 || $type == 1))
			// 	?format_number($row['amount']):'').'
			// </td>

			// Booking xử lý
			// <td class="text-end">
			// 	'.((!empty($row['qty']) 
			// 	&& ($row['amount'] / $row['qty']) < 20000
			// 	&& ($type == 0 || $type == 3))?format_number($row['amount']):'').'
			// </td>

			if ($row['amount'] > 0 || $row['bonus'] > 0) {
				if (
					!empty($row['qty'])
					&& ($row['amount'] / $row['qty']) >= 20000
					&& ($type == 0 || $type == 1)
				) {
					$bk_profit_arr[] = $row['booking_id'];
					$bk_profit = '$' . $row['booking_id'] . '_profit';
				} else $bk_profit = '';

				if ($row['type'] == 'BK') {
					$name = (!empty($row['booking_id']) ? '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking_name'] . '</a>' : (!empty($row['booking_name']) ? $row['booking_name'] : ''));
				} else if ($row['type'] == 'PT') {
					$name = (!empty($row['booking_id']) ? '<a href="index.php?module=EC_Receipt_Voucher&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $row['booking_name'] . '</a>' : (!empty($row['booking_name']) ? $row['booking_name'] : ''));
				} else {
					$name = $row['booking_name'];
				}

				$html .= '
				<tr>
					<td class="text-center">' . $i++ . '</td>
					<td class="text-center">' . date('d-m-Y H:i:s', strtotime($row['date_entered'])) . '</td>
					<td class="text-center">' . (!empty($row['transfer_time']) ? date('d-m-Y H:i:s', strtotime($row['transfer_time'])) : '') . '</td>
					<td>' . $name . '</td>
					<td class="text-end">' . $bk_profit . '</td>
					<td class="text-end">' . $row['qty'] . '</td>
					<td class="text-end">
						' . (empty($row['qty']) ? format_number($row['amount']) : '') . '
					</td>
					<td class="text-end">
						' . (!empty($row['bonus']) ? format_number($row['bonus']) : '') . '
					</td>
				</tr>';

				(!empty($row['qty']) ? (($row['amount'] / $row['qty']) >= 20000 ? $total_night += $row['amount'] : $total_exec_booking += $row['amount']) : $total_delivery += $row['amount']);
				$total_qty += $row['qty'];
				$total_bonus += $row['bonus'];
			}
		}

		// $total = $total_night + $total_exec_booking + $total_delivery + $total_bonus;
		$total = $total_delivery + $total_bonus;

		$html .= '
		<tr>
			<td></td>
			<td></td>
			<td><b>Tổng</b></td>
			<td class="text-end"><b>' . format_number($total) . '</b></td>
			<td class="text-end"><b>$TOTAL_BK_PROFIT</b></td>
			<td class="text-end"><b>' . format_number($total_qty) . '</b></td>
			<td class="text-end"><b>' . format_number($total_delivery) . '</b></td>
			<td class="text-end"><b>' . format_number($total_bonus) . '</b></td>
		</tr>';

		$html .= '<table style="display:none" cellpadding="0" cellspacing="0"></table>';

		// tính ds các bk cú đêm
		$profit_arr = $this->calculateBKProfit($user_id, $fdate, $tdate, $bk_profit_arr);
		$html = str_replace(array_keys($profit_arr), $profit_arr, $html);

		$smartyobj->assign('DETAIL_TBL', $html);
	}

	function calculateBKProfit($user_id, $from_date, $to_date, $bk_arr)
	{
		$sql = '
			SELECT 
				bk.id AS booking_id, bk.name AS booking
				, SUM( bk.total_amount ) / COUNT( dt.id ) 
				- SUM(
					IFNULL( dt.total_bought_price, 0 )) 
					- IFNULL((
						SELECT SUM(amount) 
						FROM ec_payment_voucher 
						WHERE deleted = 0 AND booking_id = bk.id 
						AND pv_status = "3" 
						AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5"
					), 0) 
				- (
					SELECT
						SUM(
							IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) 
							+ IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)
						) 
					FROM
						ec_booking_passengers 
					WHERE
						deleted = 0 
						AND booking_id = bk.id 
						AND add_type IS NULL
				) AS doanhso
			FROM
				ec_flight_bookings bk
				LEFT JOIN ec_booking_details dt 
				ON dt.booking_id = bk.id 
				AND dt.deleted = 0 
			WHERE
				bk.deleted = 0 
				AND bk.booking_status = 8 
				AND bk.assigned_user_id = "' . $user_id . '"
				AND bk.id IN ("' . implode('","', $bk_arr) . '")
			GROUP BY bk.id';

		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// 	die();
		// }

		$res = $this->bean->db->query($sql);
		$profit_arr = array();
		$total = 0;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$profit_arr['$' . $row['booking_id'] . '_profit'] = format_number($row['doanhso']);
			$total += $row['doanhso'];
		}
		$profit_arr['$TOTAL_BK_PROFIT'] = format_number($total);
		return $profit_arr;
	}

	function checkApproved($month, $year)
	{
		$sql = '
			SELECT is_approved, approved_date 
			FROM ec_employee_salary 
			WHERE deleted = 0 
			AND month = "' . $month . '" 
			AND year = "' . $year . '"
			LIMIT 1
		';
		$res = $this->bean->db->query($sql);
		$row = $this->bean->db->fetchByAssoc($res);
		return array(
			'is_approved' => $row['is_approved'],
			'approved_date' => $row['approved_date']
		);
	}

	function updateApprovedStatus()
	{
		global $current_user;
		$sql = 'UPDATE ec_employee_salary 
 				SET is_approved = 1, approved_by = "' . $current_user->id . '"
 				, approved_date = "' . date('Y-m-d H:i:s') . '" 
 				WHERE deleted = 0 AND month = "' . $_REQUEST['month_search'] . '" 
 				AND year = "' . $_REQUEST['year_search'] . '"';
		$this->bean->db->query($sql);
	}

	function populateDetail($smartyobj, $user_id)
	{
		global $app_list_strings;

		$user = new User;
		$user->retrieve($user_id);

		if (!empty($user_id)) {
			$m_search = $_REQUEST['month'] . '-' . $_REQUEST['year'];
			$month = str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT) . '-' . $_REQUEST['year'];
			$today = date('Y-m-d');

			// kiểm tra đã có bảng lương hiện tại
			$is_exist = $this->checkExistSalary($_REQUEST['month'], $_REQUEST['year']);
			if ($m_search == date('m-Y') && !$is_exist)
				$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-01', strtotime('-1 month', strtotime('01-' . $month))) . '"';
			else
				$from_date_limit = 'AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "' . date('Y-m-t', strtotime('-1 month', strtotime('01-' . $month))) . '"';

			if (empty($_REQUEST['month'])) $_REQUEST['month'] = date('n');
			if (empty($_REQUEST['year'])) $_REQUEST['year'] = date('Y');

			$salary_minus_type = $salary_minus_select = $minus_select = '';
			foreach ($app_list_strings['salary_minus_list'] as $key => $value) {
				if ($key == 'Khac') $name_key = "minus_Khac";
				else $name_key = $key;
				$salary_minus_type .= 'CASE WHEN reason = "' . $key . '" THEN SUM(IFNULL(minus_amount, 0)) END AS "' . $key . '",';
				$salary_minus_select .= ' SUM(t.' . $key . ') AS "' . $name_key . '",';
				$minus_select .= ', minus.' . $name_key;
			}
			$salary_minus_type = rtrim($salary_minus_type, ",");
			$salary_minus_select = rtrim($salary_minus_select, ",");
			$salary_bonus_type = $salary_bonus_select = $bonus_select = '';
			foreach ($app_list_strings['salary_bonus_list'] as $key => $value) {
				if ($key == 'Khac') $name_key = "bonus_Khac";
				else $name_key = $key;
				$salary_bonus_type .= 'CASE WHEN reason = "' . $key . '" THEN SUM(IFNULL(bonus_amount, 0)) END AS "' . $key . '",';
				$salary_bonus_select .= ' SUM(t.' . $key . ') AS "' . $name_key . '",';
				$bonus_select .= ', bonus.' . $name_key;
			}
			$salary_bonus_type = rtrim($salary_bonus_type, ",");
			$salary_bonus_select = rtrim($salary_bonus_select, ",");


			$sql = '
			 	SELECT 
				 	  CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
					, s.basic_salary, s.efficient_wage, s.overnight, s.delivery
					, SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income 
					, SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
					, s.ot_days, s.working_days, s.no_paid_days
					, s.minus, s.social_insurance
					, s.effort, ROUND(s.sales) AS sales
					, u.id AS user_id, u.employee_type
					, s.description AS review
					, s.actual_salary, s.extra_amount
					' . $minus_select . $bonus_select . ' 
					, s.minus_income
				FROM users u 
				LEFT JOIN (
					SELECT 
						basic_salary, efficient_wage
						, gas_allowance
						, lunch_allowance, overnight, delivery
						, tele_allowance, responsible_allowance
						, seniority_allowance, extra_amount
						, other_allowance1, other_allowance2
						, social_insurance
						, assigned_user_id, description
						, actual_salary, minus_income
						, working_days, no_paid_days
						, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, minus) AS minus
						, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, effort) AS effort
						, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, sales) AS sales
						, IF(CONCAT(month, "-", year) <> "' . $m_search . '", 0, ot_days) AS ot_days
					FROM ec_employee_salary 
					WHERE deleted = 0 
					AND CONCAT(year, "-", month, "-01") = "' . $_REQUEST['year'] . "-" . $_REQUEST['month'] . "-01" . '"
					AND assigned_user_id = "' . $user_id . '"
					GROUP BY assigned_user_id
				) AS s ON s.assigned_user_id = u.id
				LEFT JOIN (
					SELECT ' . $salary_minus_select . ', t.assigned_user_id
					FROM
					(
						SELECT ' . $salary_minus_type . ', assigned_user_id
						FROM ec_salary_details 
						WHERE deleted = 0
						AND voucher_date >= "' . date("Y-m-d", strtotime("01-" . $month)) . '"
						AND voucher_date <= "' . date("Y-m-t", strtotime("01-" . $month)) . '"
						AND assigned_user_id = "' . $user_id . '"
						AND type = "minus"
						GROUP BY reason
					) AS t
					GROUP BY t.assigned_user_id
				) AS minus ON minus.assigned_user_id = u.id
				LEFT JOIN (
					SELECT ' . $salary_bonus_select . ', t.assigned_user_id
					FROM
					(
						SELECT ' . $salary_bonus_type . ', assigned_user_id
						FROM ec_salary_details 
						WHERE deleted = 0
						AND voucher_date >= "' . date("Y-m-d", strtotime("01-" . $month)) . '"
						AND voucher_date <= "' . date("Y-m-t", strtotime("01-" . $month)) . '"
						AND assigned_user_id = "' . $user_id . '"
						AND type = "bonus"
						GROUP BY reason
					) AS t
					GROUP BY t.assigned_user_id
				) AS bonus ON bonus.assigned_user_id = u.id
				WHERE u.deleted = 0 
				AND u.start_working_date IS NOT NULL
				AND u.status = "Active"
				AND u.id = "' . $user_id . '"
				GROUP BY u.id
				ORDER BY (
					CASE 
						WHEN u.title LIKE "%QuanLy%" THEN 1
						WHEN u.title LIKE "%KeToan%" THEN 2
						WHEN u.title LIKE "%Leader%" THEN 3
						WHEN u.title LIKE "%Booker%" THEN 4
					ELSE 5
					END 
				), u.start_working_date, u.first_name, u.last_name';

			// if($GLOBALS['current_user']->user_name == 'nponline') {
			// 	echo $sql; exit;
			// }

			$res = $this->bean->db->query($sql);
			$row = $this->bean->db->fetchByAssoc($res);

			// chi tiết giảm trừ
			$minus_detail = $bonus_detail = '';
			foreach ($app_list_strings['salary_minus_list'] as $key => $value) {
				if ($key == 'Khac') {
					$key = "minus_Khac";
					$value = "Khác chưa định nghĩa";
				}

				$minus_detail .= '<tr>
					<td class="salary_dt">' . $value . '</td>
					<td class="text-end">' . format_number($row[$key]) . '</td>
				</tr>';
			}

			// chi tiết thưởng thêm
			foreach ($app_list_strings['salary_bonus_list'] as $key => $value) {
				if ($key == 'Khac') {
					$key = "bonus_Khac";
					$row[$key] += $row['extra_amount'];
					$value = "Hỗ trợ khác";
				}

				$bonus_detail .= '<tr>
					<td class="salary_dt">' . $value . '</td>
					<td class="text-end">' . format_number($row[$key]) . '</td>
				</tr>';
			}


			$working_days = (float)$row['working_days'];
			// if($GLOBALS['current_user']->user_name == 'nponline') {
			// 	echo $row['sales'] + $row['overnight'] + $row['delivery'] + $row['extra_amount']; exit;
			// }
			$income = ($row['income'] + $row['allowance']) / 26 * $working_days;
			$bonus = $income + $row['sales'] + $row['extra_amount'] + $row['effort'];
			$minus = $row['minus'] + $row['minus_income'] + $row['social_insurance'] * 0.105;

			$smartyobj->assign('WORKING_DAYS', (float)$working_days);
			$smartyobj->assign('USED_LEAVE', $row['used_leave_days']);
			$smartyobj->assign('BASIC_SALARY', format_number($row['basic_salary']));
			$smartyobj->assign('EFFICIENT_WAGE', format_number($row['efficient_wage']));
			$smartyobj->assign('INCOME', format_number($row['income'] + $row['allowance']));
			$smartyobj->assign('MINUS_INCOME', format_number($row['minus_income']));
			$smartyobj->assign('NEW_INCOME', format_number($row['income'] + $row['allowance']));
			$smartyobj->assign('COM_SOCIAL_INSURANCE', format_number(0.215 * $row['social_insurance']));
			$smartyobj->assign('TEMP_SALARY', format_number($income));
			$smartyobj->assign('ALLOWANCE', format_number($row['allowance']));
			$smartyobj->assign('SALES', format_number($row['sales']));
			$smartyobj->assign('OVERNIGHT', format_number($row['overnight']));
			$smartyobj->assign('DELIVERY', format_number($row['delivery']));
			$smartyobj->assign('OVERNIGHT_DELIVERY', format_number($row['overnight'] + $row['delivery']));
			$smartyobj->assign('BONUS_DETAIL', $bonus_detail);
			$smartyobj->assign('TOTAL_BONUS', format_number($bonus));
			$smartyobj->assign('TOTAL_MINUS', format_number($minus));
			$smartyobj->assign('MINUS_DETAIL', $minus_detail);
			$smartyobj->assign('EMP_SOCAIL_INSURANCE', format_number(0.105 * $row['social_insurance']));
			$smartyobj->assign('TOTAL_INCOME', format_number($bonus - $minus));

			$smartyobj->assign('EMPLOYEE_NAME', $user->last_name . ' ' . $user->first_name);
			if ((float)$row['actual_salary'] > 0 && $row['actual_salary'] != ($bonus - $minus)) {
				$smartyobj->assign('ACTUAL_SALARY_HTML', '<tr><td>Thực nhận</td><td class="text-end">' . format_number($row['actual_salary']) . '</td></tr>');
			}
			$smartyobj->assign('REVIEW', $row['review']);
		} else {
			// $smartyobj->assign("EMPLOYEE_NAME", "Thiếu thông tin nhân viên");
			$smartyobj->assign("EMPLOYEE_NAME", "Tính năng đang bảo trì");
		}

		$smartyobj->assign('isDetail', 1);
		$smartyobj->assign('MONTH', $_REQUEST['month']);
		$smartyobj->assign('YEAR', $_REQUEST['year']);
	}

	// lưu chi tiết giảm trừ / nỗ lực
	// cập nhật tiền giảm trừ / nỗ lực trong bảng lương
	function saveAmountDetail()
	{
		global $current_user;

		for ($i = 0; $i < count($_POST['detail_amount']); $i++) {
			if (strtotime($_POST['date_detail'][$i]) != false) {
				$detail_amount = new EC_Salary_Details;
				$detail_amount->id = $_POST['amount_detail'][$i];
				$detail_amount->assigned_user_id = $_POST['assigned_user'];
				if ($_POST['type'] == 'minus')
					$detail_amount->minus_amount = unformat_number($_POST['detail_amount'][$i]);
				else {
					$detail_amount->bonus_amount = unformat_number($_POST['detail_amount'][$i]);
				}
				$detail_amount->description 	= $_POST['detail_description'][$i];
				$detail_amount->reason 		= $_POST['reason'][$i];
				$detail_amount->type 		= $_POST['type'];
				$detail_amount->voucher_date 	= $_POST['date_detail'][$i];
				$detail_amount->deleted 		= $_POST['detail_deleted'][$i];

				$monthyear[] = date('Y-m-01', strtotime($_POST['date_detail'][$i]));

				if ($detail_amount->deleted == 0) {
					if (date('Y-m', strtotime($_POST['date_detail'][$i])) == $_POST['year'] . '-' . str_pad($_POST['month'], 2, 0, STR_PAD_LEFT)) {
					}
					$detail_amount->save();
				} else {
					$detail_amount->mark_deleted($detail_amount->id);
				}
			}
		}

		$monthyear = array_unique($monthyear);

		switch ($_POST['type']) {
			case 'minus':
				$col_name = 'minus';
				$col_dt_name = 'minus_amount';
				$other_col = '';
				break;
			case 'bonus':
				$col_name = 'effort';
				$col_dt_name = 'bonus_amount';
				// $other_col = ' + overnight + delivery';
				$other_col = ' + delivery';
				break;
			default:
				break;
		}

		// cập nhật tổng giảm trừ / nỗ lực của nhân viên
		for ($k = 0; $k <  count($monthyear); $k++) {
			$sql_upt = 'UPDATE ec_employee_salary 
		 				SET ' . $col_name . ' = (
		 					SELECT SUM(IFNULL(' . $col_dt_name . ', 0)) 
		 					FROM ec_salary_details 
		 					WHERE deleted = 0 
		 					AND voucher_date >= "' . date('Y-m-01', strtotime($monthyear[$k])) . '"
		 					AND voucher_date <= "' . date('Y-m-t', strtotime($monthyear[$k])) . '"
		 					AND assigned_user_id = "' . $_POST['assigned_user'] . '"
		 				)' . $other_col . '
		 				WHERE is_approved = 0 
		 				AND assigned_user_id = "' . $_POST['assigned_user'] . '"
		 				AND month = ' . date('n', strtotime($monthyear[$k])) . ' 
		 				AND year = ' . date('Y', strtotime($monthyear[$k]));

			// if($current_user->user_name == 'nponline') {
			// 	echo $sql_upt; exit;
			// }

			$this->bean->db->query($sql_upt);
		}

		// redirect
		header("Location: index.php?module=EC_Employee_Salary&action=employeesalary&edit_btn&month_search=" . $_POST['month'] . "&year_search=" . $_POST['year']);
		exit;
	}

	function saveDetail()
	{
		$sql = 'UPDATE ec_employee_salary 
 				SET description = "' . $_POST['review'] . '"
 				  , actual_salary = "' . unformat_number($_POST['actual_salary']) . '"
 				WHERE deleted = 0 AND assigned_user_id = "' . $_POST['user_id'] . '" 
 				AND month = "' . $_POST['month'] . '" AND year = "' . $_POST['year'] . '"';
		$this->bean->db->query($sql);

		// if($GLOBALS['current_user']->user_name == 'nponline') {
		// 	echo $sql;
		// }
		// redirect
		header("Location: index.php?module=EC_Employee_Salary&action=employeesalary&for=showdetail&user_id=" . $_POST['user_id'] . "&month=" . $_POST['month'] . "&year=" . $_POST['year']);
		exit;
	}

	function exportExcel($month, $year)
	{
		$html = $this->calculateSalary($month, $year, 1);
		ob_clean();
		header('Pragma: cache');
		$excelTpl = file_get_contents('modules/EC_Employee_Salary/tpls/excel_employee_salary.tpl');
		$excelTpl = str_replace(array('{$MONTH}', '{$YEAR}', '{$DATA}'), array($month, $year, $html), $excelTpl);
		$excelTpl = chr(255) . chr(254) . mb_convert_encoding($excelTpl, 'UTF-16LE', 'UTF-8');
		header('Content-type: application/x-msdownload');
		header('Content-disposition: xls; filename=BANGLUONGTHANG' . $month . 'NAM' . $year . '_' . time() . '.xls; size=' . strlen($excelTpl));
		echo $excelTpl;
		exit;
	}

	function generateSearchMonthHTML()
	{
		$curr_month = date('m-Y');
		$prev_month = date('m-Y', strtotime('-1 month'));
		$html = '<table id="month_list" cellpadding="0" cellspacing="0"><tbody>';
		$html .= '<tr><td>' . $this->generateMonthYearFormat($prev_month);
		$html .= '<td>' . $this->generateMonthYearFormat($curr_month) . '</td>';
		$html .= '<td><span class="excerpt_salary">Quay lại bảng lương hiện tại</span></td></tr>';
		$date = date('Y-m-d', strtotime('-2 month'));
		// do {
		// 	$html .= '<tr>
		// 		<td>'.$this->generateMonthYearFormat(date('m-Y', strtotime($date))).'</td>
		// 		<td>'.$this->generateMonthYearFormat(date('m-Y', strtotime($date. ' -1 month'))).'</td>
		// 		<td>'.(strtotime($date, '-2 month') < strtotime('01-01-2020')?$this->generateMonthYearFormat(date('m-Y', strtotime($date. ' -2 month'))):'Những tháng trước').'</td>
		// 	</tr>';
		// 	$date = date('Y-m-d', strtotime($date, '-3 month'));
		// } while(strtotime($date) >= strtotime('01-01-2020'));
		$html .= '</tbody></table>';
		// echo $html;
		return $html;
	}

	function generateMonthYearFormat($monthyear)
	{
		if (strpos($monthyear, '-') !== false) {
			$month_year_arr = explode('-', $monthyear);
			return 'Tháng ' . $month_year_arr[0] . ' / ' . $month_year_arr[1];
		}
		return false;
	}

	// kiểm tra đã có bảng lương tháng hiện tại
	function checkExistSalary($month, $year)
	{
		$sql_exists = 'SELECT IF(COUNT(id) > 0, 1, 0) FROM ec_employee_salary 
 					   WHERE deleted = 0 AND month = ' . $month . ' 
 					   AND year = ' . $year;
		return $this->bean->db->query($sql_exists);
	}

	function updateWorkingDays()
	{
		global $db, $current_user;

		if (isset($_REQUEST['month_search'])) {
			$month = $_REQUEST['month_search'];
		} else $month = date('n');

		// $today 				= date('Y-'.$month.'-t'); // là ngày cuối tháng

		/**
		 * Bởi vì qua tháng sau mới cập nhật ngày công của tháng trước. Nên today phải -1 month
		 */
		$today 				= date('Y-'.$month.'-t', strtotime('-1 month'));
		$start_date 		= date('Y-m-01', strtotime($today));
		$end_date 			= date('Y-m-t', strtotime($today));
		$month 				= date('m-Y', strtotime($today));
		$month_before 		= date('m-Y', strtotime('-1 month', strtotime($today)));
		$end_date_before 	= date('Y-m-t', strtotime('-1 month', strtotime($start_date)));
		
		// Ngày duyệt lương của tháng trước
		$sql = '
			 SELECT DATE_FORMAT(approved_date, "%Y-%m-%d") AS from_date 
			 FROM ec_employee_salary 
			 WHERE deleted = 0 
			 AND month = "' . date('n', strtotime('01-' . $month . ' -1 month')) . '" 
			 AND year = "' . date('Y', strtotime('01-' . $month . ' -1 month')) . '"
			 LIMIT 1
		 ';

		$res 			= $db->query($sql);
		$row_fdate 		= $db->fetchByAssoc($res);
		$from_date_q 	= '';

		if (strtotime($row_fdate['from_date']) < strtotime('01-' . $month) && strtotime($row_fdate['from_date']) != false) {
			$from_date_s = $row_fdate['from_date'];
			$from_date_q = ' AND from_date >= "' . $row_fdate['from_date'] . '"';
			$first_sunday_lastm = 7 - date('N', strtotime($from_date_s)) + 1;

			for ($i = $first_sunday_lastm; $i <= date('t', strtotime('-1 month')); $i += 7) {
				if ($i > (int)date('d', strtotime($from_date_s)))
					$sundays_lastm_left[] = $i;
			}
		} else {
			$from_date_s = $start_date;
		}

		$sql = 'SELECT l.used_leave_days_curr AS used_leave_days
					, IFNULL(l.no_paid_days, 0) AS no_paid_days
					, IFNULL(l.absence_days, 0) AS leave_days
				   , ot.working_hour AS overtime
				   , u.id AS user_id, u.his_stt
				   , u.start_working_date
				 , u.his_date_start
				   , u.his_date_end
				 , CONCAT(u.last_name, " ", u.first_name) AS full_name
				   , (
						  SELECT SUM(d.working_hour) / 8
						  FROM ec_workingovertimedetails d
						  INNER JOIN ec_workingovertimes t
						  ON t.id = d.ec_workingovertimes_id_c
						  AND t.status = 2
						  WHERE d.deleted = 0
						  AND d.assigned_user_id = u.id
						  AND DATE_FORMAT(t.bonus_month, "%m-%Y") = "' . $month . '" 
					  ) AS bonus_work_days
			 FROM (
					 SELECT usr.id, usr.start_working_date
						  , usr.last_name, usr.first_name
						  , usr.title, usr.deleted, his.with_salary
						  , his.description AS history_desc
						  , his.status AS his_stt
						  , his.date_start AS his_date_start 
						 , his.date_end AS his_date_end
					 FROM users usr
					 INNER JOIN ec_workhistory his
					 ON his.assigned_user_id = usr.id
					 AND his.deleted = 0
					 AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $start_date . '"
					 AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
					 WHERE usr.deleted = 0
				 ) AS u
			 LEFT JOIN (
				 SELECT SUM(IFNULL(used_leave_days_curr_m, 0)) AS used_leave_days
				  , assigned_user_id
				  , SUM(
					IF( from_date > "' . $today . '"
						,  0
						,  CASE WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
						AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" 
						THEN IFNULL( absence_days, 0 )
						WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
						THEN DATEDIFF("' . $end_date . '", from_date) + 1
						ELSE DATEDIFF(to_date, "' . $start_date . '") + 1 END 
					)
				  ) AS absence_days
				  , SUM(
					 IF( from_date > "' . $today . '" OR no_paid_days = 0
						,  0
						,  CASE 
								-- trong thang, hom nay > ngay ket thuc nghi
								WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND (to_date <= "' . $today . '"  OR DATEDIFF("' . $today . '", from_date) >= no_paid_days)
								THEN IFNULL(no_paid_days, 0)
 
								-- trong thang, hom nay < ngay ket thuc nghi, co chon ngay nghi 0.5 buoi 
								WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND to_date > "' . $today . '" AND part_date <= "' . $today . '"
								THEN DATEDIFF("' . $today . '", from_date) + 0.5
 
								-- trong thang, hom nay < ngay ket thuc nghi, ko chon ngay nghi 0.5 buoi 
								WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" AND DATE_FORMAT( to_date, "%m-%Y" ) = "' . $month . '" AND to_date > "' . $today . '"
								THEN DATEDIFF("' . $today . '", from_date) + 1
 
								-- khac thang
								WHEN DATE_FORMAT( from_date, "%m-%Y" ) = "' . $month . '" 
								THEN DATEDIFF("' . $end_date . '", from_date) + 1
								ELSE DATEDIFF("' . $end_date . '", "' . $start_date . '") + 1 END
					) 
				  ) AS no_paid_days
				  , (
					 SELECT SUM(IFNULL(used_leave_days_next_m, 0)) 
					 FROM ec_leaveabsences
					 WHERE deleted = 0
					 AND status = 2 
					 AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month_before . '" 
					 OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month_before . '")
					 AND assigned_user_id = a.assigned_user_id
					 GROUP BY assigned_user_id
				 ) AS used_leave_days_curr
				 FROM ec_leaveabsences a
				 WHERE deleted = 0
				 AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
				 OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '")' . $from_date_q . '
				 GROUP BY assigned_user_id
			 ) AS l ON l.assigned_user_id = u.id
			 LEFT JOIN (
				 SELECT SUM(working_hour) AS working_hour, assigned_user_id 
				 FROM ec_workingovertimedetails
				 WHERE deleted = 0 AND status = 2 
				 AND register_date <= "' . $today . '"
				 AND register_date >= "' . $from_date_s . '"
				 GROUP BY assigned_user_id
			 ) AS ot ON ot.assigned_user_id = u.id
			 WHERE u.deleted = 0 
			 AND u.start_working_date IS NOT NULL
			 GROUP BY u.id
			 ORDER BY (
				 CASE 
					 WHEN u.title LIKE "%QuanLy%" THEN 1
					 WHEN u.title LIKE "%KeToan%" THEN 2
					 WHEN u.title LIKE "%Leader%" THEN 3
					 WHEN u.title LIKE "%Booker%" THEN 4
				 ELSE 5
				 END 
			 ), u.start_working_date';

		// if($current_user->user_name == 'hungnh') {
		// 	pr($sql);
		// }

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			if (
				($row['his_stt'] != 'InActive' && $row['his_stt'] != 'Absent')
				|| (($row['his_stt'] == 'InActive' || $row['his_stt'] == 'Absent') && date('m-Y', strtotime($row['his_date_start'])) == $month)
			) {

				// tính ngày bắt đầu
				if ($month == date('m-Y')) {
					$tdate = date('j'); //Ngày hiện tại trong tháng - without leading zeros

					if (date('m-Y') == date('m-Y', strtotime($row['his_date_start'])))
						$working_days = date('j') - date('j', strtotime($row['his_date_start'])) + 1;
					else
						$working_days = date('j', strtotime($today));
				} else {
					$tdate = date('j', strtotime($end_date));
					if (strtotime($end_date) < strtotime($row['his_date_start']))
						$working_days = $tdate = 0;
					else if ($row['his_date_start'] && strtotime($start_date) < strtotime($row['his_date_start']) && $row['his_date_end'] && strtotime($end_date) >= strtotime($row['his_date_end']))
						$working_days = date('j', strtotime($row['his_date_end'])) - date('j', strtotime($row['his_date_start'])) + 1;
					else
						$working_days = date('t', strtotime($start_date));
				}

				// Tính những ngày nghỉ không lương
				$no_paid_days = 0;
				if ($row['user_id']) {
					$sql1 = '
						 SELECT *
						 FROM ec_leaveabsences
						 WHERE deleted = 0
						 AND status = 2 
						 AND (
							 DATE_FORMAT(from_date, "%m-%Y") = "' . $month . '" 
							 OR DATE_FORMAT(to_date, "%m-%Y") = "' . $month . '"
						 )
						 AND assigned_user_id = "' . $row['user_id'] . '"
					 ';
					$res1 = $db->query($sql1);

					while ($row1 = $db->fetchByAssoc($res1)) {
						if ((float)$row1['no_paid_days'] > 0) {
							// xin trong tháng
							if (
								strtotime($row1['from_date']) >= strtotime($start_date)
								&& strtotime($row1['to_date']) <= strtotime($end_date)
							) {
								// nếu ngày hiện tại chưa tới ngày kết thúc nghỉ
								if (strtotime($row1['to_date']) > strtotime($today)) {
									if (strtotime($today) > strtotime($row1['from_date'])) {
										// đếm số ngày nghỉ cho đến hiện tại
										$leaves = myCalculateDayBetweenDates($row1['from_date'], $today) + 1;
										// nếu số ngày nghỉ > nghỉ không lương
										if ($leaves - $row1['no_paid_days'] >= 0) {
											$no_paid_days += $row1['no_paid_days'];
											// ngược lại
										} else {
											// kiểm tra có CN
											$tt_sun = $this->calSundaysBetweenTwoDays($row1['from_date'], $today);
											$no_paid_days = $leaves - $tt_sun;
										}
									} else $no_paid_days = 0;
								} else {
									$no_paid_days += $row1['no_paid_days'];
								}
								// xin khác tháng
							} else {
								// ngày bắt đầu thuộc tháng trước
								if (strtotime($row1['from_date']) < strtotime($start_date)) {
									$leaves = myCalculateDayBetweenDates($row1['from_date'], $end_date_before) + 1;
									$tt_sun = $this->calSundaysBetweenTwoDays($row1['from_date'], $end_date_before);
									if (($leaves - $tt_sun) < $row1['no_paid_days']) {
										$row1['no_paid_days'] -= ($leaves - $tt_sun);
									}
									if (strtotime($row1['to_date']) <= strtotime($today)) {
										$leaves2 = myCalculateDayBetweenDates($start_date, $today) + 1;
										$tt_sun2 = $this->calSundaysBetweenTwoDays($start_date, $today);
										if ($leaves2 - $tt_sun2 < $row1['no_paid_days']) {
											$no_paid_days += $leaves2 - $tt_sun2;
										} else {
											$no_paid_days += $row1['no_paid_days'];
										}
									} else {
										$no_paid_days += $row1['no_paid_days'];
									}
								} else {
									$leaves = myCalculateDayBetweenDates($row1['from_date'], $end_date) + 1;
									if ($leaves < $row['no_paid_days']) {
										$no_paid_days += $leaves;
									}
								}
							}
						}
					}
				}

				// Tính các ngày chủ nhật
				$sundays = array();
				$first_sunday = 7 - date('N', strtotime($start_date)) + 1;

				// for ($i = $first_sunday; $i <= $tdate; $i += 7) {
				// 	if (strtotime($i . '-' . $month) >= strtotime($row['his_date_start']) && strtotime($i . '-' . $month) <= strtotime($row['his_date_end']))
				// 		$sundays[] = $i;
				// }
				for ($i = $first_sunday; $i <= $tdate; $i += 7) {
					if (strtotime($i . '-' . $month) >= strtotime($row['start_working_date']))
						$sundays[] = $i;
				}
				$exclude_days = array_unique(array_merge($sundays), 0);

				// tính số ngày công
				if (strtotime($from_date_s) < strtotime($start_date)) {
					$bonus_days = date('t', strtotime($from_date_s)) - date('d', strtotime($from_date_s)) - count($sundays_lastm_left);
					$working_days += $bonus_days;
				}

				$working_days = $working_days - count($exclude_days) - (float)$no_paid_days + (int)($row['overtime'] / 8) + (int)$row['bonus_work_days'];
				if ($working_days <= 0) $working_days = 0;

				$sql2 = '
					 UPDATE ec_employee_salary 
					 SET 
						 working_days = ' . $working_days . '
							, ot_days = ' . ($row['overtime'] / 8) . ' 
							, no_paid_days = ' . (float)$row['no_paid_days'] . '
					 WHERE is_approved = 0 
					 AND assigned_user_id = "' . $row['user_id'] . '"
					 AND month = "' . date('n', strtotime($today)) . '" 
					 AND year = "' . date('Y', strtotime($today)) . '"
					 AND deleted = 0';
				$db->query($sql2);

				// những nhân viên đã nghỉ hoặc tạm vắng thì không tính công
			} else {
				$sql2 = 'UPDATE ec_employee_salary 
						  SET working_days = 0
							, ot_days = 0 
							, no_paid_days = 0
						  WHERE is_approved = 0 
						  AND assigned_user_id = "' . $row['user_id'] . '"
						  AND month="' . date('n', strtotime($today)) . '" 
						  AND year = "' . date('Y', strtotime($today)) . '" 
						  AND deleted = 0';
				$db->query($sql2);
			}
		}

		return true;
	}
}
