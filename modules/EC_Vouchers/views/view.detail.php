<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_VouchersViewDetail extends ViewDetail {
	function display() {
		$this->populateCustomFields();
		$this->populateCustomButtons();
		parent::display();
	}

	function populateCustomFields() {
		// Thời hạn
		$duration = date('d/m/Y', strtotime($this->bean->validate_from_date)). ' - ' . date('d/m/Y', strtotime($this->bean->validate_to_date));
		$this->ss->assign('CUS_DURATION', $duration);

		// Hành trình
		if(isset($this->bean->dep_code) && !empty($this->bean->dep_code) && isset($this->bean->arv_code) && !empty($this->bean->arv_code)){
			$journey = $this->bean->dep_code . ' - ' . $this->bean->arv_code;
			$this->ss->assign('CUS_JOURNEY', $journey);
		}

		// Booking sử dụng voucher
		$booking = new EC_Flight_Bookings;
		$booking->retrieve($this->bean->booking_receive_id);
		$this->ss->assign('CUS_BOOKING', '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $booking->id . '" target="_blank">' . $booking->name . '</a>');

		// Điều kiện sử dụng voucher
		if(isset($this->bean->condition_voucher) && !empty($this->bean->condition_voucher)){

			$condition_voucher 	= json_decode(html_entity_decode($this->bean->condition_voucher), true);
			$condition 		= $this->convertConditionVoucher($condition_voucher);
			$text_condition	= '';
			
			foreach($condition as $value){
				if(!empty($value)){
					$text_condition .= '- ' . $value . '. <br>';
				}
			}

			$this->ss->assign('CUS_CONDITION_VOUCHER', $text_condition);
		}
	}

	function populateCustomButtons(){
		global $app_list_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();

		// Nút 'kích hoạt' voucher
		$change_status = '';
		if($this->bean->status == 'new') {
			$active_date = date($date_format.' H:i', strtotime(date($date_format.' H:i'))+7*3600);

			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Vouchers" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Vouchers" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="status" value="active" />
				<input type="hidden" name="active_date" value="'.$active_date.'" />
				<input type="submit" class="btn btn-success" name="btnChangeStatus" value="Kích hoạt" title="Kích hoạt" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		} else if($this->bean->status == 'active'){
			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Vouchers" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Vouchers" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="status" value="cancel" />
				<input type="submit" class="btn btn-secondary" name="btnChangeStatus" value="Hủy voucher" title="Hủy voucher" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}


	}

	function convertConditionVoucher($condition_voucher){
		$text_arr = array(
			'total_amount' => '',
			'journey' => '',
			'total_qty' => '',
			'ticket_type' => '',
			'flight_type' => '',
		);

		$ticket_type_condition = array(
			'1' => 'Nội địa',
			'2' => 'Quốc tế',
		);

		$flight_type_condition = array(
			'1' => 'một chiều',
			'0' => 'khứ hồi',
		);

		foreach($condition_voucher as $condition){
			// Bé hơn
			if($condition['operator'] == '<'){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng nhỏ hơn ' . format_number($condition['value']);

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé nhỏ hơn ' . $condition['value'];

				} 
			} else if($condition['operator'] == '<='){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng nhỏ hơn hoặc bằng ' . format_number($condition['value']) .' VND';

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé nhỏ hơn hoặc bằng ' . $condition['value'];

				} 
			} else if($condition['operator'] == '=='){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng bằng ' . format_number($condition['value']) .' VND';

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé bằng ' . $condition['value'];

				} else if ($condition['field'] == 'journey'){
					$text_arr['journey'] = 'Hành trình phải thuộc các hành trình [' . $condition['value'] . ']';

				} else if ($condition['field'] == 'ticket_type'){
					$text_arr['ticket_type'] = 'Áp dụng cho hành trình ' . $ticket_type_condition[$condition['value']];

				} else if ($condition['field'] == 'flight_type'){
					$text_arr['flight_type'] = 'Áp dụng cho hành trình bay ' . $flight_type_condition[$condition['value']];

				} 
			} else if($condition['operator'] == '>'){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng lớn hơn ' . format_number($condition['value']) .' VND';

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé lớn hơn ' . $condition['value'];

				} 
			} else if($condition['operator'] == '>='){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng lớn hơn hoặc bằng ' . format_number($condition['value']) .' VND';

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé lớn hơn hoặc bằng ' . $condition['value'];

				} 
			} else if($condition['operator'] == '!='){
				if($condition['field'] == 'total_amount'){
					$text_arr['total_amount'] = 'Tổng giá trị đơn hàng khác ' . format_number($condition['value']) .' VND';

				} else if ($condition['field'] == 'total_qty'){
					$text_arr['total_qty'] = 'Tổng số vé khác ' . $condition['value'];

				} else if ($condition['field'] == 'journey'){
					$text_arr['journey'] = 'Hành trình khác các hành trình [ ' . $condition['value'] . ']';

				} 
			}
		} 

		return $text_arr;
	}

}
