<?php
class EC_LeaveAbsencesLogicHook
{
	public function checkBeforeDelete($focus, $event, $argument)
	{
		if ($focus->status == 2) {
			header("Location: index.php?module=EC_LeaveAbsences&action=DetailView&record=" . $focus->id);
			exit;
		}
	}

	// tính số ngày phép năm còn lại, số ngày nghỉ có lương, không lương
	function remark($focus, $event, $argument)
	{

		if ($focus->status == 2) {
			// nếu loại phép không lương, thì tính tất cả ngày phép không lương
			// kiểm tra số ngày phép đã sử dụng cho tới trước từ ngày

			$sql_used = '
					SELECT SUM(used_leave_days_curr_m + used_leave_days_next_m)
					FROM ec_leaveabsences 
					WHERE deleted = 0 AND status = 2 
					AND assigned_user_id = "' . $focus->assigned_user_id . '"
					AND id <> "' . $focus->id . '" 
					AND from_date >= "' . date('Y', strtotime($focus->to_date)) . '-01-01"
					AND to_date <= "' . date('Y', strtotime($focus->to_date)) . '-12-31"';
				
			$used_leave_days = $focus->db->getOne($sql_used);

			// lấy ngày vào làm, ngày nghỉ còn lại của người xin nghỉ
			$sql_u = ' 
					SELECT start_working_date 
					FROM users WHERE id = "' . $focus->assigned_user_id . '"';

			$res_u = $focus->db->query($sql_u);

			$row_u = $focus->db->fetchByAssoc($res_u);
			$start_date = new DateTime($row_u['start_working_date']);
			$now = new DateTime();
			$working_time = date_diff($start_date, $now);
			$working_months = $working_time->y * 12 + $working_time->m;

			if ($working_months >= 12) {
				// kt ngày bắt đầu có phép
				$date_start_has_leave = $start_date->modify('+1 year');
				// tháng bắt đầu có phép nằm trong năm hiện tại thì tính lại số phép
				if ($date_start_has_leave->format('Y') == date('Y', strtotime($focus->from_date))) {
					// số phép tính từ ngày bắt đầu có phép
					$to_date_dt = new DateTime($focus->to_date);
					$leave_days_dt = date_diff($date_start_has_leave, $to_date_dt);
					$remain_leave_days = $leave_days_dt->y * 12 + $leave_days_dt->m + 1 - $used_leave_days;
				} else {
					// tổng phép năm reset mỗi năm
					// và tăng dần theo từng tháng trong năm
					// không ứng trước
					$remain_leave_days = date('n', strtotime($focus->to_date)) - $used_leave_days;
				}
			} else {
				$remain_leave_days = 0;
			}

			// Các User không có phép năm
			$user_partime_arr = array(
				'de780bcd-2723-084b-e4af-56610c219aab', //hadtt
				'd14007fa-aaed-cac7-9a00-62cfccf58d5a', //ducnx
				'af285bfa-8bbf-dd0b-4394-64015e84ab94', //nghị
			);
			if(in_array($focus->assigned_user_id, $user_partime_arr) || $remain_leave_days < 0){
				$remain_leave_days = 0;
			};
			
			// User Trang Đài không có phép năm 2023
			if($focus->assigned_user_id == 'b4ff32c8-8a1e-0648-b20d-63437ab44554' && date('Y') <= 2023){
				$remain_leave_days = 0;
			}

			// kiểm tra số ngày nghỉ quy định của loại phép
			$sql_day_off = '
					SELECT day_off FROM ec_leaveabsencetypes 
					WHERE deleted = 0 
					AND id = "' . $focus->ec_leaveabsencetypes_id_c . '"';
			$day_off = $focus->db->getOne($sql_day_off);

			// nếu là loại nghỉ "không phép" thì tất cả đều là nghỉ không lương
			if ($focus->ec_leaveabsencetypes_id_c == '9b56007c-7d2f-3abf-40b7-5fd8199c6143') {
				$focus->paid_days 				= 0;
				$focus->no_paid_days 			= $focus->absence_days;
				$focus->remain_leave_days 		= $remain_leave_days;
				$focus->used_leave_days_curr_m 	= 0;
				$focus->used_leave_days_next_m 	= 0;
				
				// nếu loại phép khác phép năm
			} else if ($focus->ec_leaveabsencetypes_id_c != 'c9b612e9-4dcf-c759-66f4-5fd819fda69f') {
				if ($focus->absence_days > $day_off) {
					$focus->paid_days = $day_off;
					$left_day = $focus->absence_days - $day_off;
					if ($remain_leave_days > 0) {
						if ($remain_leave_days >= $left_day) {
							$focus->remain_leave_days = $remain_leave_days - $left_day;
							$focus->no_paid_days = 0;
							$focus->used_leave_days = $left_day;
						} else {
							$focus->remain_leave_days = 0;
							$focus->no_paid_days = $left_day - $remain_leave_days;
							$focus->used_leave_days = $remain_leave_days;
						}
					} else {
						$focus->no_paid_days = $left_day;
						$focus->used_leave_days = 0;
					}
				} else {
					$focus->paid_days = $focus->absence_days;
					$focus->no_paid_days = 0;
					$focus->remain_leave_days = $remain_leave_days;
					$focus->used_leave_days = 0;
				}
			} else {
				if ($remain_leave_days > $focus->absence_days) {
					$focus->paid_days = $focus->absence_days;
					$focus->no_paid_days = 0;
					$focus->remain_leave_days = $remain_leave_days - $focus->absence_days;
					// $focus->used_leave_days = $focus->absence_days;
					if (date('m', strtotime($focus->from_date)) != date('m', strtotime($focus->to_date))) {
						$leave_days_curr_m = myGetDayBetween($focus->from_date, date('t-m-Y', strtotime($focus->from_date))) + 1;
						$focus->used_leave_days_curr_m = $leave_days_curr_m;
						$focus->used_leave_days_next_m = $focus->absence_days - $leave_days_curr_m;
					} else {
						$focus->used_leave_days_curr_m = $focus->absence_days;
						$focus->used_leave_days_next_m = 0;
					}
				} else {
					$focus->paid_days = $remain_leave_days;
					$focus->no_paid_days = $focus->absence_days - $remain_leave_days;
					$focus->remain_leave_days = 0;
					if (date('m', strtotime($focus->from_date)) != date('m', strtotime($focus->to_date))) {
						$leave_days_curr_m = myGetDayBetween($focus->from_date, date('t-m-Y', strtotime($focus->from_date))) + 1;
						$focus->used_leave_days_curr_m = $remain_leave_days;
						$focus->used_leave_days_next_m = 1;
					} else {
						$focus->used_leave_days_curr_m = $remain_leave_days;
						$focus->used_leave_days_next_m = 0;
					}
				}
			}
		}
	}

