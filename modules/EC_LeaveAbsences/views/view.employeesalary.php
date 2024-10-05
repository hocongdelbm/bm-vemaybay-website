<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
class Viewemployeesalary extends SugarView {
 	function display() {
 		global $current_user;

 		$smarty = new Sugar_Smarty();
		
 		// kiểm tra user xem lương chi tiết
		if(is_admin($current_user) || $current_user->title == 'QuanLy') {
			$user_id = $_REQUEST['user_id'];
		} else {
			$user_id = $current_user->id;
		}

 		if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'effortdetail') {
 			$this->populateEffortDetail($smarty, $user_id);
 		} else if(isset($_POST['saveDetail'])) {
 			$this->saveDetail();
 		} else if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'showdetail') {
 			$this->populateDetail($smarty, $user_id);
 		} else {
	 		if(isset($_POST['for']) && $_POST['for'] == 'Save') {
	 			if(isset($_POST['commission_from_amt']))
	 				$this->saveCommission();
	 			$this->saveSalaryInf();
	 		}
	 		if(isset($_POST['for']) && $_POST['for'] == 'SaveMinus') {
	 			$this->saveMinus();
	 		}
	 		if(isset($_POST['approved_btn'])) {
	 			$this->updateApprovedStatus();
	 		}
	 		$this->populateButtons($smarty);
	 		$this->populateContent($smarty);
	 	}
 		$smarty->display('modules/EC_LeaveAbsences/tpls/view_employeesalary.tpl');	
 	}

 	function populateButtons($smartyobj) {
 		global $current_user;

		// nút chỉnh sửa
		if(empty($_POST['month_search'])) $_POST['month_search'] = date('n', strtotime("-1 month"));
		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y', strtotime("-1 month"));
		
		$is_approved = $this->checkApproved($_POST['month_search'], $_POST['year_search']);
 		if((is_admin($current_user) || $current_user->title == 'QuanLy')) {
 			if(!$is_approved) {
	 			if(!isset($_REQUEST['edit_btn'])) {
		 			$edit_btn = '<input type="submit" name="edit_btn" value="Chỉnh sửa">';
		 			$smartyobj->assign('EDIT_BTN', $edit_btn);
		 		} else {
		 			$save_btn = '<input type="submit" id="save_btn" name="save_btn" value="Lưu">';
		 			$smartyobj->assign('SAVE_BTN', $save_btn);
		 		}

		 		if(is_admin($current_user)) {
		 			$approved_btn = '<input type="submit" name="approved_btn" value="Duyệt">';
		 			$smartyobj->assign('APPROVED_BTN', $approved_btn);
		 		}
		 	}
 		} 
 	}

 	function populateContent($smartyobj) {
 		global $current_user, $app_list_strings;
 		
 		for($m = 1; $m <= 12; $m++) {
 			$m_arr[$m] = $m;
 		}
 		if(empty($_POST['month_search'])) $_POST['month_search'] = date('n', strtotime("-1 month"));
 		$smartyobj->assign('MONTH_OPTION', get_select_options_with_id($m_arr, $_POST['month_search']));
 		$smartyobj->assign('MONTH', $_POST['month_search']);

 		for($y = (date('Y') - 1); $y <= (date('Y') + 3); $y++) {
 			$y_arr[$y] = $y;
 		}
 		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y', strtotime("-1 month"));
 		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($y_arr, $_POST['year_search']));
 		$smartyobj->assign('YEAR', $_POST['year_search']);

 		$is_approved = $this->checkApproved($_POST['month_search'], $_POST['year_search']);
 		if((!isset($_REQUEST['edit_btn']) || $is_approved) || (!is_admin($current_user) && $current_user->title != 'QuanLy') && isset($_REQUEST['edit_btn'])) {
			$data = $this->calculateSalary($_POST['month_search'], $_POST['year_search']);
			$smartyobj->assign('SALARY', $data);
			$smartyobj->assign('isEffortDetail', 0);
			$smartyobj->assign('isDetail', 0);
		} else {
			$smartyobj->assign('isEdit', 1);
			$data = $this->calculateSalaryEdit($_POST['month_search'], $_POST['year_search']);
			$smartyobj->assign('SALARY_EDIT', $data);
			$smartyobj->assign('REASON_OPTION', get_select_options_with_id($app_list_strings['salary_minus_list'], ''));

			// bảng tiền phụ cấp
			$data2 = $this->calculateCommission($_POST['month_search'], $_POST['year_search']);
			$smartyobj->assign('COMMISSION', $data2['html']);
			$smartyobj->assign('ROW_COUNT', $data2['count']);

			// bảng tiền phụ cấp
			$data3 = $this->calculateAllowance($_POST['month_search'], $_POST['year_search']);
			$smartyobj->assign('EMPLOYEE_ALLOWANCE', $data3);

			// bảng tiền bảo hiểm
			$data4 = $this->calculateInsurrance($_POST['month_search'], $_POST['year_search']);
			$smartyobj->assign('EMPLOYEE_INSURANCE', $data4);
			$smartyobj->assign('ROW_COUNT_MINUS', 1);
			$smartyobj->assign('CREATED_USER', $current_user->user_name);
		}
 	}

 	function calculateSalary($month_search, $year_search) {
 		global $current_user;

 		if($current_user->title != 'QuanLy' && !is_admin($current_user)) {
 			$assigned_user_id = ' AND assigned_user_id = "' . $current_user->id . '"';
 		}
 		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
 		$today = date('Y-m-d');
 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income 
 					 , SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
 					 , l.no_paid_days AS no_paid_days
 					 , IFNULL(l.absence_days, 0) AS leave_days
 					 , ot.working_hour AS overtime
 					 , s.minus, s.social_insurance
 					 , s.effort, ROUND(s.sales) AS sales
 					 , u.id AS user_id, u.employee_type
  				FROM users u 
 				LEFT JOIN (
 					SELECT SUM(IFNULL(no_paid_days, 0)) AS no_paid_days
 						 , SUM(IFNULL(absence_days, 0)) AS absence_days
 						 , assigned_user_id
 					FROM ec_leaveabsences 
 					WHERE deleted = 0
 					AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "'.$month.'" 
 					OR DATE_FORMAT(to_date, "%m-%Y") = "'.$month.'") 
 					AND (to_date <= "'.$today.'"
 					OR from_date <= "'.$today.'")
 					'.$assigned_user_id .'
 					GROUP BY assigned_user_id
 				) AS l ON l.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT SUM(working_hour) AS working_hour, assigned_user_id 
 					FROM ec_workingovertimedetails
 					WHERE deleted = 0 AND status = 2 
 					AND register_date <= "'.$today.'"
 					AND DATE_FORMAT(register_date, "%m-%Y") = "'.$month.'"
 					'.$assigned_user_id .'
 					GROUP BY assigned_user_id
 				) AS ot ON ot.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT basic_salary, efficient_wage, gas_allowance, lunch_allowance
 						 , tele_allowance, responsible_allowance, seniority_allowance
 						 , other_allowance1, other_allowance2, social_insurance
 						 , assigned_user_id, effort, minus, sales
 					FROM ec_employee_salary 
 					WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered LIMIT 1
 					)
 					'.$assigned_user_id .'
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.start_working_date IS NOT NULL
 				AND u.status = "Active"
 				'.str_replace('assigned_user_id', 'u.id', $assigned_user_id) .'
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
 		// if($current_user->user_name == 'nponline') {
 		// 	echo $sql; exit;
 		// }
 		$res = $this->bean->db->query($sql);
 		$i = 1;
 		$total_income = $total_allowance = $total_working_days = $total_overtime = 0;
 		$total_minus = $total_effort = $total_sale = $total_goverment = $total_amount = 0;
 		$total_leave_days = 0;
 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			// tính cột nhà nước = BHXH(8%) + BHYT(1.5%) + BHTN(1%)
 			$government = $row['social_insurance'] * 0.08 + $row['social_insurance'] * 0.015 + $row['social_insurance'] * 0.01;

 			// tính số ngày công	
 			if(date('m') == $month_search) {
 				$working_days = date('j');
 			} else {
 				$working_days = date('t', strtotime('01-'.$month));
 			}

 			$working_days = $working_days - count($this->getExcludeDays($month)) - $row['no_paid_days'] + ($row['overtime'] / 8);
 			if($row['employee_type'] == 3) $working_days = 0;

 			// tính cột thực lãnh 
 			if($row['employee_type'] != 3) {
 				$total = ($row['income'] + $row['allowance']) / 26 * $working_days - $row['minus'] + $row['effort'] + $row['sales'] - $government;
 			} else {
 				$total = ($row['income'] + $row['allowance']);
 			}

			$html = '';
 			$html .= '<tr>
 						<td class="center">'.$i++.'</td>
 						<td><a href="index.php?module=EC_LeaveAbsences&action=employeesalary&for=showdetail&user_id='.$row['user_id'].'&month='.$month_search.'&year='.$year_search.'" target="_blank">'.$row['full_name'].'</a></td>
 						<td class="right">'.format_number($row['income']).'</td>
 						<td class="right">'.format_number($row['allowance']).'</td>
 						<td class="right">'.$working_days.'</td>
 						<td class="right">'.$row['leave_days'].'</td>
 						<td class="right">'.($row['overtime'] / 8).'</td>
 						<td class="right"><span class="show_detail" employee_name="'.$row['full_name'].'" employee="'.$row['user_id'].'" minus="'.format_number($row['minus']).'">'.format_number($row['minus']).'</span></td>
 						<td class="right"><a href="index.php?module=EC_LeaveAbsences&action=employeesalary&for=effortdetail&user_id='.$row['user_id'].'&month='.$month_search.'&year='.$year_search.'" target="_blank">'.format_number($row['effort']).'</a></td>
 						<td class="right">'.format_number($row['sales']).'</td>
 						<td class="right">'.format_number($government).'</td>
 						<td class="right">'.format_number($total).'</td>
 					</tr>';
 			$total_income += $row['income'];
 			$total_allowance += $row['allowance'];
 			$total_working_days += $working_days;
 			$total_leave_days += $row['leave_days'];
 			$total_overtime += $row['overtime'] / 8;
 			$total_minus += $row['minus'];
 			$total_effort += $row['effort'];
 			$total_sale += $row['sales'];
 			$total_goverment += $government;
 			$total_amount += $total;
 		}

 		$html .= '<tr>
 			<td></td>
 			<td><b>Tổng</b></td>
 			<td class="right"><b>'.format_number($total_income).'</b></td>
 			<td class="right"><b>'.format_number($total_allowance).'</b></td>
 			<td class="right"><b>'.$total_working_days.'</b></td>
 			<td class="right"><b>'.$total_leave_days.'</b></td>
 			<td class="right"><b>'.$total_overtime.'</b></td>
 			<td class="right"><b>'.format_number($total_minus).'</b></td>
 			<td class="right"><b>'.format_number($total_effort).'</b></td>
 			<td class="right"><b>'.format_number($total_sale).'</b></td>
 			<td class="right"><b>'.format_number($total_goverment).'</b></td>
 			<td class="right"><b>'.format_number($total_amount).'</b></td>
 		</tr>';

 		return $html;
 	}

 	function calculateSalaryEdit($month_search, $year_search) {
 		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
 		$today = date('d-m-Y');
 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , SUM(IFNULL(s.basic_salary, 0)) AS basic_salary
 					 , SUM(IFNULL(s.efficient_wage, 0)) AS efficient_wage
 					 , SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income
 					 , SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
 					 , l.no_paid_days AS no_paid_days
 					 , l.absence_days AS leave_days
 					 , ot.working_hour AS overtime
 					 , u.id AS user_id
 					 , s.id AS salary_id
 					 , CONCAT(s.month, "-", s.year) AS salary_start_month
 					 , s.social_insurance
 					 , s.minus, s.effort, s.sales
  				FROM users u 
 				LEFT JOIN (
 					SELECT SUM(no_paid_days) AS no_paid_days
 						 , SUM(absence_days) AS absence_days
 						 , assigned_user_id
 					FROM ec_leaveabsences 
 					WHERE deleted = 0
 					AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "'.$month.'" 
 					OR DATE_FORMAT(to_date, "%m-%Y") = "'.$month.'") 
 					AND (DATE_FORMAT(to_date, "%d-%m-%Y") <= "'.$today.'"
 					OR DATE_FORMAT(from_date, "%d-%m-%Y") <= "'.$today.'")
 					GROUP BY assigned_user_id
 				) AS l ON l.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT SUM(working_hour) AS working_hour, assigned_user_id 
 					FROM ec_workingovertimedetails
 					WHERE deleted = 0 AND status = 2 
 					AND DATE_FORMAT(register_date, "%d-%m-%Y") <= "'.$today.'"
 					AND DATE_FORMAT(register_date, "%m-%Y") = "'.$month.'"
 					GROUP BY assigned_user_id
 				) AS ot ON ot.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT * FROM ec_employee_salary WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered LIMIT 1
 					)
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.start_working_date IS NOT NULL
 				AND u.status = "Active"
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
 		$res = $this->bean->db->query($sql);
 		$i = 1;
 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			// tính số ngày công	
 			if(date('m') == $month_search) {
 				$working_days = date('j');
 			} else {
 				$working_days = date('t', strtotime('01-'.$month));
 			}

 			$working_days = $working_days - count($this->getExcludeDays($month)) - $row['no_paid_days'] + ($row['overtime'] / 8);

 			if($row['salary_start_month'] == $month_search."-".$year_search) {
 				$salary_detail = $row['salary_id'];
 			} else {
 				$salary_detail = '';
 			}

 			$government = $row['social_insurance']*0.08 + $row['social_insurance']*0.015 + $row['social_insurance']*0.01;
 			$total = ($row['income'] + $row['allowance']) / 26 * $working_days - $row['minus'] + $row['effort'] + $row['sales'] - $government;

			$html = '';
 			$html .= '<tr>
 						<td class="center">'.$i++.'</td>
 						<td>'.$row['full_name'].'
 							<input type="hidden" name="full_name[]" value="'.$row['full_name'].'">
 							<input type="hidden" name="assigned_user_id[]" value="'.$row['user_id'].'">
 							<input type="hidden" name="salary_detail[]" value="'.$salary_detail.'"></td>
 						<td class="right"><input name="basic_salary[]" id="basic_salary'.($i-1).'" class="allow-number-only" type="text" value="'.format_number($row['basic_salary']).'" oninput="calculateIncome('.($i-1).')"></td>
 						<td class="right"><input name="efficient_wage[]" id="efficient_wage'.($i-1).'" class="allow-number-only" type="text" value="'.format_number($row['efficient_wage']).'" oninput="calculateIncome('.($i-1).')"></td>
 						<td class="right" id="salary'.($i-1).'">'.format_number($row['income']).'</td>
 						<td class="right" id="allowance'.($i-1).'">'.format_number($row['allowance']).'</td>
 						<td class="right"><span class="show_detail" employee_name="'.$row['full_name'].'" employee="'.$row['user_id'].'">'.format_number($row['minus']).'</span></td>
 						<td class="right">'.format_number($row['effort']).'</td>
 						<td class="right">'.format_number($row['sales']).'</td>
 						<td class="right" id="government'.($i-1).'">'.format_number($government).'</td>
 						<td class="right" id="total'.($i-1).'">'.format_number($total).'</td>
 					</tr>';
 		}

 		return $html;
 	}

 	// lấy tất cả các ngày chủ nhật trong tháng và các ngày lễ
 	// chỉ tính tới ngày hiện tại
 	function getExcludeDays($month_year) {
 		$month = date('m', strtotime('01-'.$month_year));

 		// các ngày lễ
 		$holidays = array(
 			'01' => array(1),
 			'default' => array(),
 		);

 		// tính các ngày chủ nhật
 		$fdate = '01-'.$month_year;
 		if($month_year == date('m-Y')) {
 			$tdate = date('d');
 		} else {
 			$tdate = date('t', strtotime('01-'.$month_year));
 		}

 		$first_sunday = 7 - date('N', strtotime($fdate)) + 1; 

 		for($i = $first_sunday; $i <= $tdate; $i+=7) {
 			$sundays[] = $i;
 		}

 		$exclude_days = array_unique(array_merge($sundays, (array_key_exists($month, $holidays)?$holidays[$month]:$holidays['default'])), SORT_REGULAR);

 		
 		return $exclude_days;
 	}

 	function calculateCommission($month_search, $year_search) {
 		$month = $month_search . '-' . $year_search;
 		$sql = 'SELECT *, CONCAT(month, "-", year) AS commission_start_month 
 				FROM ec_commission 
 				WHERE deleted = 0 AND DATE_FORMAT(date_entered, "%Y-%m-%d") = ( 
	 				SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_commission 
	 				WHERE deleted = 0 AND CONCAT(year, "-", month, "-01") <= "'.date('Y-m-01').'"
	 				ORDER BY date_entered DESC
	 				LIMIT 1
	 			)';
	 	// echo $sql;
	 	$res = $this->bean->db->query($sql);
	 	$i = 0;
	 	while($row = $this->bean->db->fetchByAssoc($res)) { 
	 		if($row['commission_start_month'] == $month) {
 				$commission_detail = $row['id'];
 			} else {
 				$commission_detail = '';
 			}
			 $html = '';
	 		$html .= '<tr id="commission_line'.++$i.'">
	 			<td id="commission_order'.$i.'" class="center">'.$i.'</td>
	 			<td><input type="text" class="allow-number-only" name="commission_from_amt[]" value="'.format_number($row['from_value']).'"></td>
	 			<td><input type="text" class="allow-number-only" name="commission_to_amt[]" value="'.format_number($row['to_value']).'"></td>
	 			<td><input type="text" class="allow-number-only" name="commission_percentage[]" value="'.format_number($row['percentage']).'"></td>
	 			<td class="center">
					<button title="Xóa" type="button" onclick="markRowDeleted(\'commission_line\', \'commission_deleted\', \'commission_order\', '.$i.')" style="background:transparent; border:0;"><img src="custom/themes/default/images/delete_16x16.png"></button>
					<input type="hidden" name="commission_deleted[]" id="commission_deleted'.$i.'" value="0">
					<input type="hidden" name="commission_detail[]" id="commission_detail'.$i.'" value="'.$commission_detail.'">
					<input type="hidden" name="commission_order[]" value="'.$row['name'].'">
				</td>
	 		</tr>';
	 	};


	 	return array('html' => $html, 'count' => $i+=1);
 	}	

 	function calculateAllowance($month_search, $year_search) {
 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , s.gas_allowance, s.lunch_allowance, s.tele_allowance
 					 , s.responsible_allowance, s.seniority_allowance, s.other_allowance1
 					 , s.other_allowance2
 				FROM users u 
 				LEFT JOIN (
 					SELECT * FROM ec_employee_salary WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered LIMIT 1
 					)
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.start_working_date IS NOT NULL AND LENGTH(u.start_working_date) > 0
 				AND u.status = "Active"
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
 		// echo $sql;
 		$res = $this->bean->db->query($sql);
 		$i = 0;
 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			$total_allowance = (int)$row['gas_allowance'] + (int)$row['lunch_allowance'] + (int)$row['tele_allowance'] + (int)$row['responsible_allowance'] + (int)$row['seniority_allowance'] + (int)$row['other_allowance1'] + (int)$row['other_allowance2'];
 			$html = '';
			$html .= '<tr>
 				<td class="center">'.++$i.'</td>
 				<td>'.$row['full_name'].'</td>
 				<td><input name="gas_allowance[]" id="gas_allowance'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['gas_allowance']).'"></td>
 				<td><input name="lunch_allowance[]" id="lunch_allowance'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['lunch_allowance']).'"></td>
 				<td><input name="tele_allowance[]" id="tele_allowance'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['tele_allowance']).'"></td>
 				<td><input name="responsible_allowance[]" id="responsible_allowance'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['responsible_allowance']).'"></td>
 				<td><input name="seniority_allowance[]" id="seniority_allowance'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['seniority_allowance']).'"></td>
 				<td><input name="other_allowance1[]" id="other_allowance1'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['other_allowance1']).'"></td>
 				<td><input name="other_allowance2[]" id="other_allowance2'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalAllowance('.$i.')" value="'.format_number($row['other_allowance2']).'"></td>
 				<td class="right" id="total_allowance'.$i.'">'.format_number($total_allowance).'</td>
 			</tr>';
 		}

 		return $html;
 	}

 	function calculateInsurrance($month_search, $year_search) {
 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , s.social_insurance
 				FROM users u 
 				LEFT JOIN (
 					SELECT * FROM ec_employee_salary WHERE deleted = 0 
 					AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'"
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
 						AND CONCAT(year, "-", month, "-01") <= "'.$year_search."-".$month_search."-01".'" 
 						ORDER BY date_entered LIMIT 1
 					)
 					GROUP BY assigned_user_id
 				) AS s ON s.assigned_user_id = u.id
 				WHERE u.deleted = 0 
 				AND u.start_working_date IS NOT NULL AND LENGTH(u.start_working_date) > 0
 				AND u.status = "Active"
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
 		// echo $sql;
 		$res = $this->bean->db->query($sql);
 		$i = 0;
 		while($row = $this->bean->db->fetchByAssoc($res)) {
			$html = '';
 			$html .= '<tr>
 				<td class="center">'.++$i.'</td>
 				<td>'.$row['full_name'].'</td>
 				<td><input name="social_insurance[]" id="insurance_rate'.$i.'" class="allow-number-only" type="text" oninput="calculateTotalInsurance('.$i.')" value="'.$row['social_insurance'].'"></td>
 				<td class="right" id="com_social_insurance'.$i.'">'.format_number($row['social_insurance']*0.175).'</td>
 				<td class="right" name="com_health_insurance[]" id="com_health_insurance'.$i.'">'.format_number($row['social_insurance']*0.03).'</td>
 				<td class="right" name="com_accident_insurance[]" id="com_accident_insurance'.$i.'">'.format_number($row['social_insurance']*0.01).'</td>
 				<td class="right" name="emp_social_insurance[]" id="emp_social_insurance'.$i.'">'.format_number($row['social_insurance']*0.08).'</td>
 				<td class="right" name="emp_health_insurance[]" id="emp_health_insurance'.$i.'">'.format_number($row['social_insurance']*0.015).'</td>
 				<td class="right" name="emp_accident_insurance[]" id="emp_accident_insurance'.$i.'">'.format_number($row['social_insurance']*0.01).'</td>
 			</tr>';
 		}

 		return $html;
 	}

 	function saveCommission() {
 		for($i = 0; $i < count($_POST['commission_deleted']); $i++) {
 			$commission = new EC_Commission;
 			$commission->id = $_POST['commission_detail'][$i];
 			$commission->name = $_POST['commission_order'][$i];
 			$commission->month = $_POST['month_search'];
 			$commission->year = $_POST['year_search'];
 			$commission->from_value = $_POST['commission_from_amt'][$i];
 			$commission->to_value = $_POST['commission_to_amt'][$i];
 			$commission->percentage = $_POST['commission_percentage'][$i];
 			$commission->deleted = $_POST['commission_deleted'][$i];
 			if($commission->deleted == 1) {
 				$commission->mark_deleted($commission->id);
 			} else {
 				$commission->save();
 			}
 		}
 	}

 	function saveSalaryInf() {
 		for($i = 0; $i < count($_POST['basic_salary']); $i++) {
 			$salary = new EC_Employee_Salary;
 			$salary->id = $_POST['salary_detail'][$i];
 			$salary->assigned_user_id = $_POST['assigned_user_id'][$i];
 			$salary->name = $_POST['full_name'][$i];
 			$salary->month = $_POST['month'];
 			$salary->year = $_POST['year'];
 			$salary->basic_salary = $_POST['basic_salary'][$i];
 			$salary->efficient_wage = $_POST['efficient_wage'][$i];
 			$salary->minus = $_POST['minus'][$i];

 			// phụ cấp
 			$salary->gas_allowance = $_POST['gas_allowance'][$i];
 			$salary->lunch_allowance = $_POST['lunch_allowance'][$i];
 			$salary->tele_allowance = $_POST['tele_allowance'][$i];
 			$salary->responsible_allowance = $_POST['responsible_allowance'][$i];
 			$salary->seniority_allowance = $_POST['seniority_allowance'][$i];
 			$salary->other_allowance1 = $_POST['other_allowance1'][$i];
 			$salary->other_allowance2 = $_POST['other_allowance2'][$i];

 			// BHXH
 			$salary->social_insurance = $_POST['social_insurance'][$i];
 			$salary->save();

 			// cập nhật thông tin lương và phụ cấp cho nhân viên chỉ khi nhập đúng tháng hiện tại
 			// nhập khác tháng thì không cập nhật
 			if($_POST['month'].'-'.$_POST['year'] >= date('m-Y', strtotime('-1 month'))) {
 				$u = new User;
 				$u->retrieve($_POST['assigned_user_id'][$i]);
 				$u->basic_salary = str_replace(',', '', $_POST['basic_salary'][$i]);
 				$u->efficient_wage = str_replace(',', '', $_POST['efficient_wage'][$i]);
 				$u->gas_allowance = str_replace(',', '', $_POST['gas_allowance'][$i]);
 				$u->lunch_allowance = str_replace(',', '', $_POST['lunch_allowance'][$i]);
	 			$u->tele_allowance = str_replace(',', '', $_POST['tele_allowance'][$i]);
	 			$u->responsible_allowance = str_replace(',', '', $_POST['responsible_allowance'][$i]);
	 			$u->seniority_allowance = str_replace(',', '', $_POST['seniority_allowance'][$i]);
	 			$u->other_allowance1 = str_replace(',', '', $_POST['other_allowance1'][$i]);
	 			$u->other_allowance2 = str_replace(',', '', $_POST['other_allowance2'][$i]);
	 			$u->save();
 			}
 		}
 	}

 	function populateEffortDetail($smartyobj, $user_id) {
 		$user = new User;
 		$user->retrieve($user_id);

 		$smartyobj->assign('isEffortDetail', 1);
 		$smartyobj->assign('MONTH', $_REQUEST['month']);
 		$smartyobj->assign('YEAR', $_REQUEST['year']);
 		$smartyobj->assign('EMPLOYEE_NAME', $user->last_name.' '.$user->first_name);

 		$fdate = $_REQUEST['year'].'-'.str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT).'-01';
 		$tdate = date('Y-m-t', strtotime($_REQUEST['year'].'-'.str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT).'-01'));

 		$sql = 'SELECT SUM( dt.quantity ) * 20000 AS amount, SUM( dt.quantity ) AS qty
 					 , b.assigned_user_id, b.id AS booking_id, b.name AS booking_name
 					 , DATE_FORMAT(DATE_ADD(b.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y %H:%i:%s") AS date_entered 
 					 , DATE_FORMAT(DATE_ADD(p.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y %H:%i:%s") AS transfer_time 
				FROM
					ec_booking_details dt 
					LEFT JOIN ec_flight_bookings b ON b.id = dt.booking_id AND b.deleted = 0
					LEFT JOIN ec_working_process p ON p.parent_id = b.id AND p.deleted = 0 AND p.paid = 1
				WHERE
					dt.deleted = 0 
					AND dt.booking_id IN (
						SELECT
							bk.id
						FROM
							ec_flight_bookings bk
							INNER JOIN ec_working_process p ON p.parent_id = bk.id 
							AND p.deleted = 0 AND p.paid = 1 
							AND DATE_ADD( p.date_entered, INTERVAL 7 HOUR ) NOT BETWEEN DATE_FORMAT( DATE_ADD( p.date_entered, INTERVAL 7 HOUR ), "%Y-%m-%d 07:30:00") AND DATE_FORMAT( DATE_ADD( p.date_entered, INTERVAL 7 HOUR ), "%Y-%m-%d 21:00:00")
						WHERE
						bk.deleted = 0 
						AND DATE_ADD( bk.date_entered, INTERVAL 7 HOUR ) >= "'.$fdate.'" 
						AND DATE_ADD( bk.date_entered, INTERVAL 7 HOUR ) <= "'.$tdate.'" 
						AND DATE_ADD( bk.date_entered, INTERVAL 7 HOUR ) NOT BETWEEN DATE_FORMAT( DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d 07:30:00") AND DATE_FORMAT( DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d 21:00:00")
						AND bk.booking_status <> 4 
					  	AND bk.is_paid = 1 
					) 
					AND b.assigned_user_id = "'.$user_id.'"
				GROUP BY b.id
				UNION
				SELECT SUM(p.ticket_delivery) * 20000 AS amount, 0 AS qty, p.assigned_user_id
					 , b.id AS booking_id, b.name AS booking_name  
					 , DATE_FORMAT(DATE_ADD(b.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_entered
					 , "" AS transfer_time
				FROM ec_working_process p 
				INNER JOIN ec_flight_bookings b ON b.id = p.parent_id AND b.deleted = 0
				AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "'.$fdate.'" 
				AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "'.$tdate.'" 
				WHERE p.deleted = 0 AND p.ticket_delivery = 1 
				AND p.assigned_user_id = "'.$user_id.'"
				GROUP BY b.id
				ORDER BY date_entered';
		$res = $this->bean->db->query($sql);
		$i = 1;
		$total_night = $total_delivery = 0;
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$html = '';
			$html .= '<tr>
				<td class="center">'.$i++.'</td>
				<td class="center">'.$row['date_entered'].'</td>
				<td class="center">'.$row['transfer_time'].'</td>
				<td><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='.$row['booking_id'].'" target="_blank">'.$row['booking_name'].'</a></td>
				<td class="right">'.$row['qty'].'</td>
				<td class="right">'.(!empty($row['qty'])?format_number($row['amount']):'').'</td>
				<td class="right">'.(empty($row['qty'])?format_number($row['amount']):'').'</td>
			</tr>';

			(!empty($row['qty']) ? $total_night += $row['amount']:$total_delivery += $row['amount']);
			$total_qty = 0;
			$total_qty += $row['qty'];
		}

		$html .= '<tr>
			<td></td>
			<td></td>
			<td></td>
			<td><b>Tổng</b></td>
			<td class="right"><b>'.format_number($total_qty).'</b></td>
			<td class="right"><b>'.format_number($total_night).'</b></td>
			<td class="right"><b>'.format_number($total_delivery).'</b></td>
		</tr>';	

		$html .= '<table style="display:none" cellpadding="0" cellspacing="0">

		</table>';

		$smartyobj->assign('DETAIL_TBL', $html);
 	}

 	function checkApproved($month, $year) {
 		$sql = 'SELECT is_approved 
 				FROM ec_employee_salary 
 				WHERE deleted = 0 
 				AND month = "' . $month . '" 
 				AND year = "' . $year . '"
 				LIMIT 1';
 		$is_approved = $this->bean->db->getOne($sql);
 		return $is_approved;
 	}

 	function updateApprovedStatus() {
 		global $current_user;
 		$sql = 'UPDATE ec_employee_salary 
 				SET is_approved = 1, approved_by = "'.$current_user->id.'"
 				, approved_date = "'.date('Y-m-d').'" 
 				WHERE deleted = 0 AND month = "' . $_POST['month_search'] . '" 
 				AND year = "' . $_POST['year_search'] . '"';
 		$this->bean->db->query($sql);
 	}

 	function populateDetail($smartyobj, $user_id) {
 		global $app_list_strings;

 		$user = new User;
 		$user->retrieve($user_id);

 		if(!empty($user_id)) {
	 		$month = str_pad($_REQUEST['month'], 2, 0, STR_PAD_LEFT) . '-' . $_REQUEST['year'];
	 		$today = date('Y-m-d');

	 		if(empty($_REQUEST['month'])) $_REQUEST['month'] = date('n');
			if(empty($_REQUEST['year'])) $_REQUEST['year'] = date('Y');

			$salary_type = '';
			$salary_select = '';
			$minus_select = '';
			foreach($app_list_strings['salary_minus_list'] AS $key => $value) {
				$salary_type .= 'CASE WHEN reason = "'.$key.'" THEN SUM(IFNULL(minus_amount, 0)) END AS "'.$key.'",';
				$salary_select .= ' SUM(t.'.$key.') AS "'.$key.'",';
				$minus_select .= ', minus.' . $key;
			}
			$salary_type = rtrim($salary_type, ",");
			$salary_select = rtrim($salary_select, ",");

	 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
	 					 , s.basic_salary, s.efficient_wage, s.overnight, s.delivery
	 					 , SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income 
	 					 , SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
	 					 , l.used_leave_days AS used_leave_days
	 					 , IFNULL(l.no_paid_days, 0) AS no_paid_days
	 					 , IFNULL(l.absence_days, 0) AS leave_days
	 					 , ot.working_hour AS overtime
	 					 , s.minus, s.social_insurance
	 					 , s.effort, ROUND(s.sales) AS bonus
	 					 , u.id AS user_id, u.employee_type
	 					 , s.description AS review, s.actual_salary
	 					 '.$minus_select.' 
	  				FROM users u 
	 				LEFT JOIN (
	 					SELECT SUM(IFNULL(used_leave_days_curr_m, 0)) AS used_leave_days
	 						 , SUM(IFNULL(no_paid_days, 0)) AS no_paid_days
	 						 , SUM(IFNULL(absence_days, 0)) AS absence_days
	 						 , assigned_user_id
	 					FROM ec_leaveabsences 
	 					WHERE deleted = 0
	 					AND status = 2 AND (DATE_FORMAT(from_date, "%m-%Y") = "'.$month.'" 
	 					OR DATE_FORMAT(to_date, "%m-%Y") = "'.$month.'") 
	 					AND (to_date <= "'.$today.'"
	 					OR from_date <= "'.$today.'")
	 					AND assigned_user_id = "'.$user_id.'"
	 					GROUP BY assigned_user_id
	 				) AS l ON l.assigned_user_id = u.id
	 				LEFT JOIN (
	 					SELECT SUM(working_hour) AS working_hour, assigned_user_id 
	 					FROM ec_workingovertimedetails
	 					WHERE deleted = 0 AND status = 2 
	 					AND register_date <= "'.$today.'"
	 					AND DATE_FORMAT(register_date, "%m-%Y") = "'.$month.'"
	 					AND assigned_user_id = "'.$user_id.'"
	 					GROUP BY assigned_user_id
	 				) AS ot ON ot.assigned_user_id = u.id
	 				LEFT JOIN (
	 					SELECT basic_salary, efficient_wage, gas_allowance
	 						 , lunch_allowance, overnight, delivery
	 						 , tele_allowance, responsible_allowance, seniority_allowance
	 						 , other_allowance1, other_allowance2, social_insurance
	 						 , assigned_user_id, effort, minus, sales
	 						 , description, actual_salary
	 					FROM ec_employee_salary 
	 					WHERE deleted = 0 
	 					AND CONCAT(year, "-", month, "-01") <= "'.$_REQUEST['year']."-".$_REQUEST['month']."-01".'"
	 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
	 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
	 						AND CONCAT(year, "-", month, "-01") <= "'.$_REQUEST['year']."-".$_REQUEST['month']."-01".'" 
	 						ORDER BY date_entered DESC LIMIT 1
	 					)
	 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= (
	 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_employee_salary WHERE deleted = 0 
	 						AND CONCAT(year, "-", month, "-01") <= "'.$_REQUEST['year']."-".$_REQUEST['month']."-01".'" 
	 						ORDER BY date_entered LIMIT 1
	 					)
	 					AND assigned_user_id = "'.$user_id.'"
	 					GROUP BY assigned_user_id
	 				) AS s ON s.assigned_user_id = u.id
	 				LEFT JOIN (
	 					SELECT '.$salary_select.', t.assigned_user_id
	 					FROM
	 					(
		 					SELECT '.$salary_type.', assigned_user_id
		 					FROM ec_salary_details 
		 					WHERE deleted = 0
		 					AND voucher_date >= "'.date("Y-m-d", strtotime("01-".$month)).'"
		 					AND voucher_date <= "'.date("Y-m-t", strtotime("01-".$month)).'"
		 					AND assigned_user_id = "'.$user_id.'"
		 					GROUP BY reason
		 				) AS t
		 				GROUP BY t.assigned_user_id
	 				) AS minus ON minus.assigned_user_id = u.id
	 				WHERE u.deleted = 0 
	 				AND u.start_working_date IS NOT NULL
	 				AND u.status = "Active"
	 				AND u.id = "'.$user_id.'"
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
	 		// if($GLOBALS['current_user']->user_name == 'nponline') {
	 		// 	echo $sql; exit;
	 		// }
	 		$res = $this->bean->db->query($sql);
	 		$row = $this->bean->db->fetchByAssoc($res);

	 		// chi tiết giảm trừ
			$minus_detail = '';
	 		foreach($app_list_strings['salary_minus_list'] AS $key => $value) {
				$minus_detail .= '<tr>
					<td>'.$value.'</td>
					<td class="right">'.format_number($row[$key]).'</td>
				</tr>';
			}

			if(date('m') == $_REQUEST['month']) {
 				$working_days = date('j');
 			} else {
 				$working_days = date('t', strtotime('01-'.$month));
 			}

 			$working_days = $working_days - count($this->getExcludeDays($month)) - $row['no_paid_days'] + ($row['overtime'] / 8);
 			if($row['employee_type'] == 3) $working_days = 0;

 			$bonus = ($row['income'] + $row['allowance']) / 26 * $working_days + $row['bonus'] + $row['overnight'] + $row['delivery'];
 			$minus = $row['minus'] + $row['social_insurance'] * 0.105;

			$smartyobj->assign('WORKING_DAYS', $working_days);
			$smartyobj->assign('USED_LEAVE', $row['used_leave_days']);
			$smartyobj->assign('BASIC_SALARY', format_number($row['basic_salary']));
			$smartyobj->assign('EFFICIENT_WAGE', format_number($row['efficient_wage']));
			$smartyobj->assign('INCOME', format_number($row['income']));
			$smartyobj->assign('COM_SOCIAL_INSURANCE', format_number(0.215 * $row['social_insurance']));
			$smartyobj->assign('TEMP_SALARY', format_number($row['income'] / 26 * $working_days));
			$smartyobj->assign('ALLOWANCE', format_number($row['allowance']));
	 		$smartyobj->assign('BONUS', format_number($row['bonus']));
	 		$smartyobj->assign('OVERNIGHT', format_number($row['overnight']));
	 		$smartyobj->assign('DELIVERY', format_number($row['delivery']));
	 		$smartyobj->assign('TOTAL_BONUS', format_number($bonus));
	 		$smartyobj->assign('TOTAL_MINUS', format_number($minus));
	 		$smartyobj->assign('MINUS_DETAIL', $minus_detail);
	 		$smartyobj->assign('EMP_SOCAIL_INSURANCE', format_number(0.105 * $row['social_insurance']));
	 		$smartyobj->assign('TOTAL_INCOME', format_number($bonus - $minus));
	 		
	 		$smartyobj->assign('EMPLOYEE_NAME', $user->last_name.' '.$user->first_name);

	 		if(is_admin($user->id) || $user->title == 'QuanLy') {
	 			$smartyobj->assign('SAVE_BTN', '<input type="submit" name="saveDetail" value="Lưu">');
	 			$smartyobj->assign('ACTUAL_SALARY', '<input type="text" class="right allow-number-only" name="actual_salary" value="'.$row['actual_salary'].'">');
	 			$smartyobj->assign('REVIEW', '<input type="text" name="review" value="'.$row['review'].'">');
	 			$smartyobj->assign('USER', $user->id);
	 		} else {
	 			$smartyobj->assign('ACTUAL_SALARY', $row['actual_salary']);
	 			$smartyobj->assign('REVIEW', $row['review']);
	 		}
	 	} else {
	 		$smartyobj->assign('EMPLOYEE_NAME', "Thiếu thông tin nhân viên");
	 	}

	 	$smartyobj->assign('isDetail', 1);
	 	$smartyobj->assign('MONTH', $_REQUEST['month']);
 		$smartyobj->assign('YEAR', $_REQUEST['year']);
 	}

 	// lưu chi tiết giảm trừ
 	// cập nhật tiền giảm trừ trong bảng lương
 	function saveMinus() {
 		global $current_user;

 		$total_minus = 0;
 		for($i = 0; $i < count($_POST['minus_amount']); $i++) {
 			if(strtotime($_POST['date_minus'][$i]) != false) {
		 		$minus_dt = new EC_Salary_Details;
		 		$minus_dt->id = $_POST['minus_detail'][$i]; 
		 		$minus_dt->assigned_user_id = $_POST['assigned_user'];
		 		$minus_dt->minus_amount = unformat_number($_POST['minus_amount'][$i]);
		 		$minus_dt->description = $_POST['minus_description'][$i];
		 		$minus_dt->reason = $_POST['reason'][$i];
		 		$minus_dt->type = 'minus';
		 		$minus_dt->voucher_date = $_POST['date_minus'][$i];
		 		$minus_dt->deleted = $_POST['minus_deleted'][$i];

		 		$monthyear[] = date('Y-m-01', strtotime($_POST['date_minus'][$i]));
		 	
		 		if($minus_dt->deleted == 0) {
		 			if(date('Y-m', strtotime($_POST['date_minus'][$i])) == $_POST['year'].'-'.str_pad($_POST['month'], 2, 0, STR_PAD_LEFT)) {
		 				$total_minus += unformat_number($_POST['minus_amount'][$i]);
		 			}
		 			$minus_dt->save();
		 		} else {
		 			$minus_dt->mark_deleted($minus_dt->id);
		 		}
		 	}
	 	} 

	 	$monthyear = array_unique($monthyear);

	 	// cập nhật tổng giảm trừ của nhân viên
	 	for($k = 0; $k <  count($monthyear); $k++) {
		 	$sql_upt = 'UPDATE ec_employee_salary SET minus = '.$total_minus.'
		 				WHERE is_approved = 0 
		 				AND assigned_user_id = "' . $_POST['assigned_user'] . '"
		 				AND month = ' . date('m', strtotime($monthyear[$k])) . ' 
		 				AND year = ' . date('Y', strtotime($monthyear[$k]));
		 	// if($current_user->user_name == 'nponline') {
		 	// 	echo $sql_upt; exit;
		 	// }
		 	$this->bean->db->query($sql_upt);
		 }

	 	// redirect
	 	header("Location: index.php?module=EC_LeaveAbsences&action=employeesalary&edit_btn");
		exit;
 	}

 	function saveDetail() {
 		$sql = 'UPDATE ec_employee_salary 
 				SET description = "'.$_POST['review'].'"
 				  , actual_salary = "'.unformat_number($_POST['actual_salary']).'"
 				WHERE deleted = 0 AND assigned_user_id = "'.$_POST['user_id'].'" 
 				AND month = "'.$_POST['month'].'" AND year = "'.$_POST['year'].'"';
 		$this->bean->db->query($sql);
 		if($GLOBALS['current_user']->user_name == 'nponline') {
 			echo $sql;
 		}
 		// redirect
	 	header("Location: index.php?module=EC_LeaveAbsences&action=employeesalary&for=showdetail&user_id=".$_POST['user_id']."&month=".$_POST['month']."&year=".$_POST['year']);
		exit;
 	}
} 