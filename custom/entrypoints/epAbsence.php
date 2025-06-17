<?php
	global $db, $current_user, $app_list_strings; 

	// kiểm tra nghỉ phép trước khi lưu
	if($_POST['for'] == 'checkAbsenceCondition') {
		if(strtotime($_POST['from_date']) == false) {
			echo 'Ô Từ ngày không đúng định dạng';
			exit;
		} else if(strtotime($_POST['to_date']) == false) {
			echo 'Ô Đến ngày không đúng định dạng';
			exit;
		} else if(strtotime($_POST['from_date']) > strtotime($_POST['to_date'])) {
			echo 'Từ ngày lớn hơn Đến ngày';
 			exit;
		} else if((strtotime($_POST['from_date']) < strtotime(date('d-m-Y')) || strtotime($_POST['to_date']) < strtotime(date('d-m-Y'))) && !is_admin($current_user) && $current_user->title != 'QuanLy') {
			echo 'Bạn phải xin nghỉ phép trước 1 ngày.';
 			exit;
		} else if(date('Y', strtotime($_POST['from_date'])) != date('Y', strtotime($_POST['to_date']))) {
			echo 'Từ ngày và Đến ngày không thể khác năm. Vui lòng tách làm 2 phiếu.';
 			exit;
		} else if(empty($_POST['assigned_user_id'])) {
			echo 'Bạn chưa chọn giao cho.';
 			exit;
		}else {
			$absence_days = (strtotime($_POST['to_date']) - strtotime($_POST['from_date'])) / 86400;

			// nếu trong khoảng thời gian từ ngày và đến ngày có chủ nhật thì cảnh báo để sửa lại
			if(date('m', strtotime($_POST['from_date'])) != date('m', strtotime($_POST['to_date']))) {
				$count_sunday = countSunday($_POST['from_date'], $_POST['from_date'], date('t-m-Y', strtotime($_POST['from_date']))) + countSunday('01-'.date('m-Y', strtotime($_POST['to_date'])), '01-'.date('m-Y', strtotime($_POST['to_date'])), $_POST['to_date']);

				$count_holiday = countHoliday(date('d', strtotime($_POST['from_date'])), date('t-m-Y', strtotime($_POST['from_date'])), date('m', strtotime($_POST['from_date']))) + countHoliday('01', date('d', strtotime($_POST['to_date'])), date('m', strtotime($_POST['to_date'])));
			} else {
				$count_sunday = countSunday($_POST['from_date'], $_POST['from_date'], $_POST['to_date']);
				$count_holiday = countHoliday(date('d', strtotime($_POST['from_date'])), date('d', strtotime($_POST['to_date'])), date('m', strtotime($_POST['from_date'])));
			}

			if((($absence_days + 1) - $_POST['absence_days'] - $count_sunday - $count_holiday) < 0 || (($absence_days + 1) - $_POST['absence_days'] - $count_sunday - $count_holiday) > 0.5) {
				echo 'Số ngày nghỉ khác với hiệu số Từ ngày và Đến ngày. (Số ngày nghỉ không được tính ngày chủ nhật và lễ tết)';

				exit;
			} else if((($absence_days + 1) - $_POST['absence_days'] - $count_sunday - $count_holiday) == 0.5) {
				if(empty($_POST['partofday'])) {
					echo 'Bạn có đăng ký nghỉ nửa buổi. Vui lòng chọn buổi sáng hay chiều và ngày nghỉ nửa buổi.';
	
					exit;
				} else {
					if(strtotime($_POST['part_date']) == false) {
						echo 'Ô ngày phần nửa buổi sai định dạng';
		
						exit;
					} else {
						if(strtotime($_POST['part_date']) > strtotime($_POST['to_date']) 
						|| strtotime($_POST['part_date']) < strtotime($_POST['from_date'])) {
							echo 'Ngày đăng ký nghỉ nửa buổi không nằm trong phạm vi Từ ngày và Đến ngày';
			
							exit;
						} else if(countSunday($_POST['part_date'], $_POST['part_date'], $_POST['part_date']) > 0) {
							echo 'Ngày đăng ký nghỉ nửa buổi không thể là ngày chủ nhật.';
			
							exit;
						} else if(countHoliday(date('d', strtotime($_POST['part_date'])), date('d', strtotime($_POST['part_date'])), date('m', strtotime($_POST['part_date']))) > 0) {
							echo 'Ngày đăng ký nghỉ nửa buổi không thể là ngày lễ tết';
			
							exit;
						}
					}
				}
			} else {
				if(strtotime($_POST['part_date']) != false) {
					echo 'Bạn không có nghỉ 0.5 ngày. Vui lòng chọn không có.';
					exit;
				} 
			}

			// kiểm tra ngày nghỉ có trùng với các phiếu nghỉ phép trước đó
			if($_POST['partofday'] == 0){
				$sql_partofday = '';
			} else {
				$sql_partofday = 'AND ((part_date IS NOT NULL OR part_date <> "") AND partofday = "' . $_POST['partofday'] . '")';

			}

			$sql_dup = 'SELECT IF(COUNT(*) > 0, 1, 0)
						FROM ec_leaveabsences 
						WHERE deleted = 0 
						AND assigned_user_id = "' . $_POST['assigned_user_id'] . '" 
						AND (
							(from_date <= "'.date('Y-m-d', strtotime($_POST['from_date'])).'" AND to_date >= "'.date('Y-m-d', strtotime($_POST['from_date'])).'")
							OR (from_date <= "'.date('Y-m-d', strtotime($_POST['to_date'])).'" AND to_date >= "'.date('Y-m-d', strtotime($_POST['to_date'])).'")
							OR (from_date >= "'.date('Y-m-d', strtotime($_POST['from_date'])).'" AND from_date <= "'.date('Y-m-d', strtotime($_POST['to_date'])).'")
							OR (to_date >= "'.date('Y-m-d', strtotime($_POST['from_date'])).'" AND to_date <= "'.date('Y-m-d', strtotime($_POST['to_date'])).'")
						) 
						AND status <> 0 '.$sql_partofday.'
						';
			$is_dup = $db->getOne($sql_dup);

			// if($GLOBALS['current_user']->user_name == 'hungnh'){
			// 	pr($sql_dup);
			// }

			if($is_dup) {
				echo "Trùng ngày nghỉ phép với các phiếu cũ. Vui lòng kiểm tra lại.";
				exit;
			}
		} 
		echo 1;
	}

	function countSunday($date, $from_date, $to_date) {
		$fdate = date('d', strtotime($from_date));
		$tdate = date('d', strtotime($to_date));

		$date = '01-'.date('m-Y', strtotime($date));
		$first_sunday = 7 - date('N', strtotime($date)) + 1;
		$last_day_in_month = date('t', strtotime($date));
		for($i = $first_sunday; $i <= $last_day_in_month; $i+=7) {
			$sunday[] = $i;
		}

		$count = 0;
		for($k = $fdate; $k <= $tdate; $k++) {
			if(in_array($k, $sunday)) {
				$count++;
			}
		}

		return $count;
	}

	function countHoliday($from_date, $to_date, $month) {
		$holidays = array(
			
		);

		$count = 0;
		for($i = $from_date; $i <= $to_date; $i++) {
			if(in_array($i.'-'.$month, $holidays)) {
				$count++;
			}
		}
		
		return $count;
	}

	// hiện giảm trừ / nỗ lực của nhân viên trong tháng
	if($_POST['for'] == 'showAmountDetail') {
		$from_date 	= date('Y-m-01', strtotime($_POST['year'].'-'.$_POST['month'].'-01'));
		$to_date 		= date('Y-m-t', strtotime($_POST['year'].'-'.$_POST['month'].'-01'));
		$i 			= 1;
		$total 		= 0;

		// phụ cấp
		if($_POST['type'] == 'allowance') {
			$sql = '
				SELECT gas_allowance, lunch_allowance, tele_allowance, responsible_allowance
				     , seniority_allowance, other_allowance1, other_allowance2
					 , (gas_allowance + lunch_allowance + tele_allowance + responsible_allowance + seniority_allowance + other_allowance1 + other_allowance2) AS total
				FROM ec_employee_salary 
				WHERE deleted = 0 
				AND month = ' . $_POST['month'] . ' 
				AND year = ' . $_POST['year'] . ' 
				AND assigned_user_id = "' . $_POST['assigned_user_id'] . '"';
			$res = $db->query($sql);
			$row = $db->fetchByAssoc($res);

			$body = '
				<tr>
					<td width="55%" class="text-center"><b>Loại phụ cấp</b></td>
					<td class="text-center"><b>Giá trị</b></td>
				</tr>
				<tr>
					<td>Phụ cấp xăng xe</td>
					<td class="text-end">' . format_number($row['gas_allowance']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp cơm trưa</td>
					<td class="text-end">' . format_number($row['lunch_allowance']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp điện thoại</td>
					<td class="text-end">' . format_number($row['tele_allowance']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp trách nhiệm</td>
					<td class="text-end">' . format_number($row['responsible_allowance']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp thâm niên</td>
					<td class="text-end">' . format_number($row['seniority_allowance']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp khác 1</td>
					<td class="text-end">' . format_number($row['other_allowance1']) . '</td>
				</tr>
				<tr>
					<td>Phụ cấp khác 2</td>
					<td class="text-end">' . format_number($row['other_allowance2']) . '</td>
				</tr>
				<tr class="footer-tr">
					<td class="text-end"><b>Tổng</b></td>
					<td class="text-end"><b>' . format_number($row['total']) . '</b></td>
				</tr>';
			echo json_encode(array('body' => $body));
		} else {
			$sql_con = '';
			// nỗ lực
			if($_POST['type'] == 'bonus') {
				// $html = '<tr>
				// 	<td class="text-center" id="detail_order'.$i.'">'.$i.'</td>
				// 	<td class="text-center">'.date('m-Y', strtotime($_POST['year'].'-'.$_POST['month'].'-01')).'</td>
				// 	<td class="text-end">
				// 		'.$_POST['overnight'].'
				// 		<input type="hidden" id="detail_amount'.$i.'" value="'.unformat_number($_POST['overnight']).'">
				// 		<input type="hidden" id="detail_deleted'.$i++.'" class="detail_deleted" value="0">
				// 	</td>
				// 	<td class="text-center">Cú đêm</td>
				// 	<td></td>
				// 	<td></td>
				// 	<td></td>
				// </tr>';
				$html = '<tr>
					<td class="text-center" id="detail_order'.$i.'">'.$i.'</td>
					<td class="text-center">'.date('m-Y', strtotime($_POST['year'].'-'.$_POST['month'].'-01')).'</td>
					<td class="text-end">
						'.$_POST['delivery'].'
						<input type="hidden" id="detail_amount'.$i.'" value="'.unformat_number($_POST['delivery']).'">
						<input type="hidden" id="detail_deleted'.$i++.'" class="detail_deleted" value="0">
					</td>
					<td class="text-center">Giao vé</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>';
				// $total += unformat_number($_POST['overnight']) + unformat_number($_POST['delivery']);
				$total += unformat_number($_POST['delivery']);
			} 
			$sql = '
				SELECT dt.*, u.user_name
				FROM ec_salary_details dt
				LEFT JOIN users u ON u.id = dt.created_by 
				WHERE dt.deleted = 0 
				AND dt.assigned_user_id ="'.$_POST['assigned_user_id'].'" 
				AND dt.type = "'.$_POST['type'].'" 
				AND dt.voucher_date 
				BETWEEN "' . date('Y-m-d', strtotime($from_date)) . '" 
				AND "' . date('Y-m-d', strtotime($to_date)) . '"
				'.$sql_con.'
				ORDER BY dt.voucher_date';

			$res = $db->query($sql);
			while($row = $db->fetchByAssoc($res)) {
				if($_POST['type'] == 'minus') {
					$detail_amount = (int)$row['minus_amount'];
				} else {
					$detail_amount = (int)$row['bonus_amount'];
				}
				$html .= '<tr id="detail_line'.$i.'">
					<td class="text-center" id="detail_order'.$i.'">'.$i.'</td>
					<td class="text-center">'.date('d-m-Y', strtotime($row['voucher_date'])).'</td>
					<td class="text-end">'.format_number($detail_amount).'</td>
					<td class="text-center">'.$app_list_strings['salary_minus_list'][$row['reason']].'</td>
					<td>'.$row['description'].'</td>
					<td class="text-center">'.$row['user_name'].'</td>';

				if(!isset($_POST['detail'])) { 
					$html .= '<td>
								<button title="Xóa" type="button" onclick="markRowDeleted(\'detail_line\', \'detail_deleted\', \'detail_order\', '.$i.')" class="button-remove-in-edit"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg></button>
								<input type="hidden" id="detail_deleted'.$i.'" name="detail_deleted[]" class="detail_deleted" value="0">
								<input type="hidden" id="amount_detail'.$i.'" name="amount_detail[]" value="'.$row['id'].'">
								<input type="hidden" id="date_detail'.$i.'" name="date_detail[]" value="'.date('d-m-Y', strtotime($row['voucher_date'])).'">
								<input type="hidden" id="detail_amount'.$i.'" name="detail_amount[]" value="'.$detail_amount.'">
								<input type="hidden" id="reason'.$i.'" name="reason[]" value="'.$row['reason'].'">
								<input type="hidden" id="detail_description'.$i.'" name="detail_description[]" value="'.$row['description'].'">
							</td>';
				}
				$html .= '</tr>';
				$i++;
				$total += $detail_amount;
			}

			echo json_encode(array('html' => $html, 'count' => $i, 'total' => format_number($total)));
		}
	}

	// lưu ghi chú cho bảng báo cáo hoàn ứng HCNS
	if($_POST['for'] == 'saveAdvanceNote') {
		$user_id 	= (isset($_POST['user']) && !empty($_POST['user'])) ? trim($_POST['user']) : '';
		$note 	= (isset($_POST['note']) && !empty($_POST['note'])) ? trim($_POST['note']) : '';

		// Chỉ có admin mới retrieve tới table User
		if(is_admin($current_user)){
			$user = new User;
			$user->retrieve($user_id);
			$user->advance_note = $note;
			$user->save();
		} else {
			$sql = '
				UPDATE users
				SET advance_note = "'.$note.'", date_modified = "'.date('Y-m-d H:i:s').'"
				WHERE id = "'.$user_id.'"
				AND deleted = 0';
			$db->query($sql);
		}
	}

	// hiển thị chi tiết ngày công bổ sung 
	// hoặc công làm ngoài giờ
	if($_POST['for'] == 'getUserBonusDaysDetail') {
		$html .= '';

		$from_date = date('Y-m-d', strtotime('01-'.$_POST['month'].'-'.$_POST['year']));
		$to_date = date('Y-m-t', strtotime($from_date));
		$sql = 'SELECT dt.register_date, o.approved_date, o.name, o.id
					 , (dt.working_hour / 8) AS bonus_days
					 , dt.assigned_user_id, "" AS bonus_month 
				FROM ec_workingovertimedetails dt
				INNER JOIN ec_workingovertimes o
				ON o.id = dt.ec_workingovertimes_id_c
				AND o.status = 2
				WHERE dt.deleted = 0 
				AND dt.assigned_user_id = "'.$_POST['usr'].'"
				AND dt.register_date >= "'.$from_date.'"
				AND dt.register_date <= "'.$to_date.'"
				UNION
				SELECT dt.register_date, o.approved_date, o.name, o.id
					 , (dt.working_hour / 8) AS bonus_days
					 , dt.assigned_user_id, o.bonus_month
				FROM ec_workingovertimedetails dt
				INNER JOIN ec_workingovertimes o
				ON o.id = dt.ec_workingovertimes_id_c
				AND o.status = 2
				WHERE dt.deleted = 0 
				AND dt.assigned_user_id = "'.$_POST['usr'].'"
				AND o.bonus_month >= "'.$from_date.'"
				AND o.bonus_month <= "'.$to_date.'"';

		$res = $db->query($sql);
		$total_bonus_days = 0;
		while($row = $db->fetchByAssoc($res)) {
			$html .= '<tr>
				<td class="text-center">'.date('d-m-Y', strtotime($row['register_date'])).'</td>
				<td class="text-center">'.(empty($row['bonus_month'])?'':date('m-Y', strtotime($row['bonus_month']))).'</td>
				<td class="text-center"><a href="index.php?module=EC_WorkingOverTimes&action=DetailView&record='.$row['id'].'" target="_blank">'.$row['name'].'</a></td>
				<td class="text-end">'.(float)$row['bonus_days'].'</td>
			</tr>';

			$total_bonus_days += (float)$row['bonus_days'];
		}

		$html .= '<tr class="footer-tr">
			<td colspan="3" class="text-end"><b>Tổng</b></td>
			<td class="text-end"><b>'.$total_bonus_days.'</b></td>
		</tr>';

		echo $html;
	}

	// lưu thông tin không đạt ds
	if(isset($_POST['for']) && $_POST['for'] == 'updateMinusIncome') {
		$sql = '
			UPDATE ec_employee_salary
			SET minus_income = ' . $_POST['minus_inc'] . '
			WHERE assigned_user_id = "' . $_POST['assigned_user_id'] . '" 
			AND month = "' . $_POST['month'] . '" AND year = "' . $_POST['year'] . '"
			AND is_approved = 0 AND deleted = 0
		';
		$db->query($sql);
	}