	// kiểm tra ngày xin nghỉ có trùng hay không nếu trùng thì không cho đổi trạng thái
	function checkDuplicateDate($focus, $event, $argument)
	{
		if ($focus->status > 0) {
			$sql_dup = 'SELECT IF(COUNT(*) > 0, 1, 0)
							FROM ec_leaveabsences 
							WHERE deleted = 0 
							AND assigned_user_id = "' . $focus->assigned_user_id . '" 
							AND (
								(from_date <= "' . date('Y-m-d', strtotime($focus->from_date)) . '"
								AND to_date >= "' . date('Y-m-d', strtotime($focus->from_date)) . '")
								OR (from_date <= "' . date('Y-m-d', strtotime($focus->to_date)) . '"
								AND to_date >= "' . date('Y-m-d', strtotime($focus->to_date)) . '")
								OR (from_date >= "' . date('Y-m-d', strtotime($focus->from_date)) . '" 
								AND from_date <= "' . date('Y-m-d', strtotime($focus->to_date)) . '")
								OR (to_date >= "' . date('Y-m-d', strtotime($focus->from_date)) . '" 
								AND to_date <= "' . date('Y-m-d', strtotime($focus->to_date)) . '")
							) AND status <> 0';

			$is_dup = $focus->db->query($sql_dup);
			if ($is_dup) {
				header("Location: index.php?module=EC_LeaveAbsences&action=DetailView&record=" . $focus->id . "&error_str=" . urlencode("Ngày bạn xin nghỉ đã bị trùng"));
				exit;
			}
		}
	}

	public function custom_column(SugarBean $bean, $event, $arguments) {
		global $app_list_strings;
		$status = $GLOBALS['app_list_strings']['absence_status_list'][$bean->status];
  
		// Custom status
		switch($bean->status) {
			case '0':
				$bean->status = '<span class="text-dark fw-semibold">'.$status.'</span>';
				break;
			case '1':
				$bean->status = '<span class="text-warning fw-semibold">'.$status.'</span>';
				break;
		    case '2':
			   $bean->status = '<span class="text-success fw-semibold">'.$status.'</span>';
			   break;
		    default:
			   $bean->status;
		}

		// Custom loại phép
		// $absence_type = $GLOBALS['app_list_strings']['absence_status_list'][$bean->absence_type];
		// pr($bean->absence_type);

		switch(trim($bean->absence_type)) {
			case 'Phép khác':
				$bean->absence_type = '<span class="text-dark fw-semibold">'.$bean->absence_type.'</span>';
				break;
			case 'Phép năm':
				$bean->absence_type = '<span class="text-success fw-semibold">'.$bean->absence_type.'</span>';
				break;
			case 'Phép tử tuất':
				$bean->absence_type = '<span class="text-danger fw-semibold">'.$bean->absence_type.'</span>';
				break;
			case 'Phép kết hôn':
				$bean->absence_type = '<span class="text-info fw-semibold">'.$bean->absence_type.'</span>';
				break;
		    default:
			   $bean->absence_type;
		}
	}
}
