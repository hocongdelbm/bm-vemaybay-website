<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewassignbk extends SugarView {
    public string $timezone;
    public string $date_format;
    public string $time_format;

	// Khởi tạo timezone/định dạng ngày giờ từ preference của user.
	// Tách riêng để cả display() lẫn getUserSttInf() (gọi trực tiếp từ epFlightBookings)
	// đều khởi tạo được typed property, tránh "must not be accessed before initialization".
	private function initFormats() {
		global $current_user, $sugar_config;

		$this->timezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
		$this->date_format = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
		$this->time_format = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');
	}

	public function display() {
		$this->initFormats();

		$smartyCont = new Sugar_Smarty();
		$smartyCont->assign('IS_ALLOWED_USER', is_admin($current_user));
		$smartyCont->assign('ONLINE_DATA', $this->getUserSttInf());
		$smartyCont->assign('LIST_USER', $this->getListUsers());
		$smartyCont->display("modules/{$this->bean->object_name}/tpls/view_assignbk.tpl");
	}

	public function getUserSttInf() {
		global $app_list_strings, $current_user;

		// Có thể được gọi trực tiếp (auto-refresh dashboard) mà không qua display()
		$this->initFormats();

		$today_utc = gmdate('Y-m-d');
		$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');

		$sql =
			"SELECT eor.*, u.title AS user_title
			FROM ec_online_report eor
				LEFT JOIN users u ON u.id = eor.assigned_user_id AND u.deleted = 0
			WHERE eor.deleted = 0
				AND DATE(DATE_ADD(eor.date_entered, INTERVAL 7 HOUR)) = '$today_vn'
			ORDER BY FIELD(eor.status, 1, 2, 0), eor.last_online";

		$arr_group_badge = [
			'Booker'   => '<span class="badge bg-primary">Booker</span>',
			'KeToan'   => '<span class="badge bg-warning text-dark">Kế toán</span>',
			'Laptop'   => '<span class="badge bg-danger">Laptop</span>',
			'Admin'    => '<span class="badge bg-dark">Admin</span>',
			'QuanLy'  => '<span class="badge bg-secondary">Manager</span>',
			'Telesale' => '<span class="badge bg-info">Telesale</span>',
		];

		// SQL CALL INBOUND
		$sql_inbound = 
			"SELECT u.id as user_id, count(*) as quantity_inbound
			FROM calls c
				LEFT JOIN users u ON u.id = c.assigned_user_id AND u.deleted = 0
			WHERE DATE(c.date_entered) = '$today_utc'
				AND c.direction = 'inbound'
				AND c.deleted = 0
			GROUP BY user_id";
					
		$arr_inbound = array();
		$res_inbound = $this->bean->db->query($sql_inbound);
		while ($row_inbound = $this->bean->db->fetchByAssoc($res_inbound)) {
			$arr_inbound[$row_inbound['user_id']] =  $row_inbound['quantity_inbound'];
		}

		$arr_agent = custom_get_sip_number();
		$res = $this->bean->db->query($sql);
		$i   = 0;

		$html 	 = '<table id="online_tbl" class="table-online_tbl table-details__booking" cellpadding="0" cellspacing="0">
						<thead>
							<th class="hide-mobile" width="5%">#</th>
							<th width="15%">Họ tên</th>
							<th width="10%">SIP</th>
							<th width="10%">Tình trạng</th>
							<th class="hide-mobile" width="10%">Chức vụ</th>
							<th class="hide-mobile" width="15%">Check-in</th>
							<th class="hide-mobile text-nowrap" width="10%">Nhận cuộc gọi</th>';

		if (is_admin($current_user)) {
			$html .= '<th></th>';
		}

		$html .= '</thead><tbody>';

		if ($this->bean->db->countRows($res) == 0) {
			$html .= '
				<tr>
					<td colspan="9">Chưa có nhân viên nào online</td>
				</tr>
			';
		} else {
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$start_online = '';
				if (isset($row['start_online']) && !empty($row['start_online']) && strtotime($row['start_online']) !== false) {
					$start_online = DatetimeHelper::convert_datetime($row['start_online']
						, DatetimeHelper::DB_FORMAT, "$this->date_format $this->time_format"
						, DatetimeHelper::DB_TIMEZONE, $this->timezone
					);
				}

				$row_class = $status_class = '';
				if ($row['status'] == 0) {
					$row_class = 'offline';
					$status_class = '';
					$cus_btn = '';
				} else if ($row['status'] == 2) {
					$row_class = 'busy';
					$status_class = 'stt_busy';
				} else if ($row['status'] == 1) {
					$row_class = 'change_pos_valid';
					$status_class = 'stt_online';
				}

				$html .= '
					<tr class="fw-bold ' . $row_class . '" ln="' . ($i + 1) . '">
						<td class="hide-mobile text-center col_no">' . ($i + 1) . '</td>
						<td class="text-start col_name employees"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $row['assigned_user_id'] . '" target="_bank">' . $row['name'] . '</a></td>
						<td class="text-center fw-semibold col_sip sip_number">' . $arr_agent[$row['assigned_user_id']]['user'] . '</td>
						<td class="text-center status ' . $status_class . '">' . $app_list_strings['online_stt_list'][$row['status']] . '</td>
						<td class="hide-mobile text-center group_sip">' . ($arr_group_badge[$row['user_title']] ?? $row['user_title']) . '</td>
						<td class="hide-mobile text-center start_online">' . $start_online . '</td>
						<td class="hide-mobile text-center fw-semibold call_inbound">' . ($arr_inbound[$row['assigned_user_id']] ?? '') . '</td>';

				if (is_admin($current_user)) {
					// các nút thao tác
					$cus_btn = '
							<div class="d-flex align-items-center justify-content-center flex-wrap gap-1">
								<input type="button" class="online_btn btn btn-success up_btn" value="UP" change_type="up" onl_val="' . $row['id'] . '">
								<input type="button" class="online_btn btn btn-secondary down_btn" value="DOWN" change_type="down" onl_val="' . $row['id'] . '">
								<input type="button" class="online_btn btn btn-primary busy_btn" value="BUSY" change_type="busy" onl_val="' . $row['id'] . '">
								<input type="button" class="online_btn btn btn-dark off_btn" value="OFF" change_type="off" onl_val="' . $row['id'] . '" data-sip="' . custom_get_sip_number($row['assigned_user_id']) . '">
								<input type="button" class="online_btn btn btn-danger del_btn" value="DEL" change_type="delete" onl_val="' . $row['id'] . '">
							</div>
						';

					$html .= '<td class="text-center">' . $cus_btn . '</td>';
				}

				$html .= '</tr>';
				$i++;
			}
		}

		$html .= '</tbody></table>';

		return $html;
	}

	function getListUsers()
	{
		$html = '<select class="box-select" name="user_id--report" id="user_id--report" onchange="document.frmSearch.submit();">
				<option value=""></option>';

		$sql = "SELECT *
				FROM users
				WHERE title IN ('KeToan', 'Booker', 'QuanLy') AND status = 'Active' 
				AND deleted = 0";

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$html .= '<option value="' . $row['id'] . '">' . $row['last_name'] . ' ' . $row['first_name'] . '</option>';
		}

		$html .= '</select>';

		return $html;
	}
}
