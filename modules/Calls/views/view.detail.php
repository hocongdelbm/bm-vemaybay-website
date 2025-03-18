<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class CallsViewDetail extends ViewDetail
{

	function display()
	{
		global $current_user, $app_list_strings, $sugar_config, $timedate;

		if (!is_admin($current_user)) {
			unset($this->dv->defs['templateMeta']['form']['buttons'][1]); // Ẩn nút DELETE
		}

		if ($current_user->user_name == 'hungnh') {
			$log = json_decode(html_entity_decode($this->bean->log), true);
			$date_format = $timedate->get_date_format();
			pr($this->bean->log);
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
		global $current_user, $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();

		// View call source
		if (isset($this->bean->call_sources) && !empty($this->bean->call_sources)) {
			$call_source = '<a href="https://' . $this->bean->call_sources . '" target="_blank">' . $this->bean->call_sources . '</a>';
			$this->ss->assign('CUSTOM_CALL_SOURCES', $call_source);
		}

		// DOITT - xử lý cuộc gọi

		if (ACLController::checkAccess('Calls', 'edit', true) && $this->bean->status != 'done') {
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

		// LOG CALL
		$call_annotation = '';
		if(is_admin($current_user)){
			$log_calls = json_decode(html_entity_decode($this->bean->log), true);
			$message_annotation = $this->bean->summaryLogForCalls($log_calls);
	
			$call_annotation .= '<button type="button" class="btn btn-info" data-bs-toggle="modal" id="btnAnnotation" data-bs-target="#modalAnnotation">Chú giải</button>';
			$call_annotation .= '<div class="modal fade" id="modalAnnotation" tabindex="-1" aria-labelledby="modalAnnotationLabel" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" style="max-width: 90vw !important;">
										<div class="modal-content text-wrap">
											<div class="modal-header">
												<h1 class="modal-title fs-5 text-white" id="modalAnnotationLabel">Chú giải cuộc gọi</h1>
											</div>
											<div class="modal-body">
												<div class="flex-between">
													<div class="alert alert-info mt-3 fs-7 w-50" role="alert">' . $message_annotation . '</div>
													<div class="annotation-image text-center flex-fill">
														<img src="themes/SuiteP/images/modules/calls/log_calls.png" alt="Call Annotation" class="img-fluid">
													</div>
												</div>
												<div class="d-flex border rounded p-2">
													<ul class="annotation-list w-50">
														<li>
															<strong>call_start:</strong>
															<span>Thời điểm bắt đầu cuộc gọi (hệ thống bắt đầu quay số).</span>
														</li>
														<li>
															<strong>call_accepted:</strong>
															<ol>
																<li>Thời điểm cuộc gọi được người nhận nghe máy.</li>
																<li>Không có thoại thì log không tồn tại <strong>call_accepted</strong>. Thời gian chờ = <strong>call_duration</strong></li>
															</ol>
														</li>
														<li>
															<strong>call_end:</strong>
															<span>Thời điểm cuộc gọi kết thúc.</span>
														</li>
														<li>
															<strong>call_direction:</strong>
															<span>Chiều hướng cuộc gọi <strong>(<span class="text-primary fw-semibold">Cuộc gọi đi</span>, <span class="text-success fw-semibold">Cuộc gọi đến</span>, <span class="text-dark fw-semibold">Nội bộ</span>)</strong>.</span>
														</li>
														<li>
															<strong>p/s:</strong>
															<span>Thời gian được tính bằng giây(s).</span>
														</li>
													</ul>
													<ul class="annotation-list flex-fill">
														<li>
															<strong>call_duration:</strong>
															<span>Tổng thời gian từ khi bắt đầu đến khi kết thúc cuộc gọi.</span>
														</li>
														<li>
															<strong>call_bill (call_talk):</strong> Thời gian tính cước cuộc gọi
															<ol>
																<li><span class="text-primary fw-semibold">Cuộc gọi đi: </span> Thời gian hội thoại là khoảng thời gian bị tính cước phí.</li>
																<li><span class="text-success fw-semibold">Cuộc gọi đến: </span> Công thức <strong>call_bill = call_wait + call_talk</strong>.</li>
															</ol>
														</li>
														<li>
															<strong>call_progress:</strong>
															<span>Thời gian thiết lập cuộc gọi, tính từ khi bắt đầu quay số đến khi bắt đầu đổ chuông.</span>
														</li>
														<li>
															<strong>call_wait:</strong>
															<ol>
																<li><span class="text-primary fw-semibold">Cuộc gọi đi:</span> Thời gian từ lúc bắt đầu cuộc gọi đến khi bắt đầu đổ chuông phía người nhận.</li>
																<li><span class="text-success fw-semibold">Cuộc gọi đến: </span> Thời gian đổ chuông, đọc lời chào nếu có. <strong>(call_accepted - call_start)</strong></li>
															</ol>
														</li>
														<li>
															<strong>call_answer:</strong>
															<ol>
																<li><span class="text-primary fw-semibold">Cuộc gọi đi:</span> Thời gian từ lúc bắt đầu cuộc gọi đến khi người nhận bắt máy <strong>(call_accepted - call_start)</strong>.</li>
																<li>Nếu không có hội thoại thì thời gian chờ <strong>= call_duration</strong></li>
															</ol>
														</li>
														<li>
															<strong>call_hold:</strong>
															<span>Thời gian giữ cuộc gọi.</span>
														</li>
													</ul>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
											</div>
										</div>
									</div>
								</div>';
		}
		$this->ss->assign('CALLS_ANNOTATION', $call_annotation);

		if (isset($this->bean->log) && !empty($this->bean->log)) {
			$log_call = json_decode(html_entity_decode($this->bean->log), true);

			// DATETIME WAIT
			if (isset($log_call['call_wait'])) {
				$datetime_wait = global_secondsToTimeFormat(calculateWaitTime($log_call));
				$this->ss->assign('CUS_DATE_WAIT', $datetime_wait);
			}

			// DATETIME TIẾP NHẬN
			$datetime_accept = getCallAcceptDatetime($log_call);
			$this->ss->assign('CUS_DATE_ACCEPT', $datetime_accept);

			// DATETIME HOLD
			// if (isset($log_call['call_hold'])) {
			// 	$datetime_hold = global_secondsToTimeFormat($log_call['call_hold']);
			// 	$this->ss->assign('CUS_DATE_HOLD', $datetime_hold);
			// }

			// call_failed_cause
			if (isset($log_call['call_failed_cause'])) {
				$call_failed_cause = getCallFailedCauseMeaning($log_call['call_failed_cause']);
				$this->ss->assign('CUS_CALL_FAILED_CAUSE', $call_failed_cause);
			}
		}

		// CALL TALK
		$cus_is_success = ($this->bean->is_success == 1) ? 'Thành công' : 'Thất bại';
		$this->ss->assign('CUS_IS_SUCCESS', $cus_is_success);

		// CALL TALK
		if (isset($this->bean->call_talk) && !empty($this->bean->call_talk)) {
			$call_talk = global_secondsToTimeFormat($this->bean->call_talk);
			$this->ss->assign('CUS_CALL_TALK', $call_talk);
		}
		
		// CALL DURATION
		if (isset($this->bean->call_duration) && !empty($this->bean->call_duration)) {
			$call_duration = global_secondsToTimeFormat($this->bean->call_duration);
			$this->ss->assign('CUS_CALL_DURATION', $call_duration);
		}

		// AUDIO MOS
		if (isset($this->bean->call_mos) && !empty($this->bean->call_mos)) {
			$call_mos = getMOSLabel($this->bean->call_mos);
			$this->ss->assign('CUS_CALL_MOS', $call_mos);
		}
	}
}
