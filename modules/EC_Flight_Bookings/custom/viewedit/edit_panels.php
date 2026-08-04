<?php
if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

trait EditPanelsTrait
{
	public function populateLineItineraries()
	{
		global $app_list_strings, $timedate;

		$date_format = $timedate->get_date_format();

		// Khi nhân bản booking, giữ lại mã booking cũ để JS/Save phân biệt dữ liệu nhân bản.
		$booking_prev_name = (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true') ? $this->bean->name : '';

		// Query hành trình gốc của booking; row HTML sẽ được render phía JS từ JSON.
		$sql = "SELECT i.id AS detail_id,
					i.direction,
					i.airline_code,
					i.flight_number,
					i.ticket_class,
					i.departure,
					i.arrival,
					i.departure_date,
					i.arrival_date,
					i.base_price,
					i.is_layover,
					i.description,
					i.time_limit
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '{$this->bean->id}'
					AND add_type = 0 AND i.deleted = 0
				ORDER BY i.direction, i.transit_order, i.date_entered, i.departure_date";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		/**
		 * BƯỚC 1: THU THẬP DỮ LIỆU THÀNH MẢNG (CHỈ DỮ LIỆU, KHÔNG HTML)
		 * Row sẽ được render bằng JS (`renderInitialItineraries()` + `insertItineraryLine(ln)`).
		 */
		$itineraries_data = [];
		if (!empty($this->bean->id)) {
			$i = 0;
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$itineraries_data[] = [
					'db_id' => $row['detail_id'],
					'id' => $row['detail_id'],
					'direction' => (int) $row['direction'],
					'airline_code' => $row['airline_code'],
					'flight_number' => $row['flight_number'],
					'ticket_class' => $row['ticket_class'],
					'departure' => $row['departure'],
					'arrival' => $row['arrival'],
					'departure_date' => $row['departure_date'] != '' ? date($date_format, strtotime($row['departure_date'])) : '',
					'departure_h' => $row['departure_date'] != '' ? date('H', strtotime($row['departure_date'])) : '',
					'departure_m' => $row['departure_date'] != '' ? date('i', strtotime($row['departure_date'])) : '',
					'arrival_date' => $row['arrival_date'] != '' ? date($date_format, strtotime($row['arrival_date'])) : '',
					'arrival_h' => $row['arrival_date'] != '' ? date('H', strtotime($row['arrival_date'])) : '',
					'arrival_m' => $row['arrival_date'] != '' ? date('i', strtotime($row['arrival_date'])) : '',
					'time_limit_date' => $row['time_limit'] != '' ? date($date_format, strtotime($row['time_limit'])) : '',
					'time_limit_h' => $row['time_limit'] != '' ? date('H', strtotime($row['time_limit'])) : '',
					'time_limit_m' => $row['time_limit'] != '' ? date('i', strtotime($row['time_limit'])) : '',
					'base_price' => (float) $row['base_price'],
					'is_layover' => (int) $row['is_layover'],
				];

				// Lưu thông tin airline/ticket_class theo direction để dùng ở chỗ khác
				if ($row['direction'] == '0') {
					$this->_outbound_airline = $row['airline_code'];
					$this->_outbound_ticket_class = $row['ticket_class'];
					if ($i == 0) {
						$this->_journey = $row['departure'] . '-' . $row['arrival'];
					} else {
						$this->_journey = substr($this->_journey, 0, 3) . '-' . $row['arrival'];
					}
				}

				if ($row['direction'] == '1') {
					$this->_inbound_airline = $row['airline_code'];
					$this->_inbound_ticket_class = $row['ticket_class'];
				}

				$i++;
			}
		}

		/**
		 * BƯỚC 2: TẠO HTML KHUNG BẢNG (không render row ở PHP)
		 * - Header
		 * - <tbody id="iti_tbody"></tbody> rỗng để JS render
		 * - Footer với các input hidden cần thiết + JSON data
		 */

		$html = '<table id="tbl_line_itineraries" class="table-vertical__mobile table-edit__booking table-details__booking" border="0" cellpadding="0" cellspacing="0">';
		$html .= '<thead>
			<tr id="iti_first_row">
				<th scope="col" style="width:8%;" class="text-center">Chiều</th>
				<th scope="col" style="width:5%;" class="text-center">Mã hãng</th>
				<th scope="col" style="width:7%;" class="text-center">Số hiệu</th>
				<th scope="col" style="width:11%;" class="text-center">Hạng vé</th>
				<th scope="col" style="width:5%;" class="text-center">Nơi đi</th>
				<th scope="col" style="width:5%;" class="text-center">Nơi đến</th>
				<th scope="col" style="width:14%;" class="text-center">Ngày giờ đi</th>
				<th scope="col" style="width:14%;" class="text-center">Ngày giờ đến</th>
				<th scope="col" style="width:14%;" class="text-center">Hạn giữ chỗ</th>
				<th scope="col" style="width:8%;" class="text-center">Giá cơ bản</th>
				<th scope="col" style="width:2%;" class="text-center">Quá cảnh</th>
				<th scope="col" style="width:3%;" class="text-center">&nbsp;</th>
			</tr>
		</thead>';
		$html .= '<tbody id="iti_tbody"></tbody>';
		$html .= '<tr id="iti_last_row" class="footer-tr">';
		$html .= '<td colspan="12" class="text-start">';
		$html .= '<input type="hidden" name="discount_percent_list" id="discount_percent_list" value="' . get_select_options_with_id($app_list_strings['discount_percent_list'], '') . '" />';
		$html .= '<input type="hidden" name="direction_list" id="direction_list" value="' . get_select_options_with_id($app_list_strings['bk_direction_list'], '') . '" />';
		$html .= '<input type="hidden" name="passenger_type_list" id="passenger_type_list" value="' . get_select_options_with_id($app_list_strings['passenger_type_list'], '') . '" />';
		$html .= '<input type="hidden" name="passenger_salutation_list" id="passenger_salutation_list" value="' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], '') . '" />';
		$html .= '<input type="hidden" id="iti_row_count" name="iti_row_count" value="' . $row_count . '" />';
		$html .= '<input type="hidden" id="booking_prev_name" name="booking_prev_name" value="' . $booking_prev_name . '" />';
		$html .= '<input type="hidden" id="journey" name="journey" value="' . $this->_journey . '" />';
		$html .= '<input type="hidden" id="iti_data_json" value=\'' . htmlspecialchars(json_encode($itineraries_data), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<input type="button" class="btn btn-primary" id="btnItineraryAddRow" value="Thêm dòng" title="Thêm dòng" />';
		$html .= ' Số dòng = <label id="lbl_iti_row_count">' . $row_count . '</label>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		/**
		 * BƯỚC 3: GỬI DỮ LIỆU VỀ TEMPLATE
		 */
		// Gửi HTML khung bảng (row sẽ được render bằng JS)
		$this->ss->assign('LINE_ITINERARIES', $html);
	}

	public function populateLineDetails()
	{
		$supplier_cus_sql = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		// Danh sách nhà cung cấp còn theo dõi, truyền xuống JS để render select NCC từng dòng.
		$supplier_list = str_replace('"', "'", myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', '', 'id', $supplier_cus_sql));

		// Query chi tiết vé/giá gốc; row HTML sẽ được render phía JS từ JSON.
		$sql = "SELECT id AS detail_id
					   ,direction
					   ,passenger_type
					   ,quantity
					   ,unit_price
					   ,tax_and_fee
					   ,airport_fee
					   ,admin_fee, vat_admin, admin_fee_no_vat
					   ,service_fee
					   ,total_price
					   ,total_bought_price
					   ,fee_bought
					   ,supplier_id
					   ,supplier_discount
				FROM ec_booking_details
				WHERE booking_id='" . $this->bean->id . "'
				AND deleted = 0
				ORDER BY direction, passenger_type, date_entered ";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		/**
		 * BƯỚC 1: THU THẬP DỮ LIỆU THÀNH MẢNG (CHỈ DỮ LIỆU, KHÔNG HTML)
		 * Row sẽ được render bằng JS (`renderInitialDetails()` + `insertDetailLine(ln)`).
		 */
		$details_data = [];
		if (!empty($this->bean->id)) {
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$details_data[] = [
					'db_id' => $row['detail_id'],
					'id' => $row['detail_id'],
					'direction' => (int) $row['direction'],
					'passenger_type' => (int) $row['passenger_type'],
					'quantity' => (int) $row['quantity'],
					'unit_price' => (float) $row['unit_price'],
					'tax_and_fee' => (float) $row['tax_and_fee'],
					'airport_fee' => (float) $row['airport_fee'],
					'admin_fee' => (float) $row['admin_fee'],
					'admin_fee_no_vat' => (float) $row['admin_fee_no_vat'],
					'vat_admin' => (float) $row['vat_admin'],
					'service_fee' => (float) $row['service_fee'],
					'total_price' => (float) $row['total_price'],
					'total_bought_price' => (float) ((float) $row['total_bought_price'] ? $row['total_bought_price'] : ($row['total_price'] - ($row['service_fee']) * $row['quantity'])),
					'fee_bought' => (float) $row['fee_bought'],
					'supplier_id' => $row['supplier_id'],
					'supplier_discount' => (float) $row['supplier_discount'],
				];
			}
		}

		$total_qty = isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : (isset($this->bean->total_qty) ? $this->bean->total_qty : 0);
		$subtotal_amount = isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : (isset($this->bean->subtotal_amount) ? $this->bean->subtotal_amount : 0);
		$total_bought_amount = isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : (isset($this->bean->total_bought_amount) ? $this->bean->total_bought_amount : 0);

		/**
		 * BƯỚC 2: TẠO HTML KHUNG BẢNG (không render row ở PHP)
		 * - Header
		 * - <tbody id="bkd_tbody"></tbody> rỗng để JS render
		 * - Footer với các input hidden cần thiết + JSON data
		 */
		$html = '<table id="tbl_line_details" class="table-vertical__mobile table-edit__booking table-details__booking" cellpadding="0" cellspacing="0" border="0">';
		$html .= '<thead>
				<tr id="bkd_first_row">
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Chiều</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Loại HK</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">SL</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Giá cơ bản</th>
					<th scope="col" style="width:6%;" class="text-center fw-semibold">VAT</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí sân bay</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí admin</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí dịch vụ</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Thành tiền</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Giá mua</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Chiết khấu</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Phí xuất vé</th>
					<th scope="col" class="text-center fw-semibold">NCC</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">&nbsp;</th>
				</tr>
			</thead>';
		$html .= '<tbody id="bkd_tbody"></tbody>';
		$html .= '<tr id="bkd_last_row" class="footer-tr">';
		$html .= '<td colspan="2">';
		$html .= '<input type="hidden" name="bkd_row_count" id="bkd_row_count" value="' . $row_count . '" />';
		$html .= '<input type="hidden" name="supplier_list" id="supplier_list" value="' . $supplier_list . '" />';
		$html .= '<input type="hidden" id="bkd_data_json" value=\'' . htmlspecialchars(json_encode($details_data), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<div class="d-flex align-items-center gap-2">';
		$html .= '<input type="button" class="btn btn-primary" id="btnDetailAddRow" value="Thêm dòng" title="Thêm dòng" />';
		$html .= '<p>Số dòng = <span id="lbl_bkd_row_count">' . $row_count . '</span></p>';
		$html .= '</div>';
		$html .= '</td>';
		$html .= '<td data-label="Tổng số vé"><input type="text" readonly="readonly" name="total_qty" id="total_qty" value="' . format_number($total_qty) . '" /></td>';
		$html .= '<td class="hide-mobile" colspan="5"></td>';
		$html .= '<td data-label="Tổng thành tiền" class="text-center">';
		$html .= '<input type="text" class="text-danger" readonly="readonly" name="subtotal_amount" id="subtotal_amount" value="' . format_number($subtotal_amount) . '" />';
		$html .= '</td>';
		$html .= '<td data-label="Tổng giá mua" class="text-center">';
		$html .= '<input type="text" class="text-danger" readonly="readonly" name="total_bought_amount" id="total_bought_amount" value="' . format_number($total_bought_amount) . '" />';
		$html .= '</td>';
		$html .= '<td class="hide-mobile" colspan="4"></td>';
		$html .= '</tr>';
		$html .= '</table>';

		$this->ss->assign('LINE_DETAILS', $html);
	}

	public function populateLinePassengers()
	{
		global $timedate;

		// Luồng hành lý mới: PHP chỉ xuất khung bảng + JSON, JS render row và option hành lý.

		// Định dạng ngày tháng
		$date_format = $timedate->get_date_format();

		// Điều kiện SQL để lấy nhà cung cấp
		$sql_supplier = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		/**
		 * BƯỚC 1: LẤY DỮ LIỆU TỪ DATABASE
		 */
		$sql = "SELECT p.id,
			p.type,
			p.salutation,
			p.name,
			p.birthday,
			p.eticket_outbound,
			p.eticket_inbound,
			p.eluggage_outbound,
			p.eluggage_inbound,
			p.pnr_outbound,
			p.pnr_inbound,
			p.supplier_id,
			p.supplier_inbound_id,
			p.luggage_price,
			p.luggage_price_inbound,
			p.luggage_purchase_no_vat,
			p.vat_luggage_purchase,
			p.luggage_purchase,
			p.luggage_purchase_text,
			p.luggage_purchase_inbound_no_vat,
			p.vat_luggage_purchase_inbound,
			p.luggage_purchase_inbound,
			p.luggage_purchase_text_inbound,
			p.luggage_index_outbound,
			p.luggage_index_inbound,
			p.hand_baggage_outbound,
			p.hand_baggage_inbound,
			p.passport_number
		FROM ec_booking_passengers p
		WHERE p.booking_id = '{$this->bean->id}'
			AND p.booking_id IS NOT NULL
			AND p.booking_id != ''
			AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
			AND p.deleted = 0
		ORDER BY p.type, p.date_entered";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		/**
		 * BƯỚC 2: CHUYỂN ĐỔI DỮ LIỆU THÀNH MẢNG (CHỈ DỮ LIỆU, KHÔNG HTML)
		 */
		$passengers_data = [];
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$passenger_id = isset($_POST['isDuplicate']) && (string) $_POST['isDuplicate'] === 'true' ? '' : $row['id'];

			$birthday = '';
			if (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00') {
				$birthday = date($date_format, strtotime($row['birthday']));
			}

			$passengers_data[] = [
				'id' => $passenger_id,
				'db_id' => $row['id'],
				'type' => (int) $row['type'],
				'salutation' => (int) $row['salutation'],
				'name' => $row['name'],
				'birthday' => $birthday,
				'pnr_outbound' => $row['pnr_outbound'],
				'pnr_inbound' => $row['pnr_inbound'],
				'eticket_outbound' => $row['eticket_outbound'],
				'eticket_inbound' => $row['eticket_inbound'],
				'eluggage_outbound' => $row['eluggage_outbound'],
				'eluggage_inbound' => $row['eluggage_inbound'],
				'luggage_price' => (float) $row['luggage_price'],
				'luggage_price_inbound' => (float) $row['luggage_price_inbound'],
				'luggage_purchase' => (float) $row['luggage_purchase'],
				'luggage_purchase_text' => $row['luggage_purchase_text'],
				'luggage_purchase_inbound' => (float) $row['luggage_purchase_inbound'],
				'luggage_purchase_text_inbound' => $row['luggage_purchase_text_inbound'],
				'luggage_index_outbound' => $row['luggage_index_outbound'],
				'luggage_index_inbound' => $row['luggage_index_inbound'],
				'hand_baggage_outbound' => $row['hand_baggage_outbound'],
				'hand_baggage_inbound' => $row['hand_baggage_inbound'],
				'supplier_id' => $row['supplier_id'],
				'supplier_inbound_id' => $row['supplier_inbound_id'],
			];
		}

		/**
		 * BƯỚC 3: TẠO HTML KHUNG BẢNG (không render row ở PHP)
		 * - Header
		 * - <tbody id="psg_tbody"></tbody> rỗng để JS render
		 * - Footer với các input hidden cần thiết
		 */
		$baggage_options_outbound = $this->bean->getBaggageOptionsData($this->bean->airline);
		$baggage_options_inbound = $this->bean->getBaggageOptionsData($this->bean->airline_inbound);

		$html = '<table id="tbl_line_passengers" class="table-vertical__mobile table-edit__booking table-config table-details__booking" cellpadding="0" cellspacing="0" border="0">';
		$html .= '<thead>';
		$html .= '<tr id="psg_first_row">';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:10%;">Loại HK</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:8%;">Danh xưng</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:20%;">Họ tên</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:12%;">Ngày sinh</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:10%;">PNR lượt đi</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:10%;">PNR lượt về</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:12%;">Số vé lượt đi</th>';
		$html .= '<th scope="col" class="text-center fw-semibold" style="width:12%;">Số vé lượt về</th>';
		$html .= '<th scope="col">&nbsp;</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody id="psg_tbody"></tbody>';
		$html .= '<tr id="psg_last_row" class="footer-tr">';
		$html .= '<td colspan="13" class="text-start">';
		$html .= '<input type="button" class="btn btn-primary" id="btnPassengerAddRow" value="Thêm dòng" title="Thêm dòng" />';
		$html .= ' Số dòng = <label id="lbl_psg_row_count">' . $row_count . '</label>';
		$html .= '<input type="hidden" name="psg_row_count" id="psg_row_count" value="' . $row_count . '" />';
		$html .= '<input type="hidden" id="booking_status" value="' . $this->bean->booking_status . '" >';
		$html .= '<input type="hidden" id="baggage_options_outbound" value=\'' . htmlspecialchars(json_encode($baggage_options_outbound), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<input type="hidden" id="baggage_options_inbound" value=\'' . htmlspecialchars(json_encode($baggage_options_inbound), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '<input type="hidden" id="psg_passengers_data_json" value=\'' . htmlspecialchars(json_encode($passengers_data), ENT_QUOTES, 'UTF-8') . '\' />';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';

		/**
		 * BƯỚC 4: GỬI DỮ LIỆU VỀ TEMPLATE
		 */
		// Gửi HTML khung bảng (row sẽ được render bằng JS)
		$this->ss->assign('LINE_PASSENGERS', $html);
	}

	/**
	 * Old function
	 * 
	 * @deprecated
	 */
	public function populateLinePassengersOld()
	{
		global $app_list_strings, $timedate, $current_user;

		// Luồng hành lý cũ: PHP render đầy đủ từng dòng hành khách và hành lý.
		$date_format = $timedate->get_date_format();
		$sql_supplier = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		// Query hành khách gốc của booking theo schema hành lý cũ.
		$sql = "SELECT p.id,
					p.type,
					p.salutation,
					p.name,
					p.birthday,
					p.eticket_outbound,
					p.eticket_inbound,
					p.eluggage_outbound,
					p.eluggage_inbound,
					p.pnr_outbound,
					p.pnr_inbound,
					p.luggage_price,
					p.luggage_price_inbound,
					p.luggage_purchase,
					p.luggage_purchase_inbound,
					p.supplier_id,
					p.supplier_inbound_id,
					p.luggage_index_outbound,
					p.luggage_index_inbound
				FROM ec_booking_passengers p
				WHERE p.booking_id = '{$this->bean->id}'
					AND p.booking_id IS NOT NULL
					AND p.booking_id != ''
					AND add_type IS NULL
					AND p.deleted = 0
				ORDER BY p.type, p.date_entered";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		$html = '';
		$html .= '<table id="tbl_line_passengers" class="table-vertical__mobile table-edit__booking table-config table-details__booking" cellpadding="0" cellspacing="0" border="0">';

		$html .= '<thead>
				<tr id="psg_first_row">
					<th scope="col" class="text-start fw-semibold" style="width:5%;">Loại HK</th>
					<th scope="col" class="text-center fw-semibold" style="width:5%;">Danh xưng</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Họ tên</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Ngày sinh</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé HL lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé HL lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">PNR lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">PNR lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Hành lý lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Hành lý lượt về</th>
					<th scope="col">&nbsp;</th>
				</tr>
			</thead>';

		$i = 0;
		if (!empty($this->bean->id)) {
			$booking_date = $this->bean->date_entered;
		} else {
			$booking_date = '';
		}

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$passenger_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['id'];
			$luggage_index_out = '';
			$luggage_index_in = '';

			// Hành lý lượt di
			$psg_luggage_price_out = generateLuggage($booking_date, $this->_outbound_airline, $this->_outbound_ticket_class, $row['type'], (int) $row['luggage_index_outbound'], 1, (int) $row['luggage_price']);
			if (!empty($row['luggage_index_outbound'])) {
				$luggage_index_out .= '<input type="hidden" name="psg_luggage_ob_ind[]" value="1" />';
			}

			// Hành lý lượt về
			if (is_null($row['luggage_index_inbound']) || empty($row['luggage_index_inbound'])) {
				$row['luggage_index_inbound'] = $row['luggage_price_inbound'];
			}
			$psg_luggage_price_in = generateLuggage($booking_date, $this->_inbound_airline, $this->_inbound_ticket_class, $row['type'], (int) $row['luggage_index_inbound'], 1, (int) $row['luggage_price_inbound']);
			if (!empty($row['luggage_index_inbound'])) {
				$luggage_index_in .= '<input type="hidden" name="psg_luggage_ib_ind[]" value="1" />';
			}

			if (empty($psg_luggage_price_out)) {
				$psg_luggage_price_out = '<option value="0">--không--</option>';
			}
			if (empty($psg_luggage_price_in)) {
				$psg_luggage_price_in = '<option value="0">--không--</option>';
			}

			##### Line 1 (Thông tin hành khách) #####
			$html .= '<tr id="psg_line_' . $i . '" class="psg_line">';

			// Loại khách hàng
			$html .= '<td data-label="Loại HK">
				<select name="psg_traveller_type[]" id="psg_traveller_type' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int) $row['type']) . '
				</select>
			</td>';

			// Danh xưng
			$html .= '<td data-label="Danh xưng">
				<select name="psg_salutation[]" id="psg_salutation' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int) $row['salutation']) . '
				</select>
			</td>';

			// Họ tên
			$html .= '<td data-label="Họ tên">
				<input type="text" name="psg_full_name[]" id="psg_full_name' . $i . '" value="' . $row['name'] . '" class="text-start" maxlength="128" />
			</td>';

			// Ngày sinh
			$html .= '<td data-label="Ngày sinh">
				<div class="d-flex align-items-center gap-1">
					<input type="text" name="psg_birthday[]" id="psg_birthday' . $i . '" 
						value="' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date($date_format, strtotime($row['birthday'])) : '') . '" maxlength="10" />
					<img class="flex-fill cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="psg_birthday_trigger' . $i . '" align="absmiddle" />
				</div>
			</td>';

			// Số vé lượt đi
			$html .= '<td data-label="Số vé lượt đi"><input type="text" name="psg_eticket_outbound[]" id="psg_eticket_outbound' . $i . '" value="' . $row['eticket_outbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé lượt về
			$html .= '<td data-label="Số vé lượt về"><input type="text" name="psg_eticket_inbound[]" id="psg_eticket_inbound' . $i . '" value="' . $row['eticket_inbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé HL lượt đi
			$html .= '<td data-label="Số vé HL lượt đi"><input type="text" name="psg_eluggage_outbound[]" id="psg_eluggage_outbound' . $i . '" value="' . $row['eluggage_outbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé HL lượt về
			$html .= '<td data-label="Số vé HL lượt về"><input type="text" name="psg_eluggage_inbound[]" id="psg_eluggage_inbound' . $i . '" value="' . $row['eluggage_inbound'] . '" class="text-center" maxlength="25" /></td>';
			// PNR lượt đi
			$html .= '<td data-label="PNR lượt đi"><input type="text" name="psg_pnr_outbound[]" id="psg_pnr_outbound' . $i . '" value="' . $row['pnr_outbound'] . '" class="text-center" maxlength="30" /></td>';
			// PNR lượt về
			$html .= '<td data-label="PNR lượt về"><input type="text" name="psg_pnr_inbound[]" id="psg_pnr_inbound' . $i . '" value="' . $row['pnr_inbound'] . '" class="text-center" maxlength="30" /></td>';

			// Hành lý lượt đi
			$html .= '<td data-label="HL lượt đi">
				<select name="psg_luggage_price[]" id="psg_luggage_price' . $i . '" class="text-start w-100" onchange="calculateLuggagePrice()">
					' . $psg_luggage_price_out . '
				</select>
				' . $luggage_index_out . '
			</td>';

			// Hành lý lượt về
			$html .= '<td data-label="HL lượt về">
				<select name="psg_luggage_price_inbound[]" id="psg_luggage_price_inbound' . $i . '" class="text-start w-100" onchange="calculateLuggagePrice()">
					' . $psg_luggage_price_in . '
				</select>
				' . $luggage_index_in . '
			</td>';

			// Nút xóa
			$html .= '<td data-label="Xóa dòng" class="text-center align-middle">
						<button type="button" title="Xóa" class="button-remove-in-edit" onclick="markPassengerRowDeleted(' . $i . ')">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
						</button>
						<input type="hidden" name="psg_deleted[]" id="psg_deleted' . $i . '" value="0" />
						<input type="hidden" name="psg_id[]" id="psg_id' . $i . '" value="' . $passenger_id . '" readonly />
					</td>';
			$html .= '</tr>';

			#####  Line 2 (Hành lý đi nếu có)  #####
			$html .= '<tr id="psg_line_desc_' . $i . '">
					<td data-label="Thông tin HL đi" class="row_psg_price" colspan="13">
						<div class="psg_price-wrap d-flex gap-2 align-items-center">
							<div class="col_psg_price flex-fill">
								<span class="text-label">Giá mua HL lượt đi (VAT): </span>
								<input type="text" name="psg_luggage_purchase[]" id="psg_luggage_purchase' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									value="' . format_number($row['luggage_purchase']) . '"
									maxlength="25"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);"
									onpaste="calculateLugPurchasePrice(' . $i . ', 0);"
								/>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">NCC HL lượt đi: </span>
								<select name="psg_luggage_supplier[]" id="psg_luggage_supplier' . $i . '" class="psg_luggage_purchase_select">
									<option value=""></option>
									' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $sql_supplier) . '
								</select>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">Giá mua HL lượt đi: </span>
								<input type="text" name="psg_detail_lug_pur_no_vat[]" id="psg_detail_lug_pur_no_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);" 
								/>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">VAT giá mua HL lượt đi: </span>
								<input type="text" name="psg_detail_lug_pur_vat[]" id="psg_detail_lug_pur_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);"
								/>
							</div>
						</div>
					</td>
				</tr>';

			#####  Line 3 (Hành lý về nếu có)  #####
			$html .= '<tr id="psg_line_lug_' . $i . '">
						<td data-label="Thông tin HL về" class="row_psg_price" colspan="13">
							<div class="psg_price-wrap d-flex gap-2 align-items-center">
								<div class="col_psg_price flex-fill">
									<span class="text-label">Giá mua HL lượt về (VAT): </span>
									<input type="text" name="psg_luggage_purchase_inbound[]" id="psg_luggage_purchase_inbound' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										value="' . format_number($row['luggage_purchase_inbound']) . '"
										maxlength="25"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
										onpaste="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">NCC HL lượt về: </span>
									<select name="psg_luggage_supplier_inbound[]" class="psg_luggage_purchase_select" id="psg_luggage_supplier_inbound' . $i . '" >
										<option value=""></option>
										' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_inbound_id'], 'id', $sql_supplier) . '
									</select>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">Giá mua HL lượt về: </span>
									<input type="text" name="psg_detail_lug_pur_ib_no_vat[]" id="psg_detail_lug_pur_ib_no_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">VAT giá mua HL lượt về: </span>
									<input type="text" name="psg_detail_lug_pur_ib_vat[]" id="psg_detail_lug_pur_ib_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
							</div>
						</td>
					</tr>';

			$i++;
		}

		$html .= '<tr id="psg_last_row" class="footer-tr">
			<td colspan="13" class="text-start">
				<input type="button" class="btn btn-primary" id="btnPassengerAddRow" value="Thêm dòng" title="Thêm dòng" />
				Số dòng = <label id="lbl_psg_row_count">' . $row_count . '</label>
				<input type="hidden" name="psg_row_count" id="psg_row_count" value="' . $row_count . '" />
				<input type="hidden" id="booking_status" value="' . $this->bean->booking_status . '" >
			</td>
		</tr>';
		$html .= '</table>';
		$this->ss->assign('LINE_PASSENGERS', $html);
	}
}
