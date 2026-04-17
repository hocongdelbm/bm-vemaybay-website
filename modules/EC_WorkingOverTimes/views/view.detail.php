<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_WorkingOverTimesViewDetail extends ViewDetail{

	public $date_of_week = array(
		'Monday' 		=> 'Thứ hai',
		'Tuesday' 	=> 'Thứ ba',
		'Wednesday' 	=> 'Thứ tư',
		'Thursday' 	=> 'Thứ năm',
		'Friday' 		=> 'Thứ sáu',
		'Saturday' 	=> 'Thứ bảy',
		'Sunday' 		=> 'Chủ nhật',
	);
	
	function display(){
		$this->populateCustomButtons();
		$this->populateCustomFields();
		$this->displayJS();
		$this->populateLineItems();
		parent::display();
	}
	
	function populateCustomButtons() {
		global $current_user, $app_list_strings;

		if($this->bean->status == 0) {
			$status_btn = '';
			$status_btn .= '</form><form method="post" action="index.php">
				<input type="hidden" name="module" value="EC_WorkingOverTimes">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<input type="hidden" name="status" value="1">
				<input class="btn btn-primary" type="submit" value="Chuyển đi">
			</form>';
			$this->ss->assign('STATUS_BTN', $status_btn);
		}

		
		if(is_admin($current_user) || $current_user->title == 'QuanLy') {
			// nút duyệt
			if($this->bean->status == 1) {
				$approved_btn = '<input type="button" class="btn btn-primary" id="approved_btn" value="Duyệt">';
				$this->ss->assign('STATUS_BTN', $approved_btn);
			}

			// if(is_admin($current_user)) {
				// chuyển trạng thái
				$change_status = '</form><form method="post" action="index.php">
					<input type="hidden" name="module" value="EC_WorkingOverTimes">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="record" value="'.$this->bean->id.'">
					<select class="box-select" name="status">'.get_select_options_with_id($app_list_strings['overtime_status_list'], (int)$this->bean->status).'</select>
					<input type="submit" class="btn btn-warning" value="Chuyển trạng thái">
				</form>';
				$this->ss->assign('CHANGE_STATUS_BTN', $change_status);
			// }
		}
	}

	function populateCustomFields() {
		// ngày duyệt
		if(!empty($this->bean->user_id_c) && !empty($this->bean->approved_date)) {
			$approved_user = new User;
			$approved_user->retrieve($this->bean->user_id_c);
			$approved_date = date('d-m-Y H:i', strtotime($this->bean->approved_date)) . ' bởi ' . $approved_user->last_name.' '.$approved_user->first_name;
		}

		isset($approved_date) ? $this->ss->assign('DATE_APPROVED', $approved_date) :  $this->ss->assign('DATE_APPROVED', '');
	}

	function displayJS() {
		echo '<script>
			$(document).ready(function() {
				var status = '.$this->bean->status.';
				if(status > 0) {
					$("#edit_button").hide();
					$("input[name=\'Delete\']").hide();
				}

				$("#approved_btn").click(function() {
					$("#overtime_tbl").submit();
				});
			});	
		</script>';	
	}

	function populateLineItems() {
		global $current_user;

		$html = '';
		if((is_admin($current_user) || $current_user->title == 'QuanLy') && $this->bean->status == 1) {
			$hs_arr = [];
			for ($i = 0; $i <= 6; $i += 0.5) {
				$hs_arr[(string)$i] = $i;
			}

			$html .= '<form method="post" action="index.php" id="overtime_tbl">
				<input type="hidden" name="module" value="EC_WorkingOverTimes">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="record" value="'.$this->bean->id.'">
				<input type="hidden" name="status" value="2">
				<input type="hidden" name="user_id_c" value="'.$current_user->id.'">
				<input type="hidden" name="approved_date" value="'.date('d-m-Y H:i:s').'">';
		}

		$html .= '<table id="working_over_time" class="table-details__booking" cellpadding="0" cellspacing="0"><tbody>';

		$sql = '
		SELECT  u.id AS user_id
			, CONCAT(u.last_name, " ", IFNULL(u.first_name, "")) AS full_name 
			, GROUP_CONCAT(
				CONCAT_WS(
					"///"
					, dt.name, dt.register_date, DATE_ADD(dt.from_time, INTERVAL 7 HOUR)
					, DATE_ADD(dt.to_time, INTERVAL 7 HOUR)
					, IFNULL(dt.description, ""), dt.id, dt.multiplier, dt.working_hour
				) SEPARATOR ";"
			) AS detail_line
		FROM ec_workingovertimedetails dt 
		LEFT JOIN users u ON u.id = dt.assigned_user_id AND u.deleted = 0
		WHERE dt.deleted = 0 AND dt.ec_workingovertimes_id_c = "' . $this->bean->id . '"
		GROUP BY u.id';

		$res = $this->bean->db->query($sql);

		
		while($row = $this->bean->db->fetchByAssoc($res)) {

			$html .= '<tr>
						<td colspan="8" style="background: #bfcad3; color: #000;" class="employee-name">Tên nhân viên: <b>'.$row['full_name'].'</b></td>
					</tr>';
			$html .= '
			<tr class="column-title">
				<td class="fw-bold text-center" width="5%">STT</td>
				<td class="fw-bold text-center" width="10%">Thứ</td>
				<td class="fw-bold text-center" width="10%">Ngày</td>
				<td class="fw-bold text-center" width="10%">Từ giờ</td>
				<td class="fw-bold text-center" width="10%">Đến giờ</td>
				<td class="fw-bold text-center" width="10%">Hệ số</td>
				<td class="fw-bold text-center" width="10%">Số giờ công</td>
				<td class="fw-bold text-center">Ghi chú</td>
			</tr>';
			
			$dt_line = explode(";", $row['detail_line']); 

			for($i = 0; $i < count($dt_line); $i++) {
				$dt_line_val = explode("///", $dt_line[$i]);
				$html .= '<tr class="text-center">
					<td>'.($i+1).'</td>
					<td>'.$this->date_of_week[$dt_line_val[0]].'</td>
					<td>'.date('d-m-Y', strtotime($dt_line_val[1])).'</td>
					<td>'.date('H:i', strtotime($dt_line_val[2])).'</td>
					<td>'.date('H:i', strtotime($dt_line_val[3])).'</td>';
				if((is_admin($current_user) || $current_user->title == 'QuanLy') && $this->bean->status == 1) {
					$html .= '<td><select name="multiplier[]" class="multiplier box-select">'.get_select_options_with_id($hs_arr, (int)$dt_line_val[6]).'</select><input type="hidden" name="detail[]" value="'.$dt_line_val[5].'"><input type="hidden" name="from_time[]" value="'.$dt_line_val[2].'"><input type="hidden" name="to_time[]" value="'.$dt_line_val[3].'"></td>';
					$html .= '<td></td>';
				} else {
					$html .= '<td>'.(!empty($dt_line_val[6])?'x'.$dt_line_val[6]:'').'</td>';
					$html .= '<td class="text-center">'.$dt_line_val[7].'</td>';
				}	
				$html .= '<td class="text-start">'.$dt_line_val[4].'</td>
				</tr>';
			}
		}
		$html .= '</tbody></table>';

		if((is_admin($current_user) || $current_user->ttile == 'QuanLy') && $this->bean->status == 1) {
			$html .= '</form>';
		}

		$this->ss->assign('CUS_LINE_ITEMS', $html);
	}
}
?>
