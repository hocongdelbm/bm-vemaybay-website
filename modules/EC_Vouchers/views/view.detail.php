<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_VouchersViewDetail extends ViewDetail {
	function display() {
		var_dump(
			$this->bean->uploadWebsite($this->bean->website, [
				'name' => $this->bean->name,
				'status' => $this->bean->status,
				'campaign_name' => $this->bean->campaign_name,
				'campaign_id' => $this->bean->campaign_id,
				'validate_from_date' => $this->bean->validate_from_date,
				'validate_to_date' => $this->bean->validate_to_date,
				'reduce_amount' => $this->bean->reduce_amount,
				'reduce_percent' => $this->bean->reduce_percent,
				'max_discount' => $this->bean->max_discount,
				'quantity' => $this->bean->quantity,
				'quantity_used' => 0,
				'condition_voucher' => $this->bean->condition_voucher
			])
		);
		die();

		$this->css();
		$this->populateFields();
		$this->populateButtons();
		parent::display();
	}

	private function css() {
		$styles = '<link rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/detail.css">';
		echo $styles; 
	}

	public function populateFields() {
		// Thời hạn
		$duration = date('d/m/Y', strtotime($this->bean->validate_from_date)). ' - ' . date('d/m/Y', strtotime($this->bean->validate_to_date));
		$this->ss->assign('CUS_DURATION', $duration);

		// Mệnh giá
		$amount = $this->bean->reduce_amount > 0 ? format_number($this->bean->reduce_amount) : $this->bean->reduce_percent . "%";
		$this->ss->assign('CUS_AMOUNT', $amount);

		// Tình trạng
		$this->ss->assign('CUS_STATUS', $this->bean->getFormatStatus());


		// Booking sử dụng voucher
		if($this->bean->type == 'single') {
			$booking = '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $this->bean->booking_receive_id . '" target="_blank">'. $this->bean->booking . '</a>';
		}
		else {
			$booking = $this->renderPopupListBookings();
		}
		$this->ss->assign('CUS_BOOKING', $booking);

		// Khách hàng
		$account = '';
		if(!empty($this->bean->account_name) || !empty($this->bean->account_phone)) {
			$account = '<ul class="list-group list-group-flush">
				<li class="list-group-item">'.$this->bean->account_name.'</li>
				<li class="list-group-item">'.$this->bean->account_phone.'</li>
				<li class="list-group-item">'.$this->bean->account_email.'</li>
			</ul>';
		}
		$this->ss->assign('CUS_ACCOUNT', $account);

		// Điều kiện sử dụng voucher
		if(isset($this->bean->condition_voucher) && !empty($this->bean->condition_voucher)){
			$condition_voucher = json_decode(html_entity_decode($this->bean->condition_voucher), true);
			$condition = $this->convertConditionVoucher($condition_voucher);
			$text_condition	= '';
			
			foreach($condition as $value){
				if(!empty($value)){
					$text_condition .= '- ' . $value . '. <br>';
				}
			}

			$this->ss->assign('CUS_CONDITION_VOUCHER', $text_condition);
		}
	}

	public function populateButtons(){
		global $timedate;
		$date_format = $timedate->get_date_format();
		
		if($this->bean->status == 'active'){
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

	protected function convertConditionVoucher($condition_voucher){
		$text_arr = [
			'total_amount' => '',
			'journey' => '',
			'total_qty' => '',
			'ticket_type' => '',
			'flight_type' => '',
		];

		$ticket_type_condition = [
			'1' => 'Nội địa',
			'2' => 'Quốc tế',
		];

		$flight_type_condition = [
			'1' => 'một chiều',
			'0' => 'khứ hồi',
		];

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

	protected function renderPopupListBookings() {
		return '
			<a class="view-bookings" data-bs-toggle="modal" data-bs-target="#modal_list_booking">Xem danh sách</a>
			<div class="modal" id="modal_list_booking">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Danh sách booking dùng voucher</h4>
						</div>
						<div class="modal-body">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>STT</th>
										<th>Booking</th>
										<th>Ngày tạo</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>1</td>
										<td>VJ21IO3J12IL</td>
										<td>10/11/2024 15:30</td>
									</tr>
									<tr>
										<td>2</td>
										<td>VJ21IO3J12IL</td>
										<td>10/11/2024 15:30</td>
									</tr>
									<tr>
										<td>3</td>
										<td>VJ21IO3J12IL</td>
										<td>10/11/2024 15:30</td>
									</tr>
								</tbody>
							</table>
							<ul class="pagination pagination-sm">
								<li class="page-item disabled">
									<a class="page-link" href="#">
										<svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M15 6L9 12L15 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
									</a>
								</li>
								<li class="page-item">
									<a class="page-link" href="#">
										<svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M9 6L15 12L9 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
									</a>
								</li>
							</ul>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Đóng</button>
						</div>
					</div>
				</div>
			</div>
		';
	}
}
