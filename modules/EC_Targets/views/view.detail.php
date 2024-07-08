<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_TargetsViewDetail extends ViewDetail{

	function display() {
		$this->populateCustomButtons();
		$this->populateCustomFields();
		$this->displayJS();
		parent::display();
	}
	
	function populateCustomButtons() {
		global $app_list_strings, $current_user;

		// trạng thái phiếu target
		if($this->bean->status == 0) {
			$status_btn = '';
			$status_btn .= '</form><form method="post" action="index.php">
				<input type="hidden" name="module" value="EC_Targets">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<input type="hidden" name="status" value="1">
				<input type="submit" value="Chuyển đi" class="btn btn-primary">
			</form>';
			$this->ss->assign('STATUS_BTN', $status_btn);
		}

		
		if(is_admin($current_user) || $current_user->title == 'QuanLy') {
			// nút duyệt
			if($this->bean->status == 1) {
				$approved_btn = '</form><form method="post" action="index.php">
					<input type="hidden" name="module" value="EC_Targets">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="record" value="'.$this->bean->id.'">
					<input type="hidden" name="status" value="2">
					<input type="hidden" name="user_id_c" value="'.$current_user->id.'">
					<input type="hidden" name="approved_date" value="'.date('d-m-Y H:i:s').'">
					<input type="submit" value="Duyệt" class="btn btn-primary">
				</form>';
				$this->ss->assign('APPROVED_BTN', $approved_btn);
			}

			// chuyển trạng thái
			$change_status = '</form><form method="post" action="index.php">
				<input type="hidden" name="module" value="EC_Targets">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<select class="box-select" name="status">'.get_select_options_with_id($app_list_strings['target_status_list'], (int)$this->bean->status).'</select>
				<input type="submit" value="Chuyển trạng thái" class="btn btn-primary">
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
		isset($approved_date) ? $this->ss->assign('CUS_APPROVED_BY', $approved_date) : '';
	}

	function displayJS() {
		global $app_list_strings;

		if($this->bean->status > 0) {
			echo '<script>
				$(document).ready(function() {
					$("#edit_button").hide();
					$("input[name=\'Delete\']").hide();
				});
			</script>';
		}

		echo '
		<script>
			$(document).ready(function() {
				$(".moduleTitle>h2").text($(".moduleTitle>h2").text() + " ( '.$app_list_strings['target_status_list'][$this->bean->status].' )");
			});
		</script>';
	}
}
?>