<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

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
        $smarty->display('modules/EC_Employee_Salary/tpls/view_timesheets.tpl');
 	}	

 	function populateContent($smartyobj) {
 		global $current_user;

 		for($m = 1; $m <= 12; $m++) {
 			$m_arr[$m] = $m;
 		}

 		if(empty($_POST['month_search'])) $_POST['month_search'] = date('m');
 		$smartyobj->assign('MONTH_OPTION', get_select_options_with_id($m_arr, (int)$_POST['month_search']));

 		for($y = (date('Y') - 1); $y <= (date('Y') + 3); $y++) {
 			$y_arr[$y] = $y;
 		}

 		if(empty($_POST['year_search'])) $_POST['year_search'] = date('Y');
 		$smartyobj->assign('YEAR_OPTION', get_select_options_with_id($y_arr, (int)$_POST['year_search']));
 		$smartyobj->assign('MY', $_POST['month_search'].'-'.$_POST['year_search']);

 		$sunday_arr 	= array();
 		$sunday_arr_m 	= array();
		$day_col 		= '';
		$date_col 	= '';

 		for($i = 1; $i <= date('t', strtotime('01-'.$_POST['month_search'].'-'.$_POST['year_search'])); $i++) {

 			$date_name = $this->date_name[date('l', strtotime(str_pad($i, 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT).'-'.$_POST['year_search']))];

 			if($date_name == 'CN') {
 				if(strtotime(date('d-m-Y')) > strtotime(str_pad($i, 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT).'-'.$_POST['year_search']))
 					$sunday_arr[] = $i; 
 					$sunday_arr_m[] = $i;
 			}

 			if($i.'-'.$_POST['month_search'].'-'.$_POST['year_search'] == date('j-m-Y')) {
 				$date 		= '<th class="text-center today">'.$i.'</th>';
 				$date_name_col = '<th class="text-center today">'.$date_name.'</th>';

 			} else if($date_name == 'CN') {
 				$date 		= '<th class="text-center"><font color="color-red">'.$i.'</font></th>';
 				$date_name_col = '<th class="text-center"><font color="color-red">'.$date_name.'</font></th>';
 			} else {
 				$date 		= '<th class="text-center">' . $i . '</th>';
 				$date_name_col = '<th class="text-center">'.$date_name.'</th>';
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

 		$data = $this->getTimeSheet(($i - 1), $user_id, str_pad($_POST['month_search'], 2, 0, STR_PAD_LEFT), $_POST['year_search'], $sunday_arr, $sunday_arr_m);
 		$smartyobj->assign('TIMESHEET', $data);
 	}

 	function getTimeSheet($col_num, $user_id, $month_search, $year_search, $sundays = array(), $all_sundays_in_month = array()) {
 		if(!empty($user_id)) {
 			$sql_search = ' AND assigned_user_id = "'.$user_id.'"';
 		}

 		$month = str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . $year_search;
 		$from_date_search = $year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-01';
 		$to_date_search = $year_search . '-' . str_pad($month_search, 2, 0, STR_PAD_LEFT) . '-' . date('t', strtotime($from_date_search));

 		$sql = 'SELECT CONCAT(u.last_name, " "
 							, IFNULL(u.first_name, "")
 					   ) AS full_name, u.id AS user_id
 					 , GROUP_CONCAT(DISTINCT CONCAT_WS(",", l.from_date, l.to_date, IFNULL(l.partofday, 0), IFNULL(l.part_date, ""), l.no_paid_days, l.paid_days, l.name, l.id) SEPARATOR ";") AS absence_days  
 					 , GROUP_CONCAT(DISTINCT CONCAT_WS(",", ot.register_date, ot.multiplier, ot.working_hour, ot.ec_workingovertimes_id_c) SEPARATOR ";") AS overtime
 					 , u.with_salary, u.history_desc
 					 , u.his_stt, u.his_date_start, u.his_date_end
 					 , (
 					 	SELECT SUM(d.working_hour) / 8
 					 	FROM ec_workingovertimedetails d
 					 	INNER JOIN ec_workingovertimes t
 					 	ON t.id = d.ec_workingovertimes_id_c
 					 	AND t.status = 2
 					 	WHERE d.deleted = 0
 					 	AND d.assigned_user_id = u.id
 					 	AND DATE_FORMAT(t.bonus_month, "%m-%Y") = "'.$month. '" 
 					 ) AS bonus_work_days
 				FROM (
 					SELECT usr.id
 						 , usr.last_name, usr.first_name
 						 , usr.title
						 -- , SUM(his.with_salary) AS with_salary
						 , his.with_salary AS with_salary
 						 , his.description AS history_desc
 						 , his.status AS his_stt
 						 , IFNULL(
							(
								SELECT date_start
								FROM ec_workhistory h
								WHERE deleted = 0
								AND status = "Active"
								AND assigned_user_id = usr.id
								AND date_start <= "' . $to_date_search . '"
								ORDER BY date_start DESC LIMIT 1
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
								, DATE_ADD("' . $from_date_search . '", INTERVAL 1 DAY)
							)
						 ) AS his_date_start
						 , (
							SELECT IFNULL(
								date_end
								, IF(
									status = "InActive" OR status = "Absent"
									, DATE_SUB(date_start, INTERVAL 1 DAY)
									, "' . $to_date_search . '"
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
 					AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "'.$from_date_search.'"
 					AND LAST_DAY(IFNULL(his.date_end, "'.$to_date_search.'")) >= "'.$to_date_search.'"
 					WHERE usr.deleted = 0
					AND NOT EXISTS (
						SELECT 1
						FROM ec_workhistory sub_his
						WHERE sub_his.assigned_user_id = usr.id
								AND sub_his.deleted = 0
								AND DATE_FORMAT(sub_his.date_start, "%Y-%m-01") <= "'.$from_date_search.'"
 								AND LAST_DAY(IFNULL(sub_his.date_end, "'.$to_date_search.'")) >= "'.$to_date_search.'"
								AND sub_his.date_start > his.date_start
								AND sub_his.status <> "InActive"
					)
					-- GROUP BY usr.id
 				) AS u
 				LEFT JOIN ec_leaveabsences l ON u.id = l.assigned_user_id 
 				AND l.deleted = 0 AND l.status = 2 
 				AND (DATE_FORMAT(l.from_date, "%m-%Y") = "'.$month.'"
 				OR DATE_FORMAT(l.to_date, "%m-%Y") = "'.$month.'") 
 				LEFT JOIN ec_workingovertimedetails ot 
 				ON ot.assigned_user_id = u.id
 				AND ot.status = 2 AND ot.deleted = 0 
 				AND DATE_FORMAT(ot.register_date, "%m-%Y") = "'.$month.'"
 				WHERE u.his_date_start IS NOT NULL
				'.str_replace("assigned_user_id", "u.id", $sql_search). '
 				GROUP BY u.id
 				ORDER BY (
 					CASE 
 						WHEN u.title LIKE "%QuanLy%" THEN 1
 						WHEN u.title LIKE "%KeToan%" THEN 2
 						WHEN u.title LIKE "%Leader%" THEN 3
 						WHEN u.title LIKE "%Booker%" THEN 4
 					ELSE 5
 					END 
 				), u.his_date_start, u.first_name';
				
 		// if($GLOBALS['current_user']->user_name == 'hungnh') {
 		// 	pr($sql);
 		// }

 		$res = $this->bean->db->query($sql);
 		// tính số người làm việc trong 1 ngày
 		$working_ppl_html 	= '';
 		$working_ppl 		= array();
 		$p 				= $stt = 1; 
		$html = ''; 
		// $total_ppl 		= $res->num_rows; 
		$total_ppl 		= $this->bean->db->countRows($res);

		$total_working_days = $total_normal_days = $total_overtime = 0;

		while($row = $this->bean->db->fetchByAssoc($res)) {
 			if( $row['his_stt'] != 'InActive' && $row['his_stt'] != 'Absent' || (($row['his_stt'] == 'InActive' || $row['his_stt'] == 'Absent') && date('m-Y', strtotime($row['his_date_start'])) == $month)) {
 				$html .= '<tr>
 					<td class="text-center">'.$stt++.'</td>
 					<td id="p_name'.$p.'">'.$row['full_name'].'</td>';
	 			// hiện ngày nghỉ có lương hay không lương
				$absences = $this->getDateArr($row['absence_days'], $sundays, $month_search, $all_sundays_in_month);
				$overtimes = $this->getOverTimeDays($row['overtime']);
				$overtime_hours = 0;
				$absence_no_paid = 0;
				$no_count = 0;
				$sunday_count = 0;
	 			for($k = 1; $k <= $col_num; $k++) {
	 				// tính số người đi làm
	 				$working_ppl[$k]++;

	 				// tháng đó được tính lương
	 				if($row['with_salary']) {
		 				// tính ngày công bắt đầu từ ngày vào làm
		 				if(strtotime(str_pad($k, 2, 0, STR_PAD_LEFT).'-'.$month) >= strtotime($row['his_date_start']) && strtotime(str_pad($k, 2, 0, STR_PAD_LEFT) . '-' . $month) <= strtotime($row['his_date_end'])) {
		 					if(in_array($k, $sundays)) {
		 						$sunday_count++;
		 						$working_ppl[$k]--;
		 					}

		 					if(empty($working_ppl[$k])) $working_ppl[$k] = 0;
			 				$no_paid = '';

			 				if($k.'-'.$month == date('j-m-Y')) $class_today = 'today';
			 				else $class_today = '';

			 				$html .= '<td class="text-center '.$class_today.' detail">';
			 				$partday = '';

							if(empty($absences['date']['date_number'])) {
								$absences['date']['date_number'] = array();
							}
			 				if(in_array($k, $absences['date']['date_number']) && !in_array($k, $all_sundays_in_month)) {
			 					$working_ppl[$k]--;
			 					if(in_array($k, $absences['date']['morning'])) {
			 						$partday = '<br>(0.5SA)';
			 					}
			 					if(in_array($k, $absences['date']['afternoon'])) {
			 						$partday = '<br>(0.5CH)';
			 					}
			 					if(in_array($k, $absences['date']['no_paid'])) {
			 						if(!empty($absences['date']['no_paid_half'][$k])) {
			 							$no_paid = '<br>(0.5KL)';
			 							// tới ngày nào tính ngày đó
			 							if($month == date('m-Y') && $k <= date('j') || $month <> date('m-Y'))
			 								$absence_no_paid += 0.5;
			 						} else {
			 							$no_paid = '<br>(KL)';
			 							// tới ngày nào tính ngày đó
			 							if($month == date('m-Y') && $k <= date('j') || $month <> date('m-Y'))
			 								$absence_no_paid++;
			 						}
			 					} 
			 					$html .= '<a href="index.php?module=EC_LeaveAbsences&action=DetailView&record='.$absences['date']['id'][$k].'" target="_blank"><font color="red"><b>X</b>'.$partday.$no_paid.'</font></a>';
			 				} else if(in_array($k, array_keys($overtimes))) {
			 					$working_ppl[$k]++;
			 					$html .= '<a href="index.php?module=EC_WorkingOverTimes&action=DetailView&record='.$overtimes[$k]['id'].'" target="_blank"><font color="blue"><b>O</b><br>(+'.$overtimes[$k]['day_plus'].')</font></a>';
			 					if(strtotime($k.'-'.$month) <= strtotime(date('j-m-Y'))) {
			 						$overtime_hours += $overtimes[$k]['hour'];
			 					}
			 				} 
			 				$html .= '</td>';
			 			} else if(strtotime(str_pad($k, 2, 0, STR_PAD_LEFT) . '-' . $month) < strtotime($row['his_date_start'])) {
							$html .= '<td class="text-center disabled">Vắng</td>';
							$no_count++;
						}else if(strtotime(str_pad($k, 2, 0, STR_PAD_LEFT) . '-' . $month) > strtotime($row['his_date_end'])) {
							$html .= '<td class="text-center disabled">Đã nghỉ</td>';
							$no_count++;
						} else {
			 				$html .= '<td></td>';
			 			}

					// tháng đó không được tính lương
			 		// nếu là nghỉ hẳn, tháng bắt đầu nghỉ vẫn hiện với lý do nghỉ, tháng sau ko hiện nữa
			 		// nếu là tạm vắng thì vẫn hiện lên mỗi tháng với lý do tạm vắng
			 		} else {
			 			$working_ppl[$k]--;
			 			if($k == 1) {
			 				$html .= '<td colspan="'.$col_num.'">'.$row['history_desc'].'</td>';
			 			} 
			 		}
	 			} 

	 			if($row['with_salary']) {
	 				if(date('m-Y') == $month) {
		 				if(date('m-Y') == date('m-Y', strtotime($row['start_working_date'])))
		 					$working_days = date('j') - date('j', strtotime($row['start_working_date'])) + 1;
		 				else 
		 					$working_days = date('j');
		 			} else {
		 				if(strtotime(date('t-m-Y', strtotime('01-'.$month))) < strtotime($row['start_working_date']))
		 					$working_days = 0;
		 				else if(strtotime('01-'.$month) < strtotime($row['start_working_date']))
		 					$working_days = date('t', strtotime('01-'.$month)) - date('j', strtotime($row['start_working_date'])) + 1;
		 				else 
		 					$working_days = date('t', strtotime('01-'.$month));
		 			}
	 			} else {
	 				$working_days = $absence_no_paid = $sunday_count = $overtime_hours = 0;
	 			}
	 			
	 			$bonus_days = $overtime_hours / 8 + $row['bonus_work_days'];

	 			$html .= '<td class="text-end bonus_work_days">'.($working_days - $absence_no_paid - $sunday_count - $no_count).'</td>';
	 			$html .= '<td class="text-end bonus_days">'.($bonus_days>0?'<a class="bonus_day fw-semibold cursor-pointer" title="Xem chi tiết" ln="'.$p.'">'.$bonus_days.'</a>':$bonus_days).'</td>';
	 			$html .= '<td class="text-end total_working_days fw-bold">'.($working_days - $absence_no_paid - $sunday_count - $no_count + $bonus_days).'</td>';
	 			$html .= '<input type="hidden" id="p_inf'.$p.'" value="'.$row['user_id'].'">';
	 			$html .= '</tr>';

	 			$total_working_days += ($working_days - $absence_no_paid - $sunday_count - $no_count + $overtime_hours / 8) + $row['bonus_work_days'];
	 			$total_normal_days += ($working_days - $absence_no_paid - $sunday_count - $no_count);
	 			$total_overtime += $overtime_hours / 8 + $row['bonus_work_days'];
				$p++; 	 
	 		} else {
				$total_ppl--;
			}

			for ($k = 1; $k <= $col_num; $k++) {
				// hiện tổng số người đang đi làm
				if ($p == $total_ppl) {
					$working_ppl_html .= '<td class="text-center"><b>' . $working_ppl[$k] . '</b></td>';
				}
			}
 		}

 		$html .= '
		<tr class="footer-tr">
			<td></td>
			<td class="text-center"><b>Tổng</b></td>
			'.$working_ppl_html.'
			<td class="text-end total_normal_days"><b>'.$total_normal_days.'</b></td>
			<td class="text-end total_overtime"><b>'.$total_overtime.'</b></td>
			<td class="text-end total_working_days fw-semibold"><b>'.$total_working_days.'</b></td>
		</tr>';	
 		return $html;
 	}

 	// hiện ngày nghỉ có lương hay không lương
 	// $exclude_days: bao gồm các ngày chủ nhật và lễ trước ngày hiện tại
 	// $all_exclude_days: tất cả ngày chủ nhật và lễ trong tháng
 	function getDateArr($absence_days, $exclude_days = array(), $search_month, $all_exclude_days) {
 		$absence = array();
 		if($absence_days != "0,") {
 			$absence_voucher = explode(';', $absence_days);

	 		for($k = 0; $k < count($absence_voucher); $k++) {
	 			if($absence_voucher[$k] != "0,") {
		 			$detail = explode(',', $absence_voucher[$k]);

			 		$fmonth = date('n', strtotime($detail[0]));
			 		$tmonth = date('n', strtotime($detail[1]));

			 		if($fmonth == $tmonth) {
			 			$fdate = date('j', strtotime($detail[0]));
			 			$tdate = date('j', strtotime($detail[1]));
			 			$no_paid = $detail[4];
			 		} else if($fmonth == $search_month) {
			 			$fdate = date('j', strtotime($detail[0]));
			 			$tdate = date('t', strtotime($detail[0]));
			 			$no_paid = $detail[4];
			 		} else if($tmonth == $search_month) {
			 			$fdate = 1;
			 			$tdate = date('j', strtotime($detail[1]));
			 			$no_paid = $tdate - $fdate - $detail[5] + 1;
			 		}

			 		for($i = $fdate; $i <= $tdate; $i++) {
		 				if(!in_array($i, $exclude_days)) {
		 					$absence['date']['date_number'][] = $i;

		 					if((float)$no_paid > 0) {
		 						if(!in_array($i, $all_exclude_days)) {
			 						$absence['date']['no_paid'][] = $i;
			 						if((float)$no_paid < 1 || date('j', strtotime($detail[3])) == $i && strtotime($detail[3]) != false && !empty($detail[2])) {
			 							$no_paid-=0.5;
			 							$absence['date']['no_paid_half'][$i] = 1;
			 						} else {
			 							$no_paid--;
			 							$absence['date']['no_paid_half'][$i] = 0;
			 						}
			 					}
		 					}

		 					$absence['date']['name'][$i] = $detail[6];
		 					$absence['date']['id'][$i] = $detail[7];
		 				}
		 			}
		 			if(strtotime($detail[3]) != false && !empty($detail[3])) {
		 				if($detail[2] == 1) {
		 					$absence['date']['morning'][] = date('j', strtotime($detail[3]));
		 				} else if($detail[2] == 2) {
		 					$absence['date']['afternoon'][] = date('j', strtotime($detail[3]));
		 				}
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
 				$overtime_date[date('j', strtotime($overtime[0]))]['day_plus'] += $overtime[2] / 8;
 				$overtime_date[date('j', strtotime($overtime[0]))]['hour'] += $overtime[2];
 				$overtime_date[date('j', strtotime($overtime[0]))]['id'] = $overtime[3];
 			}
 		}

 		return $overtime_date;
 	}
}
?>
