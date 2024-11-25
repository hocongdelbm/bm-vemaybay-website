<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewassignbk extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_assignbk.tpl');
	}

	function populateContent($smartyobj)
	{
		global $current_user;
		$AllowedUser = false;
		if (isAllowedUser()) {
			$AllowedUser = true;
		}

		// if (isset($_POST['type']) && $_POST['type'] == 'view_report_online') {
		// 	$smartyobj->assign('VIEW_REPORT_ONLINE', $this->viewReportOnline());
		// 	$smartyobj->assign('USER_NAME', get_user_array(true, '', '', true)[$_POST['user_id--report']]);
		// }

		$smartyobj->assign('IS_ALLOWED_USER', $AllowedUser);
		$smartyobj->assign('ONLINE_DATA', $this->getUserSttInf());
		$smartyobj->assign('LIST_USER', $this->getListUsers());
	}

	function getUserSttInf()
	{
		global $app_list_strings, $current_user;

		$sql = '
			SELECT *, (
				CASE 
					WHEN ranking < 5 THEN 0
					WHEN ranking >= 8 THEN 2
				ELSE 1 END) AS group_type
			FROM ec_online_report
			WHERE deleted = 0 
			-- AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d') . '"
			-- AND DATE_ADD(date_entered, INTERVAL 7 HOUR) >= "' . date('Y-m-d', strtotime('+7 hours', strtotime(date('Y-m-d H:i:s')))) . '"
			AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
			ORDER BY FIELD(status, 1, 2, 0), last_online
		';
		// date_modified

		// SQL CALL INBOUND
		$sql_inbound = 'SELECT u.id as user_id, count(*) as quantity_inbound
						FROM calls c
						LEFT JOIN users u ON u.id = c.assigned_user_id AND u.deleted = 0
						WHERE c.direction = "inbound"
						AND DATE(c.date_entered) = "' . date('Y-m-d', strtotime('+7 hours', strtotime(date('Y-m-d H:i:s')))) . '"
						AND c.deleted = 0
						GROUP BY user_id
					';
		$arr_inbound = array();
		$res_inbound = $this->bean->db->query($sql_inbound);
		while ($row_inbound = $this->bean->db->fetchByAssoc($res_inbound)) {
			$arr_inbound[$row_inbound['user_id']] =  $row_inbound['quantity_inbound'];
		}

		$arr_agent = custom_get_sip_number();
		$res 	 = $this->bean->db->query($sql);
		$i 		 = 0;

		$html 	 = '<table id="online_tbl" class="table-online_tbl table-details__booking" cellpadding="0" cellspacing="0">
						<thead>
							<th class="hide-mobile" width="5%">STT</th>
							<th width="12%">Họ tên</th>
							<th width="8%">SIP</th>
							<th width="8%">Tình trạng</th>
							<th class="hide-mobile" width="8%">Nhóm</th>
							<th class="hide-mobile" width="15%">Check-in</th>
							<th class="hide-mobile text-nowrap" width="8%">Nhận cuộc gọi</th>';

						if (isAllowedUser($current_user)) {
							$html .= '<th class="hide-mobile" width="15%">Last Online</th>
									<th></th>';
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
				if (isset($row['start_online']) && !empty($row['start_online']) && strtotime($row['start_online']) !== false) {
					$start_online = date('d-m-Y H:i:s', strtotime('+7 hour', strtotime($row['start_online'])));
					$last_online = date('d-m-Y H:i:s', strtotime('+7 hour', strtotime($row['last_online'])));
				} else {
					$start_online = '';
					$last_online = '';
				}

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
					<tr class="' . $row_class . '" ln="' . ($i + 1) . '">
						<td class="hide-mobile text-center col_no">' . ($i + 1) . '</td>
						<td class="text-start col_name employees"><a href="index.php?module=Employees&return_module=Employees&action=DetailView&record=' . $row['assigned_user_id'] . '" target="_bank">' . $row['name'] . '</a></td>
						<td class="text-center fw-semibold col_sip sip_number">' . $arr_agent[$row['assigned_user_id']]['user'] . '</td>
						<td class="text-center status ' . $status_class . '">' . $app_list_strings['online_stt_list'][$row['status']] . '</td>
						<td class="hide-mobile text-center group_sip">' . getNameGroupCalls($arr_agent[$row['assigned_user_id']]['user']) . '</td>
						<td class="hide-mobile text-center start_online">' . $start_online . '</td>
						<td class="hide-mobile text-center fw-semibold call_inbound">' . ($arr_inbound[$row['assigned_user_id']] ?? '') . '</td>';

					if (isAllowedUser($current_user)) {
						// các nút thao tác
						$cus_btn = '
							<div class="d-flex align-items-center justify-content-center flex-wrap gap-1">
								<input type="button" class="online_btn flex-fill btn btn-primary up_btn" value="UP" change_type="up" onl_val="' . $row['id'] . '">
								<input type="button" class="online_btn flex-fill btn btn-secondary down_btn" value="DOWN" change_type="down" onl_val="' . $row['id'] . '">
								<input type="button" class="online_btn flex-fill btn btn-danger off_btn" value="OFF" change_type="off" onl_val="' . $row['id'] . '" data-sip="' . custom_get_sip_number($row['assigned_user_id']) . '">
							</div>
						';
						$last_online = $row['last_online'] ? date('d-m-Y H:i:s', strtotime('+7 hour', strtotime($row['last_online']))) : '';

						$html .= '<td class="hide-mobile text-center last_online">' . $last_online . '</td>
									<td class="text-center">' . $cus_btn . '</td>';
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

	function viewReportOnline()
	{
		$html 		= '<table id="online-report__tbl" class="table-online__report table-details__booking w-50" cellpadding="0" cellspacing="0" border="0">
						<thead>
							<tr>
								<th>Ngày</th>
								<th>Check in</th>
								<th>Last online</th>
							</tr>
						</thead>
						<tbody>';
		$year 		= date("Y");
		$user_id 	= isset($_POST['user_id--report']) ? $_POST['user_id--report'] : 'none';

		$sql = 'SELECT date_entered, start_online, last_online
				FROM ec_online_report
				WHERE assigned_user_id = "' . $user_id . '"
				AND date_entered >= (LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))
				AND date_entered < (LAST_DAY(CURDATE()) + INTERVAL 1 DAY)
				AND YEAR(date_entered) = ' . $year . '
				AND deleted = 0
				ORDER BY date_entered';

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$start_online 	= !empty($row['start_online']) ? date('H:i:s', strtotime('+7 hours', strtotime($row['start_online']))) : '';
			$last_online 	= !empty($row['start_online']) ? date('H:i:s', strtotime('+7 hours', strtotime($row['last_online']))) : '';

			$html .= '<tr>
					<td align="center">' . date('d-m-Y', strtotime('+7 hours', strtotime($row['date_entered']))) . '</td>		
					<td align="center">' . $start_online . '</td>		
					<td align="center">' . $last_online . '</td>		
			</tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}
}
