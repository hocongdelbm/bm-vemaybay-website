<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
class Viewusedleaveday extends SugarView {
 	function display() {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/EC_LeaveAbsences/tpls/view_usedleaveday.tpl');
 	}	

 	function populateContent($smartyobj) {
 		global $current_user;
 		$user_id = '';
 		if(!is_admin($current_user) && $current_user->title != 'QuanLy') {
 			$user_id = $current_user->id;
 		}

 		for($i = date('Y') - 1; $i < (date('Y') + 3); $i++) {
 			$year[$i] = $i;
 		}
 		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($year, $_POST['year_search']));

 		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y');
 		
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
 			$sql_month .= ', SUM(
 								IF(DATE_FORMAT(from_date, "%m-%Y") = DATE_FORMAT(to_date, "%m-%Y") AND DATE_FORMAT(from_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_curr_m, 0), 
									IF(DATE_FORMAT(from_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_curr_m, 0), 
										IF(DATE_FORMAT(to_date, "%m-%Y") = "'.str_pad($i, 2, 0, STR_PAD_LEFT).'-'.$year.'", IFNULL(used_leave_days_next_m, 0), 0)
									)
 								) 
 							) AS total_month'.$i;
 			$total_used .= 'total_month'.$i;
 			if($i < 12) {
 				$total_used .= ' +';
 			}
 		}

 		$total_used .= ') AS total_used';

 		$sql = 'SELECT t.* '.$total_used.' FROM (
	 				SELECT u.id AS user_id
	 					 , CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
	 					 , ROUND(IF(DATEDIFF("'.date($year.'-m-d').'", u.start_working_date) > 365, 12, 0)) AS total_leave_days
	 					 '.$sql_month.' 
	 				FROM users u 
	 				LEFT JOIN ec_leaveabsences np ON u.id = np.assigned_user_id 
	 				AND np.deleted = 0
	 				AND (DATE_FORMAT(np.from_date, "%Y") = '.$year.'
	 				OR DATE_FORMAT(np.to_date, "%Y") = '.$year.')
	 				WHERE u.deleted = 0 AND u.status = "Active" 
	 				AND u.start_working_date IS NOT NULL AND LENGTH(u.start_working_date) > 0
	 				GROUP BY u.id
	 			) AS t
	 			'.$user_search.'
	 			GROUP BY t.user_id';
 		// if(is_admin($GLOBALS['current_user'])) {
 		// 	echo $sql; exit;
 		// }
 		$res = $this->bean->db->query($sql);
		$html = '';
 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			$total_leave_days = number_format($row['total_leave_days'], 1, '.', ',');
 			$html .= '<tr>
 				<td>'.$row['full_name'].'</td>
 				<td class="right">'.$total_leave_days.'</td>
 				<td class="right">'.$row['total_month1'].'</td>
 				<td class="right">'.$row['total_month2'].'</td>
 				<td class="right">'.$row['total_month3'].'</td>
 				<td class="right">'.$row['total_month4'].'</td>
 				<td class="right">'.$row['total_month5'].'</td>
 				<td class="right">'.$row['total_month6'].'</td>
 				<td class="right">'.$row['total_month7'].'</td>
 				<td class="right">'.$row['total_month8'].'</td>
 				<td class="right">'.$row['total_month9'].'</td>
 				<td class="right">'.$row['total_month10'].'</td>
 				<td class="right">'.$row['total_month11'].'</td>
 				<td class="right">'.$row['total_month12'].'</td>
 				<td class="right">'.number_format(((float)$total_leave_days - (float)$row['total_used']), 1, '.', ',').'</td>
 				<td></td>
 			</tr>';
 		}
 		return $html;
 	}
 
}
?>
