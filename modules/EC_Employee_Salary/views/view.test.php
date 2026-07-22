<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewtest extends SugarView
{

	function display()
	{
		// $this->updateWorkingDays();
		// $this->updateEfforts();
		// $this->updateMissingEfforts();
		// $this->createMonthSalary();
		// $this->updateBKSale();
		// $this->updateBkQty();
		// $this->updateBkTicket();
		// $this->createBKCompleted();
	}

	function updateWorkingDays()
	{
		global $db, $current_user;

		$today = date('Y-m-d');
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
				for($i = $first_sunday; $i <= $tdate; $i+=7) {
					if(strtotime($i.'-'.$month) >= strtotime($row['start_working_date']))
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

	function updateEfforts()
	{
		global $db, $current_user;

		// $fdate = date('Y-m-01');
		$fdate = '2024-03-01';
		// $tdate = date('Y-m-d');
		$tdate = '2024-03-31';


		// if(date('j', strtotime($fdate)) == 1) {
		// 	$fdate = date('Y-m-d', strtotime('-1 month'));
		// 	$tfdate = date('Y-m-t', strtotime($fdate));
		// }

		$sql = 'SELECT SUM(t.amount) AS amount
					 , SUM(t.overnight) AS overnight
					 , GROUP_CONCAT(t.overnight_bk) AS overnight_bk
					 , SUM(t.delivery) AS delivery
					 , SUM(t.bonus) AS bonus
					 , s.assigned_user_id
				FROM ec_employee_salary s 
				LEFT JOIN (
					SELECT SUM(CASE	
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 00:00:00" ) 
						 AND o.start_process_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" ) 
						 AND transfer_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8
						THEN qty * 20000 
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 21:00:00" ) 
						 AND o.start_process_time <= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 23:59:59" ) 
						 AND transfer_time < DATE_FORMAT( DATE_ADD( o.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" )
						 AND is_paid = 1  
						 AND o.booking_status = 8
						THEN qty * 20000
						ELSE 2000 END) AS amount
		 			 , SUM(CASE	
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 00:00:00" ) 
						 AND o.start_process_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" ) 
						 AND transfer_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8
						THEN qty * 20000 
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 21:00:00" ) 
						 AND o.start_process_time <= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 23:59:59" ) 
						 AND transfer_time < DATE_FORMAT( DATE_ADD( o.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8 
						THEN qty * 20000
						ELSE 2000 END) AS overnight
					 , GROUP_CONCAT(CASE	
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 00:00:00" ) 
						 AND o.start_process_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" ) 
						 AND transfer_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8
						THEN o.booking_id
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 21:00:00" ) 
						 AND o.start_process_time <= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 23:59:59" ) 
						 AND transfer_time < DATE_FORMAT( DATE_ADD( o.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8 
						THEN o.booking_id
						ELSE NULL END) AS overnight_bk
					 , 0 AS delivery
					 , 0 AS bonus
					 , o.assigned_user_id	
	 				FROM (
		 				SELECT SUM( dt.quantity ) * 20000 AS amount
		 					 , SUM( dt.quantity ) AS qty
		 					 , b.assigned_user_id, b.id AS booking_id
		 					 , b.name AS booking_name, b.booking_status
		 					 , DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered 
		 					 , DATE_ADD(
						 		(
						 			SELECT p.date_entered FROM ec_working_process p 
						 			WHERE p.deleted = 0 
						 			AND p.paid > 0 AND p.parent_id = b.id
						 			AND p.description IS NOT NULL AND LENGTH(p.description) > 0
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
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59"
						GROUP BY b.id
						HAVING start_process_time >= "' . $fdate . ' 00:00:00" 
							AND start_process_time <= "' . $tdate . ' 23:59:59" 
							AND start_process_time NOT BETWEEN DATE_FORMAT( start_process_time, "%Y-%m-%d 07:31:00") AND DATE_FORMAT( start_process_time, "%Y-%m-%d 20:59:59")
					) AS o
					GROUP BY o.assigned_user_id

					UNION
					SELECT SUM(p.ticket_delivery) * 20000 AS amount
						 , 0 AS overnight, NULL AS overnight_bk
						 , SUM(p.ticket_delivery) * 20000 AS delivery
						 , 0 AS bonus
						 , p.assigned_user_id 
					FROM ec_working_process p 
					INNER JOIN ec_flight_bookings b ON b.id = p.parent_id AND b.deleted = 0
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
					WHERE p.deleted = 0 AND p.ticket_delivery = 1  
					GROUP BY p.assigned_user_id

					UNION 
					SELECT SUM(bonus_amount) AS amount
						 , 0 AS overnight, NULL AS overnight_bk
						 , 0 AS delivery
						 , SUM(bonus_amount) AS bonus
						 , assigned_user_id			
					FROM ec_salary_details
					WHERE deleted = 0
					AND type = "bonus"
					AND voucher_date >= "' . $fdate . '"
					AND voucher_date <= "' . date('Y-m-d', strtotime($tdate)) . '"
					GROUP BY assigned_user_id

					-- Giao thuc pham ben PT
					UNION ALL
					SELECT SUM(p.ticket_delivery) * 20000 AS amount
						, 0 AS overnight
						, NULL AS overnight_bk
						, SUM(p.ticket_delivery) * 20000 AS delivery
						, 0 AS bonus
						, p.assigned_user_id 
						FROM ec_working_process p 
						INNER JOIN ec_receipt_voucher rv ON rv.id = p.parent_id AND rv.deleted = 0
						AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
						AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
						WHERE p.deleted = 0 AND p.ticket_delivery = 1  
					GROUP BY p.assigned_user_id
				) AS t ON s.assigned_user_id = t.assigned_user_id
				WHERE s.month = ' . date('n', strtotime($fdate)) . '
				AND s.year = ' . date('Y', strtotime($fdate)) . ' 
				AND s.deleted = 0
				GROUP BY s.assigned_user_id';


		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// 	die();
		// }


		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {

			if (!empty($row['overnight_bk'])) {
				// tính ds bk cú đêm được tính 20k
				$overnight_bk_arr = explode(',', $row['overnight_bk']);
				$total_profit = $this->calculateTotalBKProfit($row['assigned_user_id'], $fdate, $tdate, $overnight_bk_arr);
			} else $total_profit = 0;

			// cập nhật cột nỗ lực
			// $sql2 = '
			// 	UPDATE ec_employee_salary 
			// 	SET effort = '.(empty($row['amount'])?0:$row['amount']).'
			// 	  , overnight = '.(empty($row['overnight'])?0:$row['overnight']). '
			// 	  , delivery = '.(empty($row['delivery'])?0:$row['delivery']). '
			// 	  , profit_overnight = ' . $total_profit . ' 
			// 	WHERE deleted = 0 
			// 	AND assigned_user_id = "' . $row['assigned_user_id'] . '" 
			// 	AND month = "'.date('n', strtotime($fdate)).'" 
			// 	AND year = "'.date('Y', strtotime($fdate)).'" 
			// 	AND is_approved = 0';

			$sql2 = '
				UPDATE ec_employee_salary 
				SET effort = ' . (empty($row['amount']) ? 0 : $row['amount']) . '
				  , delivery = ' . (empty($row['delivery']) ? 0 : $row['delivery']) . '
				WHERE deleted = 0 
				AND assigned_user_id = "' . $row['assigned_user_id'] . '" 
				AND month = "' . date('n', strtotime($fdate)) . '" 
				AND year = "' . date('Y', strtotime($fdate)) . '" 
				AND is_approved = 0';
			// $db->query($sql2);
		}

		return true;
	}

	function calculateTotalBKProfit($user_id, $from_date, $to_date, $bk_arr)
	{
		global $db;
		$sql = '
			SELECT 
				SUM( bk.total_amount ) / COUNT( dt.id ) 
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
		$res = $db->query($sql);
		$total = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$total += $row['doanhso'];
		}
		return $total;
	}

	function updateMissingEfforts()
	{
		global $db;

		// xoá hết các dòng bonus đã cộng thêm vào tháng hiện tại
		$sql_d = 'UPDATE ec_salary_details SET deleted = 1 WHERE deleted = 0 AND name = "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '"';
		$db->query($sql_d);

		// thêm bonus nỗ lực còn thiếu lại
		$fdate = date('Y-m-01', strtotime('-1 month'));
		$tdate = date('Y-m-t', strtotime('-1 month'));
		$sql = 'SELECT SUM(t.amount) AS amount
					 , SUM(t.saved_overnight) AS saved_overnight
				 	 , SUM(t.saved_delivery) AS saved_delivery
					 , t.assigned_user_id
				FROM (
					SELECT SUM(CASE	
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 00:00:00" ) 
						 AND o.start_process_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" ) 
						 AND transfer_time < DATE_FORMAT( o.start_process_time, "%Y-%m-%d 07:31:00" )
						 AND is_paid = 1 
						 AND o.booking_status = 8
						THEN qty * 20000 
						WHEN o.start_process_time >= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 21:00:00" ) 
						 AND o.start_process_time <= DATE_FORMAT( o.start_process_time, "%Y-%m-%d 23:59:59" ) 
						 AND transfer_time < DATE_FORMAT( DATE_ADD( o.start_process_time, INTERVAL 1 DAY ), "%Y-%m-%d 07:30:00" )
						 AND is_paid = 1  
						 AND o.booking_status = 8
						THEN qty * 20000
						ELSE 2000 END) AS amount
					 , 0 AS saved_overnight
					 , 0 AS saved_delivery
					 , o.assigned_user_id	
	 				FROM (
		 				SELECT SUM( dt.quantity ) * 20000 AS amount
		 					 , SUM( dt.quantity ) AS qty
		 					 , b.assigned_user_id, b.id AS booking_id
		 					 , b.name AS booking_name, b.booking_status
		 					 , DATE_ADD(b.date_entered, INTERVAL 7 HOUR) AS date_entered
		 					 , DATE_ADD(
						 		(
						 			SELECT p.date_entered FROM ec_working_process p 
						 			WHERE p.deleted = 0 
						 			AND p.called > 0 AND p.parent_id = b.id 
						 			GROUP BY p.parent_id
						 		)
		 					 	, INTERVAL 7 HOUR) AS start_process_time 
		 					 , DATE_ADD(
						 		(
						 			SELECT p.date_entered FROM ec_working_process p 
						 			WHERE p.deleted = 0 
						 			AND p.paid > 0 AND p.parent_id = b.id
						 			AND p.description IS NOT NULL AND LENGTH(p.description) > 0
						 			GROUP BY p.parent_id
						 		)
		 					 	, INTERVAL 7 HOUR) AS transfer_time 
		 					 , 0 AS bonus, b.is_paid
						FROM
							ec_booking_details dt 
							INNER JOIN ec_flight_bookings b ON b.id = dt.booking_id AND b.deleted = 0
						WHERE
							dt.deleted = 0 
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00"
							AND DATE_ADD(b.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59"
						GROUP BY b.id
						HAVING start_process_time >= "' . $fdate . ' 00:00:00" 
						AND start_process_time <= "' . $tdate . ' 23:59:59" 
						AND start_process_time NOT BETWEEN DATE_FORMAT( start_process_time, "%Y-%m-%d 07:31:00") 
						AND DATE_FORMAT( start_process_time, "%Y-%m-%d 20:59:59")
					) AS o
					GROUP BY o.assigned_user_id
					UNION
					SELECT SUM(p.ticket_delivery) * 20000 AS amount
						 , 0 AS saved_delivery
					 	 , 0 AS saved_effort
						 , p.assigned_user_id 
					FROM ec_working_process p 
					INNER JOIN ec_flight_bookings b ON b.id = p.parent_id AND b.deleted = 0
					AND b.booking_status IN (3, 7, 8)
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) >= "' . $fdate . ' 00:00:00" 
					AND DATE_ADD(p.date_entered, INTERVAL 7 HOUR) <= "' . $tdate . ' 23:59:59" 
					WHERE p.deleted = 0 AND p.ticket_delivery = 1  
					GROUP BY p.assigned_user_id

					UNION
					SELECT 0 AS amount
						 , overnight AS saved_overnight
					 	 , delivery AS saved_delivery
						 , assigned_user_id			
					FROM ec_employee_salary
					WHERE deleted = 0
					AND month = "' . date('n', strtotime($fdate)) . '"
					AND year = "' . date('Y', strtotime($fdate)) . '"
					GROUP BY assigned_user_id
				) AS t GROUP BY t.assigned_user_id';
		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$bonus = (int)$row['amount'] - (int)$row['saved_overnight'] - (int)$row['saved_delivery'];
			if ($bonus != 0) {
				// kiểm tra đã có chưa, nếu chưa có thì insert, còn nếu có rồi thì update
				$sql2 = 'INSERT INTO ec_salary_details(id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, assigned_user_id, bonus_amount, reason, type, voucher_date) VALUES (uuid(), "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '", "' . date('Y-m-d H:i:s') . '", "' . date('Y-m-d H:i:s') . '", 1, 1, "Bổ sung nỗ lực còn thiếu của tháng ' . date('n', strtotime('-1 month')) . ' năm ' . date('Y', strtotime('-1 month')) . '", 0, "' . $row['assigned_user_id'] . '", ' . $bonus . ', "Khac", "bonus", "' . date('Y-m-01') . '")';
				$db->query($sql2);
			}
		}

		return true;
	}

	function createMonthSalary()
	{
		global $db;

		// $this_m = '2024-01-01';
		$this_m = date('Y-m-01');
		$end_date = date('Y-m-t', strtotime($this_m));

		// từ ngày 01 tháng trước
		$from_date_prev_m = date('Y-m-01', strtotime('-1 month', strtotime($this_m)));

		// kế thừa bảng lương từ tháng trước
		$sql = '
			SELECT 
				s.basic_salary, s.efficient_wage, s.gas_allowance
				, s.lunch_allowance, s.tele_allowance
				, s.responsible_allowance
				, s.seniority_allowance, s.other_allowance1
				, s.other_allowance2
				, s.social_insurance, s.assigned_user_id
				, s.name
			FROM ec_employee_salary s
			INNER JOIN (
				SELECT usr.id, his.status AS his_stt
				FROM users usr
				INNER JOIN ec_workhistory his
				ON his.assigned_user_id = usr.id
				AND his.deleted = 0
				AND DATE_FORMAT(his.date_start, "%Y-%m-01") <= "' . $this_m . '"
				AND LAST_DAY(IFNULL(his.date_end, "' . $end_date . '")) >= "' . $end_date . '"
				WHERE usr.deleted = 0
			) AS u ON u.id = s.assigned_user_id
			WHERE u.his_stt <> "InActive" AND s.deleted = 0
			AND s.month = "' . date('n', strtotime($from_date_prev_m)) . '"
			AND s.year = "' . date('Y', strtotime($from_date_prev_m)) . '"';

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			// nếu có lệnh tạo mới, xoá hồ sơ lương đã tạo cho tháng này
			// tạo lại hồ sơ mới
			$sql = 'UPDATE ec_employee_salary SET deleted = 1
					WHERE assigned_user_id = "' . $row['assigned_user_id'] . '"
					AND month = "' . date('n', strtotime($this_m)) . '"
					AND year = "' . date('Y', strtotime($this_m)) . '"';
			// $db->query($sql);

			// tạo hồ sơ lương mới cho tháng mới
			// $salary = new EC_Employee_Salary;
			// $salary->name 					= $row['name'];
			// $salary->assigned_user_id 		= $row['assigned_user_id'];
			// $salary->month 				= date('n', strtotime($this_m));
			// $salary->year 					= date('Y', strtotime($this_m));
			// $salary->basic_salary 			= (int)$row['basic_salary'];
			// $salary->efficient_wage 			= (int)$row['efficient_wage'];
			// $salary->gas_allowance 			= (int)$row['gas_allowance'];
			// $salary->lunch_allowance 		= (int)$row['lunch_allowance'];
			// $salary->tele_allowance 			= (int)$row['tele_allowance'];
			// $salary->responsible_allowance 	= (int)$row['responsible_allowance'];
			// $salary->seniority_allowance 		= (int)$row['seniority_allowance'];
			// $salary->other_allowance1 		= (int)$row['other_allowance1'];
			// $salary->other_allowance2 		= (int)$row['other_allowance2'];
			// $salary->social_insurance 		= (int)$row['social_insurance']; 
			// $salary->save();
		}
	}

	function updateBKSale()
	{
		global $db;
		$sql = '
			SELECT * FROM ec_flight_bookings
			WHERE deleted = 0 
			AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "2022-09-19 00:00:00" 
			AND booking_status = 8
		';
		$res = $db->query($sql);

		while ($row = $db->fetchByAssoc($res)) {
			$bk = new EC_Flight_Bookings;
			$total_amt = $bk->calculateBKTotalAmt($row['id']);
			$sql2 = '
				UPDATE ec_working_process
				SET total_amount = ' . (int)$total_amt . '
				WHERE deleted = 0 AND paid > 0
				AND parent_id = "' . $row['id'] . '"
			';
			$db->query($sql2);
		}
	}

	function updateBkQty()
	{
		global $db;
		$sql = '
			SELECT * FROM ec_flight_bookings
			WHERE deleted = 0 
			AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "2022-09-19 00:00:00" 
			AND booking_status = 8
		';
		$res = $db->query($sql);

		while ($row = $db->fetchByAssoc($res)) {
			$bk = new EC_Flight_Bookings;
			$total_ticket = $bk->calculateBookingTicketQty($row['id']);
			$sql2 = '
				UPDATE ec_working_process
				SET total_ticket = ' . (int)$total_ticket . '
				WHERE deleted = 0 AND paid > 0
				AND parent_id = "' . $row['id'] . '"
			';
			$db->query($sql2);
		}
	}

	function updateBkTicket()
	{
		global $db;
		$sql = '
			SELECT SUM(total_qty) AS total_qty, assigned_user_id
			FROM ec_flight_bookings
			WHERE deleted = 0 
			AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "2022-09-19 00:00:00" 
			AND booking_status = 8 
			GROUP BY assigned_user_id
		';
		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$sql2 = '
				UPDATE users SET total_ticket = ' . $row['total_qty'] . '
				WHERE id = "' . $row['assigned_user_id'] . '"
			';
			$db->query($sql2);
		}
	}

	// tính các ngày CN giữa 2 ngày
	function calSundaysBetweenTwoDays($from_date, $to_date)
	{
		$total_sunday = 0;
		$date_range = myGetDateRange($from_date, $to_date);
		foreach ($date_range as $val) {
			if (date('l', strtotime($val)) == 'Sunday') {
				$total_sunday += 1;
			}
		}
		return $total_sunday;
	}
}
