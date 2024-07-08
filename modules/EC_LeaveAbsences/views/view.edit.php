<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_LeaveAbsencesViewEdit extends ViewEdit{
	function display() {

		if($this->bean->status != 0 && !isset($_POST['isDuplicate'])) {
			header("Location: index.php?module=EC_LeaveAbsences&action=DetailView&record=" . $this->bean->id);
			exit;
		} else {
			$this->populateCustomFields();
			parent::display();
		}
	}
	
	function populateCustomFields() {
		// số ngày nghỉ
		$part_date = (strtotime($this->bean->part_date) != false?date('d-m-Y', strtotime($this->bean->part_date)):'');

		$absence_days = '<input type="text" name="absence_days" id="absence_days" value="'.$this->bean->absence_days.'">';

		$absence_days .='
			<div class="d-flex gap-2 flex-column mt-2">
				<div class="absence_days--notes text-label">Nếu nghỉ 0.5 ngày thì chọn buổi sáng hay buổi chiều và của ngày nào. Còn lại chọn không có.</div>
				<div class="absence_days--option d-flex flex-column gap-2">
					<div class="partofday--wrap d-flex gap-2 align-items-center">
						<input type="radio" id="none" class="partofday form-check-input" name="partofday" value="0" '.($this->bean->partofday==0?'checked':'').'>
						<label class="form-check-label" for="none">Không có</label>
					</div>
					<div class="partofday--wrap d-flex gap-2 align-items-center">
						<input type="radio" id="morning" class="partofday form-check-input" name="partofday"  value="1" '.($this->bean->partofday==1?'checked':'').'>
						<label class="form-check-label" for="morning">Buổi sáng</label>

						<span class="dateTime position-relative" style="display:none;">
							<input type="text" class="date_chosen date_input" name="date_chosen_m" id="date_chosen_m" size="11" maxlength="10" value="'.($this->bean->partofday==1?$part_date:'').'">

							<button class="icon_dateTime" type="button" id="date_chosen_m_trigger" onclick="return false;">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
									<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
									<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
								</svg>
							</button>
						</span>
					</div>
					<div class="partofday--wrap d-flex gap-2 align-items-center">
						<input type="radio" id="afternoon" class="partofday form-check-input" name="partofday" value="2" '.($this->bean->partofday==2?'checked':'').'>
						<label class="form-check-label" for="afternoon">Buổi chiều</label>

						<span class="position-relative dateTime" style="display:none;">
							<input type="text" class="date_chosen date_input" name="date_chosen_a" id="date_chosen_a" size="11" maxlength="10" value="'.($this->bean->partofday==2?$part_date:'').'">
							<button class="icon_dateTime" type="button" id="date_chosen_a_trigger" onclick="return false;">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
									<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
									<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
								</svg>
							</button>
						</span>
					</div>
				</div>
				<input type="hidden" name="part_date_chosen" id="part_date_chosen" value="'.$part_date.'">
			</div>';
		$this->ss->assign('CUS_ABSENCE_DAYS', $absence_days);
	}

}
?>
