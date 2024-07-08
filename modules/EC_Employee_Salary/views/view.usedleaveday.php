<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewusedleaveday extends SugarView {

 	function display() {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/EC_Employee_Salary/tpls/view_usedleaveday.tpl');
 	}	

 	function populateContent($smartyobj) {
 		global $current_user;
 		$user_id = '';
 		if(!is_admin($current_user) && $current_user->title != 'QuanLy') {
 			$user_id = $current_user->id;
 		}

 		for($i = 2022; $i < (date('Y') + 3); $i++) {
 			$year[$i] = $i;
 		}
 		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y');
 		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($year, (int)$_POST['year_search']));
 		
 		$data = $this->getTable($_POST['year_search'], $user_id);
 		$smartyobj->assign('USEDLEAVEDAY_TBL', $data);
 	}

 	function getTable($year, $user_id = "") {
 		$user_search = '';
 		if(!empty($user_id)) {
 			$user_search = 'WHERE t.user_id = "' . $user_id . '"';
 		}
 		$total_used = ',SUM(';
		$sql_month = '';
 		for($i = 1; $i <= 12; $i++) {
 			$sql_month .= '
				, SUM(
					IF(DATE_FORMAT(from_date, "%m-%Y") = DATE_FORMAT(to_date, "%m-%Y") AND DATE_FORMAT(from_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_curr_m, 0), 
						IF(DATE_FORMAT(from_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_curr_m, 0), 
							IF(DATE_FORMAT(to_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_next_m, 0), 0)
						)
					) 
				) AS total_month' . $i;
 			$total_used .= 'total_month'.$i;
 			if($i < 12) {
 				$total_used .= ' +';
 			}
 		}

 		$total_used .= ') AS total_used';

		// lấy mốc tính số ngày vào làm
		if(strtotime(date('Y-m-d')) > strtotime($year.'-12-31')) {
			$init_date = $year.'-12-31';
		} else {
			$init_date = date('Y-m-d');
		}

 		$sql = '
			SELECT t.* '.$total_used.' FROM (
				SELECT u.id AS user_id, u.title, u.start_working_date
						, CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
						, ROUND(
							IF(
								DATEDIFF(
									"'.$init_date.'", u.start_working_date
								) > 365
								, 12, 0
							)
						) AS total_leave_days
						'.$sql_month.' 
				FROM users u 
				LEFT JOIN ec_leaveabsences np ON u.id = np.assigned_user_id 
				AND np.deleted = 0 AND np.status = 2
				AND (DATE_FORMAT(np.from_date, "%Y") = '.$year.'
				OR DATE_FORMAT(np.to_date, "%Y") = '.$year.')
				WHERE u.deleted = 0 AND u.status = "Active" 
				AND u.start_working_date IS NOT NULL AND LENGTH(u.start_working_date) > 0
				GROUP BY u.id
			) AS t
			'.$user_search.'
			GROUP BY t.user_id
			ORDER BY (
				CASE 
					WHEN t.title LIKE "%QuanLy%" THEN 1
					WHEN t.title LIKE "%KeToan%" THEN 2
					WHEN t.title LIKE "%Leader%" THEN 3
					WHEN t.title LIKE "%Booker%" THEN 4
				ELSE 5
				END 
			), t.start_working_date';

 		// if(is_admin($GLOBALS['current_user'])) {
 		// 	pr($sql);
 		// }

 		$res = $this->bean->db->query($sql);
		$html = '';

		$arr_user_ignore = array(
			'de780bcd-2723-084b-e4af-56610c219aab', //Hà
			'd14007fa-aaed-cac7-9a00-62cfccf58d5a', //Đức
			'af285bfa-8bbf-dd0b-4394-64015e84ab94', //Nghị
		);

 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			$total_leave_days = number_format($row['total_leave_days'], 1, '.', ',');

			// Bỏ qua
			if(in_array($row['user_id'], $arr_user_ignore) || ($row['user_id'] == 'b4ff32c8-8a1e-0648-b20d-63437ab44554' && $year <= 2023)){
				continue;
			}

 			$html .= '<tr>
 				<td class="fw-bold">'.$row['full_name'].'</td>
 				<td class="text-end">'.$total_leave_days.'</td>
 				<td class="text-end">'.$row['total_month1'].'</td>
 				<td class="text-end">'.$row['total_month2'].'</td>
 				<td class="text-end">'.$row['total_month3'].'</td>
 				<td class="text-end">'.$row['total_month4'].'</td>
 				<td class="text-end">'.$row['total_month5'].'</td>
 				<td class="text-end">'.$row['total_month6'].'</td>
 				<td class="text-end">'.$row['total_month7'].'</td>
 				<td class="text-end">'.$row['total_month8'].'</td>
 				<td class="text-end">'.$row['total_month9'].'</td>
 				<td class="text-end">'.$row['total_month10'].'</td>
 				<td class="text-end">'.$row['total_month11'].'</td>
 				<td class="text-end">'.$row['total_month12'].'</td>
 				<td class="text-end">'.number_format(((float)$total_leave_days - (float)$row['total_used']), 1, '.', ',').'</td>
 				<td></td>
 			</tr>';
 		}
 		return $html;
 	}
 
}
?>
