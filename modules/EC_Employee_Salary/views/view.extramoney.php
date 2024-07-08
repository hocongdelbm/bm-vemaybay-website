<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewextramoney extends SugarView {
 	function display() {
 		$smarty = new Sugar_Smarty();
 		if(isset($_POST['btnSaveExtra'])) {
 			$this->saveExtra();
 		} else {
 			$this->populateContent($smarty);
 		}
 		$smarty->display('modules/EC_Employee_Salary/tpls/view_extramoney.tpl');
 	}

 	function populateContent($smarty) {
 		// tháng
 		for($m = 1; $m <= 12; $m++) {
 			$month[$m] = $m;
 		}
 		if(empty($_REQUEST['month'])) $_REQUEST['month'] = date('n', strtotime('-1 month'));
 		$smarty->assign('MONTH', get_select_options_with_id($month, (int)$_REQUEST['month']));

 		// năm 
 		for($i = date('Y') - 1; $i <= date('Y') + 1; $i++) {
 			$year[$i] = $i;
 		}
 		if(empty($_REQUEST['year'])) $_REQUEST['year'] = date('Y');
 		$smarty->assign('YEAR', get_select_options_with_id($year, (int)$_REQUEST['year']));

 		// tạo ra bảng hoàn tạm ứng
 		$data = $this->populateExtraMoney($_REQUEST['month'], $_REQUEST['year']);
 		$smarty->assign('DATA', $data);
 	}

 	function populateExtraMoney($month_search, $year_search) {

 		$today 				= date('Y-m-d');
 		$m_search 			= $month_search . '-' . $year_search;
 		$month 				= str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
 		$date_search 			= '01-'.str_pad($month_search, 2, 0, STR_PAD_LEFT).'-'.$year_search;
		$date_end_search 		= date('Y-m-t', strtotime($date_search));
 		$html 				= '';
 		$total_income 			= $total_allowance 	= $total_sale = $total_tl = $total_term_income = 0;
 		$total_extra 			= $total_actual = $total_minus = $total_minus_after = 0;
 		$is_exist 			= $this->checkExistSalary($month_search, $year_search);

 		if($month == date('m-Y') && !$is_exist) {
 			$from_date_limit = ' AND DATE_FORMAT(date_entered, "%Y-%m-%d") >= "'.date('Y-m-01', strtotime('-1 month', strtotime('01-'.$month))).'" AND month = ' . date('n', strtotime('-1 month', strtotime('01-'.$month))) . ' AND year = ' . date('Y', strtotime('-1 month', strtotime('01-'.$month)));
 		} else {
 			$from_date_limit = ' AND month = ' . $month_search . ' AND year = ' . $year_search;
 		}
 
 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , SUM(IFNULL(s.basic_salary, 0) + IFNULL(s.efficient_wage, 0)) AS income 
 					 , SUM(IFNULL(s.gas_allowance, 0) + IFNULL(s.lunch_allowance, 0) + IFNULL(s.tele_allowance, 0) + IFNULL(s.responsible_allowance, 0) + IFNULL(s.seniority_allowance, 0) + IFNULL(s.other_allowance1, 0) + IFNULL(s.other_allowance2, 0)) AS allowance
 					 , SUM(IFNULL(ROUND(s.sales), 0) + IFNULL(s.effort, 0)) AS total_bonus
 					 , s.working_days, s.extra_amount, s.actual_salary
 					 , s.is_online, s.minus_amount
					 , SUM(
						IFNULL(s.minus, 0) 
						+ IFNULL(s.minus_income, 0) 
						+ 0.105 * IFNULL(s.social_insurance, 0)
					 ) AS minus
 					 , SUM(
 						IFNULL(s.effort, 0)
 					  + IFNULL(s.extra_amount, 0)
 					  + IFNULL(s.sales, 0)
 					  - 0.105 * IFNULL(s.social_insurance, 0)
 					  - IFNULL(s.minus, 0) 
					  - IFNULL(s.minus_income, 0)
 					 ) AS salary
 					 , u.id AS user_id, u.employee_type
					 , u.his_stt, u.his_date_start, u.his_date_end
  				FROM (
 					SELECT usr.id, usr.start_working_date, usr.status
 						 , usr.last_name, usr.first_name
 						 , usr.employee_type, usr.deleted
 						 , usr.title, SUM(his.with_salary) AS with_salary
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
									) AND date_start >= IFNULL(
										( 
											SELECT date_start 
											FROM ec_workhistory 
											WHERE deleted = 0 
											AND status = "InActive" 
											AND assigned_user_id = h.assigned_user_id
											ORDER BY date_start DESC LIMIT 1,1 
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
 					FROM users usr
 					INNER JOIN ec_workhistory his
 					ON his.assigned_user_id = usr.id
 					AND his.deleted = 0
 					AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . date('Y-m-d', strtotime($date_search)) . '"
 					AND LAST_DAY(IFNULL(his.date_end, "' . $date_end_search . '")) >= "' . $date_end_search . '"
 					WHERE usr.deleted = 0
					GROUP BY usr.id
 				) AS u 
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
 					GROUP BY assigned_user_id
 				) AS l ON l.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT SUM(working_hour) AS working_hour, assigned_user_id 
 					FROM ec_workingovertimedetails
 					WHERE deleted = 0 AND status = 2 
 					AND register_date <= "'.$today.'"
 					AND DATE_FORMAT(register_date, "%m-%Y") = "'.$month.'"
 					GROUP BY assigned_user_id
 				) AS ot ON ot.assigned_user_id = u.id
 				LEFT JOIN (
 					SELECT basic_salary, efficient_wage, gas_allowance, lunch_allowance
 						 , tele_allowance, responsible_allowance, seniority_allowance
 						 , other_allowance1, other_allowance2, social_insurance, actual_salary
 						 , assigned_user_id, working_days, extra_amount, is_online
						 , minus_income, minus_amount
 						 , IF(CONCAT(month, "-", year) <> "'.$m_search.'", 0, minus) AS minus
 						 , IF(CONCAT(month, "-", year) <> "'.$m_search.'", 0, effort) AS effort
 						 , IF(CONCAT(month, "-", year) <> "'.$m_search.'", 0, sales) AS sales
 					FROM ec_employee_salary 
 					WHERE deleted = 0 
 					AND year = '.$year_search.' AND month <= '.$month_search.'
 					AND DATE_FORMAT(date_entered, "%Y-%m-%d") <= (
 						SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") 
 						FROM ec_employee_salary WHERE deleted = 0 
 						AND year = '.$year_search.' AND month <= '.$month_search.' 
 						ORDER BY date_entered DESC LIMIT 1
 					)
 					'.$from_date_limit. '
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
 				), u.his_date_start';
				
		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// }

		$res 	= $this->bean->db->query($sql);
		$i 		= 1;
		$total 	= 0;

		while($row = $this->bean->db->fetchByAssoc($res)) {
			if ((
					($row['his_stt'] == 'Active' || $row['his_stt'] == 'Online')
					&& strtotime($row['his_date_start']) <= strtotime($date_end_search)
				) || (
					($row['his_stt'] == 'InActive' || $row['his_stt'] == 'Absent')
					&& date('m-Y', strtotime($row['his_date_start'])) == $month
			)) {
				$thuclanh = $row['salary'] + ($row['income'] + $row['allowance']) * $row['working_days'] / 26;
				// $thucnhan = $row['actual_salary'] + $row['total_bonus'] + $row['extra_amount'];
				$thucnhan = $thuclanh + $row['extra_amount'] - $row['minus_amount'];

				$term_income = ($row['income'] + $row['allowance']) * $row['working_days'] / 26;

				// if($row['employee_type'] == 3 || !empty($row['is_online'])) $thuclanh = $row['income'];	

				if($thuclanh < 0) $thuclanh = 0;	
				
				$html .= '
				<tr>
					<td class="fw-bold text-center">'.$i.'</td>
					<td class="fw-bold">'.$row['full_name'].'</td>
					<td class="text-end">'.format_number($row['income']).'</td>
					<td class="text-end">'.format_number($row['allowance']). '</td>
					<td class="text-end">'.format_number($term_income).'</td>
					<td class="text-end total_bonus">'.format_number($row['total_bonus']).'</td>
					<td class="text-end">'.format_number($row['minus']).'</td>
					<td class="text-end thuclanh" id="salary'.$i.'">'.format_number($thuclanh - $row['extra_amount']).'</td>
					<td class="text-end"><input type="text" id="extra_amount'.$i.'" class="box-input extra_amt allow-number-only text-end" value="' . format_number($row['extra_amount']) . '" name="extra_amount[]" oninput="calculateExtra(' . $i . ')" onpaste="setTimeout(function(){calculateExtra(' . $i . ');}, 2000);"></td>
					<td class="text-end"><input type="text" id="minus_amount' . $i . '" class="box-input extra_amt text-end" value="' . format_number($row['minus_amount']) . '" name="minus_amount[]" oninput="calculateExtra(' . $i . ')" onpaste="setTimeout(function(){calculateExtra(' . $i . ');}, 2000);"></td>
					<td id="actual_salary'.$i.'" class="text-end">'. (($row['actual_salary'] > 0) ? format_number($thucnhan) : format_number($thuclanh)). '</td>
					<input type="hidden" name="user_id[]" value="'.$row['user_id'].'">
					<input type="hidden" id="actual_salary_val'.$i.'" name="actual_salary[]" value="'.$thucnhan.'">
				</tr>';
				$i++;

				// tổng cộng
				$total_income += $row['income'];
				$total_allowance += $row['allowance'];
				$total_term_income += $term_income;
				$total_sale += $row['total_bonus'];
				$total_minus += $row['minus'];
				$total_tl += round($thuclanh);
				$total_extra += $row['extra_amount'];
				$total_minus_after += $row['minus_amount'];
				$total_actual += round(($row['actual_salary'] > 0)?$thucnhan:$thuclanh);
			}
		}	
		$html .= '
		<tr class="footer-tr">
			<td class="text-center"></td>
			<td><b>Tổng cộng</b></td>
			<td class="text-end"><b>'.format_number($total_income).'</b></td>
			<td class="text-end"><b>'.format_number($total_allowance). '</b></td>
			<td class="text-end"><b>'.format_number($total_term_income).'</b></td>
			<td class="text-end"><b>'.format_number($total_sale).'</b></td>
			<td class="text-end"><b>'.format_number($total_minus).'</b></td>
			<td class="text-end"><b>'.format_number($total_tl).'</b></td>
			<td class="text-end"><b>'.format_number($total_extra). '</b></td>
			<td class="text-end"><b>'.format_number($total_minus_after). '</b></td>
			<td class="text-end"><b>'.format_number($total_actual).'</b></td>
			<input type="hidden" id="grp_seperator">
			<input type="hidden" id="dec_seperator">
			<input type="hidden" id="sig_digits" value="0">
		</tr>';
	
 		return $html;
 	}

 	function saveExtra() {
 		for($i = 0; $i < count($_POST['user_id']); $i++) {
 			$sql_update = '
				UPDATE ec_employee_salary 
				SET 
					extra_amount = '.(int)unformat_number($_POST['extra_amount'][$i]). '
					, minus_amount = ' . (int)unformat_number($_POST['minus_amount'][$i]) . '
 					, actual_salary = '.(int)unformat_number($_POST['actual_salary'][$i]).'
				WHERE deleted = 0 
				AND month = "'.$_POST['month'].'" 
				AND year = "'.$_POST['year'].'" 
				AND assigned_user_id = "'.$_POST['user_id'][$i].'"';
 			$this->bean->db->query($sql_update);

 			// if($GLOBALS['current_user']->user_name == 'nponline' && $_POST['user_id'][$i] == 'ebc40fa1-8878-1a86-000d-5b6949a87e11') {
 			// 	echo $sql_update; exit;
 			// }
 		}
 		header("Location: index.php?module=EC_Employee_Salary&action=extramoney&month=".$_POST['month']."&year=".$_POST['year']);
 	}

 	// kiểm tra đã có bảng lương tháng hiện tại
 	function checkExistSalary($month, $year) {
 		$sql_exists = 'SELECT IF(COUNT(id) > 0, 1, 0) FROM ec_employee_salary 
 					   WHERE deleted = 0 AND month = ' . $month . ' 
 					   AND year = ' . $year;
 		return $this->bean->db->query($sql_exists);
 	}

}