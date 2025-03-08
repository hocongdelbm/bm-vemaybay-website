<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_VouchersViewDetail extends ViewDetail {
	function display() {
		$this->getStyle();
		$this->populateCustomFields();
		$this->populateBookingsPanel();
		$this->populateCustomButtons();
		parent::display();
	}

	function getStyle() {
		$style = '';
		$style .= '<link rel="stylesheet" href="modules/'. $this->bean->module_dir .'/css/detail.css">';
		echo $style;
	}

	public function populateCustomFields() {
		// Code
		$voucher_type = '';
		if($this->bean->type == 'single') $voucher_type = '<span class="badge bg-success ms-3">Voucher đơn</span>';
		elseif($this->bean->type == 'group') $voucher_type = '<span class="badge bg-primary ms-3">Voucher nhóm</span>';
		$this->ss->assign('CODE_FIELD', $this->bean->name . $voucher_type);

		// Tình trạng
		$this->ss->assign('STATUS_FIELD', $this->bean->formatStatus());

		// Giảm giá
		$discount = '';
		if($this->bean->reduce_amount > 0) $discount = '<b style="color:red">'.format_number($this->bean->reduce_amount).' VND</b>';
		elseif($this->bean->reduce_percent > 0) $discount = '<b style="color:red">'.format_number($this->bean->reduce_percent).'%</b>';
		$this->ss->assign('DISCOUNT_FIELD', $discount);

		// Giảm tối đa
		$max_discount = '';
		if($this->bean->max_discount && $this->bean->max_discount > 0) $max_discount = format_number($this->bean->max_discount) . ' VND';
		$this->ss->assign('MAX_DISCOUNT_FIELD', $max_discount);

		// Thời hạn
		$duration = 'Từ ' . date('d-m-Y H:i', strtotime($this->bean->start_time)). ' đến ' . date('d-m-Y H:i', strtotime($this->bean->end_time));
		$this->ss->assign('DURATION_FIELD', $duration);

		// Điều kiện sử dụng voucher
		$this->ss->assign('CONDITION_VOUCHER_FIELD', $this->renderConditionVoucher());
	}

	function populateCustomButtons() {

		if($this->bean->type == 'group' && $this->bean->status == 'new' && isAllowedUser()) {
			$btn_active = '<button type="button" id="active_pub_voucher" class="btn btn-primary" record_id="'.$this->bean->id.'">Kích hoạt</button>';
			$this->ss->assign('ACTIVE_BUTTON', $btn_active);
		}
	}

	/**
	 * Render HTML condition voucher field
	 * 
	 * @param string $condition_voucher
	 * @return string HTML
	 */
	public function renderConditionVoucher($condition_voucher = '') {
		if(!$condition_voucher || !is_string($condition_voucher) || empty($condition_voucher)) $condition_voucher = $this->bean->condition_voucher;
		$condition = json_decode(html_entity_decode(trim($condition_voucher)), true);

		if(empty($condition)) return '';

		$html = '<ul class="condition-list">';
		if(isset($condition['min_order_value']) && $condition['min_order_value'] > 0) {
			$html .= '<li>
				Đơn tối thiểu <b>'. format_number($condition['min_order_value']) .' VND</b>
			</li>';
		}

		if(isset($condition['flight_type'])) {
			$label = $condition['flight_type'] == 'domestic' ? 'Nội địa' : 'Quốc tế';
			$html .= '<li>
				Chỉ áp dụng cho chuyến <b>'. $label .'</b>
			</li>';
		}

		if(isset($condition['ticket_type']) && (int)$condition['ticket_type'] > 0) {
			$label = (int)$condition['ticket_type'] == 2 ? 'Khứ hồi' : 'Một chiều';
			$html .= '<li>
				Chỉ áp dụng cho vé <b>'. $label .'</b>
			</li>';
		}

		if(isset($condition['journey']) && strlen($condition['journey']) > 6) {
			$html .= '<li>
				Hành trình áp dụng: <b>'. str_replace(",", ", ", $condition['journey']) .'</b>
			</li>';
		}

		if(isset($condition['number_of_tickets']) && $condition['number_of_tickets'] > 6) {
			$html .= '<li>
				Booking từ <b>'. $condition['number_of_tickets'] .' vé</b> trở lên
			</li>';
		}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Populate bookings panel
	 */
	protected function populateBookingsPanel() {
		$tr = '';
		$sql = "SELECT 
					bv.booking_id,
					b.name AS booking_name,
					bv.discount_amount,
					bv.date_modified
				FROM bookings_vouchers bv
					LEFT JOIN ec_flight_bookings b ON b.id = bv.booking_id
				WHERE bv.voucher_id = '{$this->bean->id}' AND bv.deleted = 0
				ORDER BY bv.date_modified DESC";
		
		$stt = 0;
		$res = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$tr .= '<tr>
				<td>'.(++$stt).'</td>
				<td>
					<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record='. $row['booking_id'] .'" target="_blank">'. $row['booking_name'] .'</a>
				</td>
				<td>'. format_number($row['discount_amount']) .' VND</td>
				<td>'. date('d-m-Y H:i', strtotime($row['date_modified'])) .'</td>
			</tr>';
		}

		$table = '';
		if(!empty($tr)) {
			$table = '<table class="table table-hover">
				<thead>
					<tr>
						<th>STT</th>
						<th>Booking</th>
						<th>Số tiền giảm</th>
						<th>Thời gian sử dụng</th>
					</tr>
				</thead>
				<tbody>'.$tr.'</tbody>
			</table>';
		}
		else {
			$table = '<center>Chưa có booking sử dụng</center>';
		}

		$this->ss->assign('BOOKINGS', $table);
	}
<<<<<<< HEAD

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
=======
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
}
