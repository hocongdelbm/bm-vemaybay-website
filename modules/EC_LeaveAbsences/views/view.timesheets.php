<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
class Viewtimesheets extends SugarView {
	public $date_name = array(
		'Monday' => 'T2',
		'Tuesday' => 'T3',
		'Wednesday' => 'T4',
		'Thursday' => 'T5',
		'Friday' => 'T6',
		'Saturday' => 'T7',
		'Sunday' => 'CN',
	);

 	function display() {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/EC_LeaveAbsences/tpls/view_timesheets.tpl');
 	}	

 	function populateContent($smartyobj) {
 		global $current_user;

 		for($m = 1; $m <= 12; $m++) {
 			$m_arr[$m] = $m;
 		}
 		if(empty($_POST['month_search'])) $_POST['month_search'] = date('m');
 		$smartyobj->assign('MONTH_OPTION', get_select_options_with_id($m_arr, $_POST['month_search']));

 		for($y = (date('Y') - 1); $y <= (date('Y') + 3); $y++) {
 			$y_arr[$y] = $y;
 		}
 		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y');
 		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($y_arr, $_POST['year_search']));
 		$sunday_arr = array();
		$day_col = '';
		$date_col = '';

 		for($i = 1; $i <= date('t', strtotime('01-'.$_POST['month_search'].'-'.$_POST['year_search'])); $i++) {
 			$date_name = $this->date_name[date('l', strtotime(str_pad($i, 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT).'-'.$_POST['year_search']))];

 			if($date_name == 'CN' && strtotime(date('d-m-Y')) > strtotime(str_pad($i, 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT).'-'.$_POST['year_search'])) {
 				$sunday_arr[] = $i;
 			}

 			if($i.'-'.$_POST['month_search'].'-'.$_POST['year_search'] == date('j-m-Y')) {
 				$date = '<td class="center today">'.$i.'</td>';
 				$date_name_col = '<td class="center today">'.$date_name.'</td>';
 			} else if($date_name == 'CN') {
 				$date = '<td class="center"><font color="orange">'.$i.'</font></td>';
 				$date_name_col = '<td class="center"><font color="orange">'.$date_name.'</font></td>';
 			} else {
 				$date = '<td class="center">' . $i . '</td>';
 				$date_name_col = '<td class="center">'.$date_name.'</td>';
 			}

 			$day_col .= $date;
 			$date_col .= $date_name_col;
 		}
 		$smartyobj->assign('DAY_COLS', $day_col);
 		$smartyobj->assign('DATE_COLS', $date_col);

 		if(!is_admin($current_user) && $current_user->title != 'QuanLy') {
 			$user_id = $current_user->id;
 		} else {
 			$user_id = '';
 		}

