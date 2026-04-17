<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_WorkingOverTimesViewEdit extends ViewEdit {
	function display(){
		if($this->bean->stage != 0 && !isset($_POST['isDuplicate'])) {
			header("Location: index.php?module=EC_WorkingOverTimes&action=DetailView&record=" . $this->bean->id);
			exit;
		} else {
			$this->populateLineItems();
			parent::display();
		}

	}

	function populateLineItems() {

		global $app_list_strings, $app_strings, $mod_strings, $locale;

		$employee_arr = $this->getEmployeeList();

		$i = 1;
		$html = '';
		// $html .= '<link type="text/css" rel="stylesheet" href="./custom/jqueryui/css/chosen.min.css">';
		$html .= '<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">';
		$html .= '<div class="employee-area d-flex flex-column gap-3" id="employee_area">';

		$sql = 'SELECT u.id AS user_id, CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name, GROUP_CONCAT(CONCAT_WS(",", dt.name, dt.register_date, DATE_ADD(dt.from_time, INTERVAL 7 HOUR), DATE_ADD(dt.to_time, INTERVAL 7 HOUR), IFNULL(dt.description, ""), dt.id) SEPARATOR ";") AS detail_line
				FROM ec_workingovertimedetails dt 
				LEFT JOIN users u ON u.id = dt.assigned_user_id AND u.deleted = 0
				WHERE dt.deleted = 0 AND dt.ec_workingovertimes_id_c = "' . $this->bean->id . '" GROUP BY u.id';

		$res = $this->bean->db->query($sql);

		// if($this->bean->db->getRowCount($res) > 0) {
		if($this->bean->db->countRows($res) > 0) {
			while($row = $this->bean->db->fetchByAssoc($res)) {
				$html .= '<div class="employee-row" id="employee_row'.$i.'"><span class="employee-title" id="employee_title'.$i.'"><span class="fw-semibold" id="employee_lbl'.$i.'">Nhân viên thứ ' . $i . '</span><button class="remove_employees" title="Xóa nhân viên" type="button" onclick="markEmployeeRowDeleted('.$i.')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/></svg></button></span>';
				$html .= '<div class="employee-name"><label class="text-label">Tên nhân viên: </label><select class="employee-select" name="employee_id[]">'.get_select_options_with_id($employee_arr, $row['user_id']).'</select></div>';
				$html .= '<table id="tbl_overtimedetail'.$i.'" class="tbl_overtimedetail table-details__booking" border="0" cellpadding="0" cellspacing="0">';
				
				$html .= '<thead><tr id="first-row">
							<th width="5%" align="center">Dòng</th>
							<th width="17%" align="center">Ngày</th>
							<th width="10%" align="center">Từ giờ</th>
							<th width="10%" align="center">Đến giờ</th>
							<th width="50%" align="left">Ghi chú</th>
						</tr><thead><tbody>';

				$dt_line = explode(";", $row['detail_line']); 
				for($k = 0; $k < count($dt_line); $k++) {
					$dt_line_val = explode(",", $dt_line[$k]);
					if(!isset($_REQUEST['isDuplicate']) || $_REQUEST['isDuplicate'] == 'false') 
						$detail_id = $dt_line_val[5];

					$html .= '<tr id="overtime_line'.$i.'_'.($k+1).'">
						<td id="row'.$i.'_'.($k+1).'" class="row-no"><input class="center border-0 fw-semibold" id="cell_no'.$i.'_'.($k+1).'" type="text" value="'.($k+1).'" readonly></td>
						<td>
							<span class="dateTime d-flex align-items-center position-relative">
								<input autocomplete="off" type="text" name="date_chosen'.$i.'[]" id="date_chosen'.$i.'_'.($k+1).'" class="date_input date_chosen" title="Ngày" size="11" maxlength="10" value="'.date("d-m-Y", strtotime($dt_line_val[1])).'">
								<button type="button" id="date_chosen_btn'.$i.'_'.($k+1).'trigger" class="icon_dateTime" onclick="return false;">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
										<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"></path>
										<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"></path>
									</svg>
								</button>
							</span>

							<script>
								Calendar.setup ({ 
									inputField : "date_chosen'.$i.'_'.($k+1).'",
									daFormat : "%d-%m-%Y %H:%M", 
									button : "date_chosen_btn'.$i.'_'.($k+1).'trigger", 
									singleClick : true, 
									dateStr : "", 
									step : 1, 
									weekNumbers:false 
								}); 
							</script>
						</td>
						<td>
							<div class="d-flex gap-1 align-items-center">
								<input type="text" class="time-input" name="from_hour'.$i.'[]" value="'.date("H", strtotime($dt_line_val[2])).'">
								<span>:</span>
								<input type="text" name="from_minute'.$i.'[]" class="time-input" value="'.date("i", strtotime($dt_line_val[2])).'">
							</div>
						</td>
						<td>
							<div class="d-flex gap-1 align-items-center">
								<input type="text" class="time-input" name="to_hour'.$i.'[]" value="'.date("H", strtotime($dt_line_val[3])).'">
								<span>:</span>
								<input type="text" class="time-input" name="to_minute'.$i.'[]" value="'.date("i", strtotime($dt_line_val[3])).'">
							</div>
						</td>
						<td>
							<div class="d-flex gap-2 align-items-center">
								<textarea rows="1" cols="100" class="middle" name="description'.$i.'[]">'.$dt_line_val[4].'</textarea> 
								<button class="remove_employees" title="Xóa dòng làm ngoài giờ" type="button" onclick="markOverTimeRowDeleted(\''.$i.'_'.($k+1).'\', '.$i.')">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"></path></svg>
								</button> 
								<input type="hidden" name="overtime_deleted'.$i.'[]" id="overtime_deleted'.$i.'_'.($k+1).'" value="0">
								<input type="hidden" name="detail_id'.$i.'[]" id="detail_id'.$i.'_'.($k+1).'" value="'.$detail_id.'">
							</div>
						</td>
					</tr>';
				}

				$html .= '</tbody><tfoot>
					<tr class="footer-tr">
						<td colspan="5" class="text-start">
							<input type="button" class="btn btn-primary btnAddRow" ln="'.$i.'" value="Thêm dòng" title="Thêm dòng" />
							<input type="hidden" id="overtime_row_count'.$i.'" value="'.($k + 1).'">
						</td>
					</tr>
				</tfoot>';

				$html .= '</table>
					<input type="hidden" name="employee_deleted[]" id="employee_deleted'.$i.'" value="0">
				</div>';
				$i++;
			}
		} else {
			$html .= '<div class="employee-row" id="employee_row'.$i.'"><span class="employee-title" id="employee_title'.$i.'"><span class="fw-semibold" id="employee_lbl'.$i.'">Nhân viên thứ ' . $i . '</span><button class="remove_employees" title="Xóa nhân viên" type="button" onclick="markEmployeeRowDeleted('.$i.')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ec2029" class="bi bi-dash-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/></svg></button></span>';
			$html .= '<div class="employee-name"><label class="text-label">Tên nhân viên: </label><select class="employee-select" name="employee_id[]">'.get_select_options_with_id($employee_arr, '').'</select></div>';
			$html .= '<table id="tbl_overtimedetail'.$i.'" class="tbl_overtimedetail table-details__booking" border="0" cellpadding="0" cellspacing="0">';
			
			$html .= '<thead><tr id="first-row">
						<th width="5%" class="text-center">Dòng</th>
						<th width="17%" class="text-center">Ngày</th>
						<th width="10%" class="text-center">Từ giờ</th>
						<th width="10%" class="text-center">Đến giờ</th>
						<th width="50%" class="text-start">Ghi chú</th>
					</tr></thead><tbody></tbody>';

			$html .= '<tfoot>
				<tr class="footer-tr">
					<td colspan="5" class="text-start">
						<input type="button" class="btn btn-primary btnAddRow" ln="'.$i.'" value="Thêm dòng" title="Thêm dòng" />
						<input type="hidden" id="overtime_row_count'.$i.'" value="'.$i.'">
					</td>
				</tr>
			</tfoot>';

			$html .= '</table>
				<input type="hidden" name="employee_deleted[]" id="employee_deleted'.$i.'" value="0">
			</div>';
			$i++;
		}

		$html .= '</div>
				<input type="button" class="btn btn-primary" id="btnAddEmployee" value="Thêm nhân viên">
				<input type="hidden" id="employee_row_count" value="'.$i.'">
				<input type="hidden" id="employee_list" value="'.get_select_options_with_id($employee_arr, '').'">
				';


		$this->ss->assign('line_items', $html);
	}

	function getEmployeeList() {
		// Lấy danh sách nhân viên
		$sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
				FROM users WHERE deleted = 0 AND status = "Active" AND title NOT IN ("Admin", "Bot", "Administrator")';

		$res = $this->bean->db->query($sql);

		while($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}

		return $employee_list;
	}

}