<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_LeaveAbsencesViewDetail extends ViewDetail{
	function display(){
		$this->populateCustomButtons();
		$this->populateCustomFields();
		$this->displayJS();
		parent::display();
	}
	
	function populateCustomButtons() {
		global $current_user, $app_list_strings;

		if($this->bean->status == 0) {
			$status_btn = '';
			$status_btn .= '</form><form method="post" action="index.php">
				<input type="hidden" name="module" value="EC_LeaveAbsences">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<input type="hidden" name="status" value="1">
				<input class="btn btn-primary" type="submit" value="Chuyển đi">
			</form>';
			$this->ss->assign('STATUS_BTN', $status_btn);
		}

		// chỉ có user có quyền HCNS với amdin có thể duyệt phiếu nghỉ phép, và user có chức danh QuanLy
		$is_hcns = $this->checkHCNS($current_user->id);
		if(is_admin($current_user) || $is_hcns || $current_user->title == 'QuanLy') {
			// nút duyệt
			if($this->bean->status == 1) {
				$approved_btn = '</form><form method="post" action="index.php">
					<input type="hidden" name="module" value="EC_LeaveAbsences">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="record" value="'.$this->bean->id.'">
					<input type="hidden" name="status" value="2">
					<input type="hidden" name="user_id_c" value="'.$current_user->id.'">
					<input type="hidden" name="approved_date" value="'.date('d-m-Y H:i:s').'">
					<input class="btn btn-primary" type="submit" value="Duyệt">
				</form>';
				$this->ss->assign('APPROVED_BTN', $approved_btn);
			}

			// chuyển trạng thái
			$change_status = '</form><form method="post" action="index.php">
				<input type="hidden" name="module" value="EC_LeaveAbsences">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<select class="box-select" name="status">'.get_select_options_with_id($app_list_strings['absence_status_list'], (int)$this->bean->status).'</select>
				<input class="btn btn-primary" type="submit" value="Chuyển trạng thái">
			</form>';
			$this->ss->assign('CHANGE_STATUS_BTN', $change_status);
		}
	}

	function populateCustomFields() {
		// ngày duyệt
		if(!empty($this->bean->user_id_c) && !empty($this->bean->approved_date)) {
			$approved_user = new User;
			$approved_user->retrieve($this->bean->user_id_c);
			$approved_date = date('d-m-Y H:i', strtotime($this->bean->approved_date)) . ' bởi ' . $approved_user->last_name.' '.$approved_user->first_name;
		}
		
		$approved_date = isset($approved_date) ? $approved_date : '';
		$this->ss->assign('CUS_APPROVED_BY', $approved_date);

		// người xin nghỉ
		$u = new User;
		$u->retrieve($this->bean->assigned_user_id);
		$this->ss->assign('ASSIGNED_USER_NAME', $u->last_name.' '.$u->first_name);

		// ngày nghỉ nửa buổi
		$absence_days = $this->bean->absence_days.' | Ngày nghỉ nửa buổi: ';
		$absence_days .= ($this->bean->partofday!=0)?$this->bean->part_date.' '.(($this->bean->partofday==1)?'Buổi sáng':'Buổi chiều'):'';
		$this->ss->assign('ABSENCE_DAYS', $absence_days);
		
		// phê duyệt
		$remark = '<table cellpadding="0" cellspacing="0" class="table-details__booking">
			<tbody>
				<tr>
					<td width="35%">Phép năm còn lại: </td>
					<td>'.$this->bean->remain_leave_days.'</td>
				</tr>
				<tr>
					<td>Nghỉ có lương: </td>
					<td>'.$this->bean->paid_days.'</td>
				</tr>
				<tr>
					<td>Nghỉ không lương: </td>
					<td>'.$this->bean->no_paid_days.'</td>
				</tr>
				<tr>
					<td>Số phép năm sử dụng: </td>
					<td>'.number_format(($this->bean->used_leave_days_curr_m + $this->bean->used_leave_days_next_m), 1, '.', ',').'</td>
				</tr>
			</tbody>
		</table>';
		$this->ss->assign('REMARK', $remark);
	}

	function displayJS() {
		echo '<script>
			$(document).ready(function() {
				var status = '.$this->bean->status.';
				if(status > 0) {
					$("#edit_button").hide();
					$("input[name=\'Delete\']").hide();
				}
			});
		</script>';
	}

	function checkHCNS($user_id) {
		$sql = 'SELECT COUNT(*) FROM acl_roles_users 
				WHERE deleted = 0 AND role_id = "bce26e01-1e6b-10ff-b53f-5fd876e11c9d" 
				AND user_id = "' . $user_id . '"';
		return $this->bean->db->getOne($sql);
	}
}
?>