 		$data = $this->getTimeSheet(($i - 1), $user_id, str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT), $_POST['year_search'], $sunday_arr);
 		$smartyobj->assign('TIMESHEET', $data);
 	}

 	function getTimeSheet($col_num, $user_id, $month_search, $year_search, $sundays = array()) {
 		if(!empty($user_id)) {
 			$sql_search = ' AND assigned_user_id = "'.$user_id.'"';
 		}

 		$holidays = array(
 			'01' => '01'
 		);

 		$month_holiday = array(); 	
 		$search_month = str_pad($month_search, 2, 0, STR_PAD_LEFT);	
 		if(in_array($search_month, array_keys($holidays))) {
 			$month_holiday[] = $holidays[$search_month];
 		}

 		$month = $month_search . '-' . $year_search;

 		$sql = 'SELECT CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name
 					 , GROUP_CONCAT(CONCAT_WS(",", l.from_date, l.to_date, IFNULL(l.partofday, 0), IFNULL(l.part_date, ""), l.no_paid_days, l.name, l.id) SEPARATOR ";") AS absence_days  
 					 , GROUP_CONCAT(DISTINCT CONCAT_WS(",", ot.register_date, ot.multiplier, ot.working_hour, ot.ec_workingovertimes_id_c) SEPARATOR ";") AS overtime
 				FROM users u 
 				LEFT JOIN ec_leaveabsences l ON u.id = l.assigned_user_id 
 				AND l.deleted = 0 AND l.status = 2 
 				AND (DATE_FORMAT(l.from_date, "%m-%Y") = "'.$month.'"
 				OR DATE_FORMAT(l.to_date, "%m-%Y") = "'.$month.'") 
 				LEFT JOIN ec_workingovertimedetails ot ON ot.assigned_user_id = u.id
 				AND ot.status = 2 AND ot.deleted = 0 
 				AND DATE_FORMAT(ot.register_date, "%m-%Y") = "'.$month.'"
 				WHERE u.deleted = 0 AND u.status = "Active" 
 				AND u.start_working_date IS NOT NULL
 				'.str_replace("assigned_user_id", "u.id", $sql_search).'
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

 		$res 			= $this->bean->db->query($sql);
		$html 			= '';
		$total_working_days = 0;

 		while($row = $this->bean->db->fetchByAssoc($res)) {
 			$html .= '<tr>
 				<td>'.$row['full_name'].'</td>';
			$absences = $this->getDateArr($row['absence_days'], ($sundays + $month_holiday), $search_month);
			
			// if(is_admin($GLOBALS['current_user']) && $row['full_name'] == 'Hoàng Minh Thiện') {
			// 	echo '<pre>';
			// 	var_dump($absences);
			// 	echo '</pre>';
			// }

			$overtimes 		= $this->getOverTimeDays($row['overtime']);
			$overtime_hours 	= 0;
			$absence_half_day 	= 0;
			$absence_no_paid 	= 0;

 			for($k = 1; $k <= $col_num; $k++) {
 				$no_paid = '';
 				$half_day = 0;
 				if($k.'-'.$month == date('j-m-Y')) $class_today = 'today';
 				else $class_today = '';
 				$html .= '<td class="center '.$class_today.' detail">';
 				$partday = '';
 				if(in_array($k, $absences['date']['date_number'])) {
 					if(in_array($k, $absences['date']['morning'])) {
 						$partday = '<br>(0.5S)';
 						$half_day = 1;
 					} 

 					if(in_array($k, $absences['date']['afternoon'])) {
 						$partday = '<br>(0.5C)';
 						$half_day = 1;
 					}
 					if(in_array($k, $absences['date']['no_paid'])) {
 						$no_paid = '<br>(KL)';
 						if(strtotime($k.'-'.$month) <= strtotime(date('j-m-Y'))) {
 							if($half_day) {
 								$absence_no_paid += 0.5;
 							} else {
 								$absence_no_paid++;
 							}
 						}
 					}
 					$html .= '<a href="index.php?module=EC_LeaveAbsences&action=DetailView&record='.$absences['date']['id'][$k].'" target="_blank"><font color="red"><b>X</b>'.$partday.$no_paid.'</font></a>';
 				} else if(in_array($k, array_keys($overtimes))) {
 					$html .= '<a href="index.php?module=EC_WorkingOverTimes&action=DetailView&record='.$overtimes[$k]['id'].'" target="_blank"><font color="blue"><b>O</b><br>(x'.$overtimes[$k]['multiply'].')</font></a>';
 					if(strtotime($k.'-'.$month) <= strtotime(date('j-m-Y'))) {
 						$overtime_hours += $overtimes[$k]['hour'];
 					}
 				}
 				
 				// if(!empty($absences['date']['name'][$k])) {
 					// $html .= '<span><a href="index.php?module=EC_LeaveAbsences&action=DetailView&record='.$absences['date']['id'][$k].'" target="_blank">'.$absences['date']['name'][$k].'</a></span>';
 				// }

 				$html .= '</td>';
 			}

 			if(date('m-Y') == $month) {
 				$working_days = date('j');
 			} else {
 				$working_days = date('t', strtotime('01-'.$month));
 			}

 			$html .= '<td class="right">'.($working_days - $absence_no_paid - count($sundays) - count($month_holiday)).'</td>';
 			$html .= '<td class="right">'.($overtime_hours / 8).'</td>';
 			$html .= '<td class="right">'.($working_days - $absence_no_paid - count($sundays) - count($month_holiday) + $overtime_hours / 8).'</td>';
 			$html .= '</tr>';

 			$total_working_days += ($working_days - $absence_no_paid - count($sundays) + count($month_holiday) + $overtime_hours / 8);
 		}

 		$html .= '<tr><td colspan="'.(date('t', strtotime('01-'.$month)) + 3).'" class="right"><b>Tổng</b></td><td class="right"><b>'.$total_working_days.'</b></td></tr>';

 		return $html;
 	}

 	function getDateArr($absence_days, $exclude_days = array(), $search_month) {
 		$absence = array();
 		if($absence_days != "0,") {
 			$absence_voucher = explode(';', $absence_days);

	 		for($k = 0; $k < count($absence_voucher); $k++) {
	 			if($absence_voucher[$k] != "0,") {
		 			$detail = explode(',', $absence_voucher[$k]);

			 		$fmonth = date('m', strtotime($detail[0]));
			 		$tmonth = date('m', strtotime($detail[1]));

			 		if($fmonth == $tmonth) {
			 			$fdate = date('j', strtotime($detail[0]));
			 			$tdate = date('j', strtotime($detail[1]));
			 			for($i = $fdate; $i <= $tdate; $i++) {
			 				if(!in_array($i, $exclude_days)) {
			 					$absence['date']['date_number'][] = $i;

			 					if(!empty($detail[4]) && $detail[4] != '0.0') {
			 						$absence['date']['no_paid'][] = $i;
			 						$detail[4] = $detail[4] - 1;
			 					}

			 					$absence['date']['name'][$i] = $detail[5];
			 					$absence['date']['id'][$i] = $detail[6];
			 				}
			 			}
			 			if(strtotime($detail[3]) != false && !empty($detail[3])) {
			 				if($detail[2] == 1) {
			 					$absence['date']['morning'][] = date('j', strtotime($detail[3]));
			 				} else if($detail[2] == 2) {
			 					$absence['date']['afternoon'][] = date('j', strtotime($detail[3]));
			 				}
		 				}
			 		} else if($fmonth == $search_month) {

			 		} else if($tmonth == $search_month) {

			 		}
			 	}
		 	}
		}
	
 		return $absence;
 	}

 	function getOverTimeDays($overtime_arr) {
 		$overtime_voucher = explode(";", $overtime_arr);

 		$overtime_date = array();
 		for($i = 0; $i < count($overtime_voucher); $i++) {
 			if(!empty($overtime_voucher[$i])) {
 				$overtime = explode(",", $overtime_voucher[$i]);
 				$overtime_date[date('j', strtotime($overtime[0]))]['multiply'] = $overtime[1];
 				$overtime_date[date('j', strtotime($overtime[0]))]['hour'] += $overtime[2];
 				$overtime_date[date('j', strtotime($overtime[0]))]['id'] = $overtime[3];
 			}
 		}

 		return $overtime_date;
 	}
}
?>
