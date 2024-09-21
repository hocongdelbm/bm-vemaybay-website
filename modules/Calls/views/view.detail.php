<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class CallsViewDetail extends ViewDetail
{

	function display()
	{
		global $current_user, $app_list_strings, $sugar_config;

		if ($current_user->user_name == 'hungnh' || $current_user->user_name == 'booker') {
			$log = json_decode(html_entity_decode($this->bean->log), true);
			pr($log);
		}

		$this->populateCustomButtons();

		if ((!is_admin($current_user) || $current_user->id == '72ece22c-cb25-8e30-9dea-56f2201cd359') && $this->bean->status == 'new') {
			$cal = new Call();
			$cal->retrieve($this->bean->id);
			$cal->status = 'processing';
			if (empty($this->bean->assigned_user_id)) $cal->assigned_user_id = $current_user->id;
			$cal->save();
			header("Refresh:0");
		}

		parent::display();
	}

	function populateCustomButtons()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();

		// View call source
		if (isset($this->bean->call_sources) && !empty($this->bean->call_sources)) {
			$call_source = '<a href="https://' . $this->bean->call_sources . '" target="_blank">' . $this->bean->call_sources . '</a>';
			$this->ss->assign('CUSTOM_CALL_SOURCES', $call_source);
		}

		// DOITT - xử lý cuộc gọi
		if (ACLController::checkAccess('Calls', 'edit', true)) {
			$change_status = '</form>
			<form action="index.php" method="post" id="frmChangeStatus" name="frmChangeStatus">
				<input type="hidden" name="module" value="Calls" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="return_module" value="Calls" />
				<input type="hidden" name="return_action" value="Save" />
				<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
				<input type="hidden" name="parent_id" value="' . $this->bean->parent_id . '" />
				<input type="hidden" name="parent_type" value="' . $this->bean->parent_type . '" />
				<select class="box-select" id="status" name="status">' . get_select_options_with_id($app_list_strings['call_status_dom'], $this->bean->status) . '</select>
				<input type="submit" class="btn btn-primary btn-change-status" id="btnchangeStatus" name="btnchangeStatus" title="Đổi tình trạng" value="Đổi tình trạng"/>
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}

		// tình trạng cuộc gọi
		$status = '';
		// if(ACLController::checkAccess('Calls', 'edit', true) && $this->bean->status == 'new'){
		// 	// Đang xử lý
		// 	$status = '</form>
		// 	<form action="index.php" method="post" name="frmProcessing" id="frmProcessing">
		// 		<input type="hidden" name="module" value="Calls" />
		// 		<input type="hidden" name="action" value="Save" />
		// 		<input type="hidden" name="record" value="'.$this->bean->id.'" />
		// 		<input type="hidden" name="return_module" value="Calls" />
		// 		<input type="hidden" name="return_action" value="DetailView" />
		// 		<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
		// 		<input type="hidden" name="status" value="processing" />
		// 		<input type="submit" class="btn btn-warning fw-semibold btn-change-status" name="btnProcessing" id="btnProcessing" value="Đang xử lý" title="Đang xử lý"/>
		// 	</form>';
		// }
		if (ACLController::checkAccess('Calls', 'edit', true) && $this->bean->status == 'processing' && ACLController::checkAccess('Bugs', 'view', true)) {
			// Đã hoàn thành
			$status = '</form>
			<form action="index.php" method="post" name="frmCompleted" id="frmCompleted">
				<input type="hidden" name="module" value="Calls" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="return_module" value="Calls" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
				<input type="hidden" name="status" value="done" />
				<input class="btn btn-success fw-semibold btn-change-status" type="submit" name="btnCompleted" id="btnCompleted" value="Hoàn thành" title="Hoàn thành"/>
			</form>';
		}
		$this->ss->assign('CALLS_STATUS', $status);
	}
}
