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
	public $booking_id;
	public $last_online;
	public $start_assign;
	public $total_qty;
	public $title;

	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	// Quy trình giao booking 
	public function assignBooking($booking_id, $total_qty, $is_test = 0) {
		global $db, $timedate;
		
		$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
        $current_datetime_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'); 

		$arr_id_admin = [
			'1', //ducpham
			'168889bb-54c2-59c7-8b3f-649102530d3c', // hungnh
			'4f4d7a13-4171-9b7d-251c-64dd8f9885e4', // panda
			'9eb0f65f-a9f6-65bb-1985-637ca8511491', // trinh
			'622ecf27-f729-7187-7e27-6520e0dab882', // quangnd
		];
		$time_current  = date('Y-m-d H:i:s', strtotime('+7 hour'));
		$ksnb_user_id = 'e3bbb3e5-6660-0bf7-8976-54869c4ee609';

		// Nhân viên Telesale không xử lý booking -> loại khỏi danh sách được auto-assign
		$sql_exclude_telesale = '
			AND assigned_user_id NOT IN (
				SELECT user_id FROM acl_roles_users
				WHERE role_id = "34beb2a2-5ee7-f001-2496-68ca264d1d3f" AND deleted = 0
			)
		';

		// Lấy người online đầu hàng
		$sql_assign = 
			"SELECT id, assigned_user_id
			FROM ec_online_report
			WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= '$today_vn'
				AND status = 1
				AND deleted = 0
				$sql_exclude_telesale
			ORDER BY last_online
			LIMIT 1";

		$res_assign = $this->db->query($sql_assign);
		$row_assign = $this->db->fetchByAssoc($res_assign);

		if ($row_assign) {
			$assigned_user_id = $row_assign['assigned_user_id'];

			if (!$is_test) {
				$online = new EC_Online_Report;
				$online->retrieve($row_assign['id']);
				$online->status       = 2;
				$online->booking_id   = $booking_id;
				// current time in the user's timezone/format; save() converts datetime fields back to DB format (UTC)
				$online->last_online  = $timedate->now();
				$online->start_assign = $timedate->now();
				$online->total_qty    = $total_qty;
				$online->save();

				if (!in_array($assigned_user_id, $arr_id_admin)) {
					content_log($assigned_user_id, $current_datetime_vn, 0);
				}
			}
		} else {
			// Không có ai online, thử lấy người busy
			$sql_assign_busy = 
				"SELECT id, assigned_user_id
				FROM ec_online_report
				WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= '$today_vn'
					AND status = 2
					AND deleted = 0
					$sql_exclude_telesale
				ORDER BY last_online
				LIMIT 1";

			$res_assign_busy = $this->db->query($sql_assign_busy);
			$row_assign      = $this->db->fetchByAssoc($res_assign_busy);

			if ($row_assign) {
				$assigned_user_id = $row_assign['assigned_user_id'];

				if (!$is_test) {
					$online = new EC_Online_Report;
					$online->retrieve($row_assign['id']);
					$online->booking_id   = $booking_id;
					$online->start_assign = $timedate->now();
					$online->last_online  = $timedate->now();
					$online->total_qty    = $total_qty;
					$online->save();

					if (!in_array($assigned_user_id, $arr_id_admin)) {
						content_log($assigned_user_id, $current_datetime_vn, 0);
					}
				}
			} else {
				// Fallback: user ksnb
				$assigned_user_id = $ksnb_user_id;
			}
		}

		if ($assigned_user_id == '') {
			// Fallback: user ksnb
			$assigned_user_id = $ksnb_user_id;
		}

		// CẬP NHẬT ASSIGN BOOKING
		$db->query("UPDATE ec_flight_bookings SET assigned_user_id = '$assigned_user_id' WHERE id = '$booking_id' AND deleted = 0");

		return $assigned_user_id;
	}

	// Tạo record ec_online_report cho hôm nay với những user chưa có
	public function populateOnlineReport()
	{
		$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

		$sql = "SELECT id, first_name, last_name, title
			FROM users
			WHERE deleted = 0
				AND status = 'Active'
				AND td_sip IS NOT NULL 
				AND td_sip != ''
				AND title != 'Bot'
				AND (is_admin = 0 OR title = 'QuanLy')
				AND id NOT IN ('e3bbb3e5-6660-0bf7-8976-54869c4ee609') 
			ORDER BY date_entered";

		$res = $this->db->query($sql);

		while ($row = $this->db->fetchByAssoc($res)) {
			$sql_exist = 
				"SELECT IF(COUNT(id) > 0, 1, 0)
				FROM ec_online_report
				WHERE assigned_user_id = '{$row['id']}'
					AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= '$today_vn'
					AND deleted = 0";

			if (!$this->db->getOne($sql_exist)) {
				$online = new EC_Online_Report;
				$online->name             = trim($row['last_name']) . ' ' . trim($row['first_name']);
				$online->assigned_user_id = $row['id'];
				$online->status           = 0;
				$online->title            = $row['title'];
				$online->save();
			}
		}
		return true;
	}

	// thay đổi vị trí trong bảng Online hoặc bật / tắt chế độ ưu tiên
	// up / down: đều chuyển trạng thái sang Online
	// up: đưa lên đầu hàng Online, down đưa xuống cuối hàng Online
	// sl người được ưu tiên mặc định tối đa 3 người
	public function changeOnlinePosition($onl_id, $change_type)
	{
		$onl_id = preg_replace('/[^a-f0-9\-]/i', '', (string)$onl_id);
		$change_type = in_array($change_type, ['up', 'down', 'off', 'busy', 'delete']) ? $change_type : '';
		if (empty($onl_id) || empty($change_type)) return '';

		if ($change_type == 'delete') {
			$this->db->query("UPDATE ec_online_report SET deleted = 1, date_modified = NOW() WHERE id = '$onl_id'");
			return '';
		}

		global $timedate;
		$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

		// Offline
		if ($change_type == 'off') {
			$onl = new EC_Online_Report;
			$onl->retrieve($onl_id);
			$onl->status = 0;
			$onl->last_online = $timedate->now();
			$onl->booking_id = '';
			$onl->start_assign = '';
			$onl->save();
		} else if ($change_type == 'busy') {
			$onl = new EC_Online_Report;
			$onl->retrieve($onl_id);
			$onl->status = 2;
			$onl->last_online = $timedate->now();
			$onl->save();
		} else if ($change_type == 'up' || $change_type == 'down') {
			// kt còn người Online
			$sql_onl_cnt = 
				"SELECT COUNT(id)
				FROM ec_online_report
				WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= '$today_vn'
					AND status = 1
					AND deleted = 0";
			$onl_cnt = $this->db->getOne($sql_onl_cnt);

			if ($change_type == 'up' && $onl_cnt > 0) {
				// Đưa lên đầu hàng Online
				$sql1 = 
					"SELECT id, last_online
					FROM ec_online_report
					WHERE DATE_ADD(date_entered, INTERVAL 7 HOUR) >= '$today_vn'
						AND status = 1
						AND deleted = 0
					ORDER BY last_online
					LIMIT 1";

				$res1 = $this->db->query($sql1);
				$row1 = $this->db->fetchByAssoc($res1);

				$sql2 = '
					UPDATE ec_online_report
					SET status = 1
						, last_online = "' . date('Y-m-d H:i:s', strtotime('-1 minute', strtotime($row1['last_online']))) . '"
						, date_modified = NOW()
					WHERE id = "' . $onl_id . '" AND deleted = 0
				';
				$this->db->query($sql2);
			} else if ($change_type == 'down') {
				// Đưa xuống cuối hàng Online
				$onl = new EC_Online_Report;
				$onl->retrieve($onl_id);
				$onl->status = 1;
				$onl->last_online = $timedate->now();
				$onl->booking_id = '';
				$onl->start_assign = '';
				$onl->save();
			}
		}

		return '';
	}
}
