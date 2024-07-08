<?php

class EC_Online_Report extends Basic
{
	public $new_schema = true;
	public $module_dir = 'EC_Online_Report';
	public $object_name = 'EC_Online_Report';
	public $table_name = 'ec_online_report';
	public $importable = true;

	public $disable_row_level_security = true; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO

	public $id;
	public $name;
	public $date_entered;
	public $date_modified;
	public $modified_user_id;
	public $modified_by_name;
	public $created_by;
	public $created_by_name;
	public $description;
	public $deleted;
	public $created_by_link;
	public $modified_user_link;
	public $assigned_user_id;
	public $assigned_user_name;
	public $assigned_user_link;
	public $SecurityGroups;

	public $start_online;
	public $status;

	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	// Quy trình giao booking 
	function assignBooking($booking_id, $total_qty, $is_test = 0)
	{
		global $db, $current_user;

		$arr_id_admin = array(
			'1', //ducpham
			'168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
			'4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //panda
			'9eb0f65f-a9f6-65bb-1985-637ca8511491', //trinh
			'622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
			'e4a1676e-536d-b5d2-75c2-6502656a118b', //cuongnv
		);
		$time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));


		// kt số người online
		$sql_onl_cnt = '
			SELECT COUNT(id)
			FROM ec_online_report
			WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
				AND status = 1
				AND deleted = 0
		';

		$onl_cnt = $this->db->getOne($sql_onl_cnt);

		if ($onl_cnt > 0) {
			// giao tuần tự
			$sql_assign = '
				SELECT id, assigned_user_id, status
				FROM ec_online_report
				WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '" 
					AND status = 1 
					AND deleted = 0
				ORDER BY last_online
				LIMIT 1
			';
			// date_modified

			$res_assign = $this->db->query($sql_assign);
			$row_assign = $this->db->fetchByAssoc($res_assign);
			$assigned_user_id = $row_assign['assigned_user_id'];

			// chuyển trạng thái
			if (!$is_test) {
				$online = new EC_Online_Report;
				$online->retrieve($row_assign['id']);
				$online->status 		= 2;
				$online->last_online 	= date('Y-m-d H:i:s');
				$online->booking_id 	= $booking_id;
				$online->start_assign 	= date('Y-m-d H:i:00');
				$online->round 			= (int)$online->round + 1;
				$online->total_qty 		= $total_qty;
				$online->save();

				// Ghi lại log thời gian lúc chuyến trạng thái 
				if (!in_array($assigned_user_id, $arr_id_admin)) {
					content_log($assigned_user_id, $time_current, '', 1);
				}
			}
		} else {
			// kt sl người busy 
			$sql_busy_cnt = '
				SELECT COUNT(id)
				FROM ec_online_report
				WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
					AND status = 2
					AND deleted = 0
			';

			$busy_cnt = $this->db->getOne($sql_busy_cnt);

			if ($busy_cnt > 0) {
				$sql_assign = '
					SELECT id, assigned_user_id, status
					FROM ec_online_report
					WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
						AND status = 2
						AND deleted = 0 
					ORDER BY last_online
					LIMIT 1
				';
				//date_modified

				$res_assign 		= $this->db->query($sql_assign);
				$row_assign 		= $this->db->fetchByAssoc($res_assign);
				$assigned_user_id 	= $row_assign['assigned_user_id'];

				// chuyển trạng thái
				if (!$is_test) {
					$online = new EC_Online_Report;
					$online->retrieve($row_assign['id']);
					$online->booking_id 	= $booking_id;
					$online->start_assign 	= date('Y-m-d H:i:00');
					$online->last_online 	= date('Y-m-d H:i:s');
					$online->round 		= (int)$online->round + 1;
					$online->total_qty 		= $total_qty;
					$online->save();

					// Ghi lại log thời gian lúc chuyến trạng thái 
					if (!in_array($assigned_user_id, $arr_id_admin)) {
						content_log($assigned_user_id, $time_current, '', 1);
					}
				}
			} else {
				// user NB kiemsoat
				$assigned_user_id = 'e3bbb3e5-6660-0bf7-8976-54869c4ee609';
				// $assigned_user_id = $current_user->id;
			}
		}

		if ($assigned_user_id == '') {
			$assigned_user_id = 'e3bbb3e5-6660-0bf7-8976-54869c4ee609';
			// $assigned_user_id = $current_user->id;
		}

		// CẬP NHẬT ASSIGN BOOKING
		$sql_assign_booking = '
			UPDATE ec_flight_bookings
			SET assigned_user_id = "' . $assigned_user_id . '"
			WHERE id = "' . $booking_id . '"
		';
		$db->query($sql_assign_booking);

		return $assigned_user_id;
	}

	// thay đổi vị trí trong bảng Online hoặc bật / tắt chế độ ưu tiên
	// up / down: đều chuyển trạng thái sang Online
	// up: đưa lên đầu hàng Online, down đưa xuống cuối hàng Online
	// sl người được ưu tiên mặc định tối đa 3 người
	function changeOnlinePosition($onl_id, $change_type)
	{
		// Offline
		if ($change_type == 'off') {
			$onl = new EC_Online_Report;
			$onl->retrieve($onl_id);
			$onl->status = 0;
			// Update time last_online 
			$onl->last_online 	= date('Y-m-d H:i:s');
			$onl->booking_id = '';
			$onl->start_assign = '';
			$onl->save();
		} else {
			// kt còn người Online
			$sql_onl_cnt = '
				SELECT COUNT(id)
				FROM ec_online_report
				WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
					AND status = 1
					AND deleted = 0
			';
			$onl_cnt = $this->db->getOne($sql_onl_cnt);
			if ($change_type == 'up' && $onl_cnt > 0) {
				// lấy thông tin người đang xếp đầu hàng Online
				// $sql1 = '
				// 	SELECT id, date_modified, round
				// 	FROM ec_online_report
				// 	WHERE deleted = 0 AND status = 1
				// 	AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
				// 	ORDER BY date_modified 
				// 	LIMIT 1
				// ';
				$sql1 = '
					SELECT id, last_online, round
					FROM ec_online_report
					WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
						AND status = 1
						AND deleted = 0
					ORDER BY last_online 
					LIMIT 1
				';
				$res1 = $this->db->query($sql1);
				$row1 = $this->db->fetchByAssoc($res1);

				// $sql2 = '
				// 	UPDATE ec_online_report
				// 	SET status = 1
				// 		, date_modified = "' . date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($row1['date_modified']))) . '"
				// 		, round = ' . $row1['round'] . '
				// 	WHERE id = "' . $onl_id . '"
				// ';
				$sql2 = '
					UPDATE ec_online_report
					SET status = 1
						, last_online = "' . date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($row1['last_online']))) . '"
						, round = ' . $row1['round'] . '
					WHERE id = "' . $onl_id . '"
				';
				$this->db->query($sql2);
			} else if ($change_type == 'down' || $onl_cnt == 0) {
				// chỉ cần đổi trạng thái qua Online
				$onl = new EC_Online_Report;
				$onl->retrieve($onl_id);
				$onl->status = 1;
				// Update time last_online 
				$onl->last_online 	= date('Y-m-d H:i:s');
				$onl->round = $this->getMaxRound();
				$onl->booking_id = '';
				$onl->start_assign = '';
				$onl->save();
			}
		}

		return '';
	}

	// lấy thông tin round cao nhất hiện có
	function getMaxRound()
	{
		$sql = '
			SELECT IFNULL(MAX(round), 1)
			FROM ec_online_report
			WHERE deleted = 0
			AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
		';
		return (int)$this->db->getOne($sql);
	}
}
