<?php
global $db, $current_user, $app_list_strings, $sugar_config;

if (isset($_POST['for']) && $_POST['for'] == 'getBookingStatus') {
	$user_list = get_user_array(true, 'Active', '', true);
	$tbl_detail = '<table id="status-detail" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff"><tbody>';

	$sql = 'SELECT created_by,
				after_value_string, 
				DATE_ADD(date_created, INTERVAL 7 HOUR) AS date_modified 
			FROM ec_flight_bookings_audit 
			WHERE parent_id = "' . $db->quote($_POST['booking_id']) . '" AND field_name = "booking_status" 
			ORDER BY date_created DESC';

	$res_ct = $db->query($sql);
	while ($row_ct = $db->fetchByAssoc($res_ct)) {
		switch ($row_ct['after_value_string']) {
			case 1:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Mới tạo</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 6:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Đã gọi</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 2:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Chờ thanh toán</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 3:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Xác nhận</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 7:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Xuất vé</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 8:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Hoàn tất</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
			case 4:
				$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime($row_ct['date_modified'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($row_ct['date_modified'])) . '</td><td width="10%">Huỷ</td><td>bởi ' . $user_list[$row_ct['created_by']] . '</td></tr>';
				break;
		}
	}

	$tbl_detail .= '<tr><td width="5%">' . date('H:i', strtotime('+7 hours', $_POST['date_entered'])) . '</td><td width="10%">' . date('d/m/Y', strtotime($_POST['date_entered'])) . '</td><td width="10%">Mới tạo</td><td>bởi ' . $user_list[$_POST['created_by']] . '</td></tbody></table>';
	echo $tbl_detail;
	exit;
}

if (isset($_POST['for']) && $_POST['for'] == 'editDescription') {
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['id']);
	$booking->description = $_POST['description'];
	$booking->save2();
}

// Edit iti line info
if (isset($_POST['for']) && $_POST['for'] == 'getItiLine') {
	$iti_arr = explode(',', $_POST['iti_id']);
	$sql = '
			SELECT *
				, GROUP_CONCAT(assigned_user_id) AS applied_pass
				, GROUP_CONCAT(name) AS name
				, GROUP_CONCAT(id) AS iti_id 
			FROM ec_booking_itineraries 
			WHERE id IN ("' . implode('","', $iti_arr) . '")
			GROUP BY booking_id
		';
	$res = $db->query($sql);
	$row = $db->fetchByAssoc($res);

	if (trim($row['airline_code']) == 'VNA' || trim($row['airline_code']) == 'VNP') {
		$selected1 = '';
		$selected2 = '';
		if (trim($row['airline_code']) == 'VNA')
			$selected1 = 'selected';
		else
			$selected2 = 'selected';
		$booking_airline = '
				<select name="bk_airline">
					<option value="VNA" ' . $selected1 . '>VNA</option>
					<option value="VNP" ' . $selected2 . '>VNP</option>
				</select>';
	} else
		$booking_airline = $row['airline_code'];

	$html = '<div class="d-flex flex-column gap-2 p-2 border border-radius">';
	$html .= '<h2 class="change-title">Thông tin ngày bay / hành trình cần sửa:</h2>';

	$html .= '<table class="table-config table-change-itineraries" cellpadding="0" cellspacing="0"><tbody>';
	$html .= '<tr><td colspan="4"><h4 class="sub-change-title">1. Thông tin hành trình: Hãng ' . $booking_airline . '</h4></td></tr>';
	$html .= '
			<tr>
				<td width="18%" class="label">Số hiệu:</td>
				<td width="32%"><input class="box-input" type="text" name="flight_number" value="' . $row['flight_number'] . '"></td>
				<td width="18%" class="label">Hạng vé:</td>
				<td width="32%"><input class="box-input" type="text" name="ticket_class" id="ticket_class0" value="' . $row['ticket_class'] . '"></td>
			</tr>
			<tr>
				<td width="18%" class="label">Nơi đi:</td>
				<td width="32%"><input class="box-input" type="text" name="departure" id="departure0" value="' . $row['departure'] . '"></td>
				<td width="18%" class="label">Nơi đến:</td>
				<td width="32%"><input class="box-input" type="text" name="arrival" id="arrival0" value="' . $row['arrival'] . '"></td>
			</tr>
			<tr>
				<td width="18%" class="label">Ngày giờ đi:</td>
				<td width="32%">
					<div class="d-flex gap-1 align-items-center">
						<input type="text" name="departure_date" value="' . date('d-m-Y', strtotime($row['departure_date'])) . '">
						<input type="text" name="departure_hour" class="input_hour" value="' . date('H', strtotime($row['departure_date'])) . '" maxlength="2">
						<input type="text" name="departure_minute" class="input_minute" value="' . date('i', strtotime($row['departure_date'])) . '" maxlength="2">
					</div>
				</td>
				<td width="18%" class="label">Ngày giờ đến:</td>
				<td width="32%">
					<div class="d-flex gap-1 align-items-center">
						<input type="text" name="arrival_date" value="' . date('d-m-Y', strtotime($row['arrival_date'])) . '">
						<input type="text" name="arrival_hour" class="input_hour" value="' . date('H', strtotime($row['arrival_date'])) . '" maxlength="2">
						<input type="text" name="arrival_minute" class="input_minute" value="' . date('i', strtotime($row['arrival_date'])) . '" maxlength="2">
					</div>
				</td>
				<input type="hidden" name="airline_code" value="' . $row['airline_code'] . '">
			</tr>';
	$html .= '</tbody></table>';

	$html .= '<input type="hidden" name="iti_id" value="' . $row['iti_id'] . '">';
	$html .= '<input type="hidden" name="applied_pass" value="' . $row['applied_pass'] . '">';
	$html .= '</div>';
	echo $html;
	exit;
}

// Dữ liệu cho dialog "Gửi Zalo" ở detail view (hành trình, hành khách/hành lý, lịch sử ZBS)
// - load lazy khi mở dialog thay vì tính sẵn trên mọi lượt xem trang.
if (isset($_POST['for']) && $_POST['for'] == 'getZaloDialogData') {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(getZaloDialogData($_POST['booking_id'] ?? ''), JSON_UNESCAPED_UNICODE);
	exit;
}

// Chi tiết doanh số booking 
if (isset($_POST['for']) && $_POST['for'] == 'getBookingProfitDetail') {
	header('Content-Type: application/json; charset=utf-8');

	if (!is_admin($current_user)) {
		echo json_encode(['success' => false, 'message' => 'Không có quyền']);
		exit;
	}

	$booking = new EC_Flight_Bookings();
	$booking->retrieve($_POST['booking_id'] ?? '');

	if (empty($booking->id)) {
		echo json_encode(['success' => false, 'message' => 'Không tìm thấy booking']);
		exit;
	}

	$bk_amt = calculateBKAmt($booking->id);
	$html = renderBookingProfitBreakdownHtml($bk_amt, $booking->name);

	echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
	exit;
}

// Insert passenger line info
if (isset($_POST['for']) && $_POST['for'] == 'getPassengerLine') {
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['booking']);

	// lấy thông tin hành trình cũ -> để lấy hạng vé
	if ($_POST['type'] == 'edit') {
		$sql_con_t = ' AND (add_type = 0 OR assigned_user_id = "' . $db->quote($_POST['pass_id']) . '")';
	} else {
		$sql_con_t = ' AND add_type = 0';
	}

	$sql_t = 'SELECT GROUP_CONCAT(iti_id_ob SEPARATOR "") AS iti_id_ob
			,GROUP_CONCAT(iti_ticket_class_ob SEPARATOR "") AS iti_ticket_class_ob
			,GROUP_CONCAT(iti_id_ib SEPARATOR "") AS iti_id_ib
			,GROUP_CONCAT(iti_ticket_class_ib SEPARATOR "") AS iti_ticket_class_ib
		FROM (
			SELECT IF(direction = 0, id, "") AS iti_id_ob 
				,IF(direction = 0, ticket_class, "") AS iti_ticket_class_ob
				,IF(direction = 1, id, "") AS iti_id_ib
				,IF(direction = 1, ticket_class, "") AS iti_ticket_class_ib
				,booking_id, MAX(sabre_logs)
			FROM ec_booking_itineraries 
			WHERE deleted = 0 AND booking_id = "' . $booking->id . '" 
				' . $sql_con_t . '
			GROUP BY direction
		) AS t
		GROUP BY booking_id';
	$res_t = $db->query($sql_t);
	$row_t = $db->fetchByAssoc($res_t);

	// Lấy hạng vé lượt đi
	if (!empty($_POST['ticket_class_ob'])) {
		$ticket_class_ob = $_POST['ticket_class_ob'];
	} else {
		$ticket_class_ob = $row_t['iti_ticket_class_ob'];
	}

	// Nếu 2 chiều, lấy thêm hạng vé lượt về
	if (empty($booking->flight_type)) {
		if (isset($_POST['ticket_class_ib']) && !empty($_POST['ticket_class_ib'])) {
			$ticket_class_ib = $_POST['ticket_class_ib'];
		} else {
			$ticket_class_ib = $row_t['iti_ticket_class_ib'];
		}
	}

	// Lấy thông tin nhà cung cấp hành lý
	$sql_supplier = "SELECT id, name FROM accounts 
		WHERE deleted = 0 AND account_type = 'Supplier' AND is_stop_tracking = 0 
		ORDER BY ticker_symbol";
	$res_supplier = $db->query($sql_supplier);
	$supplier = ['' => ''];
	while ($row_supplier = $db->fetchByAssoc($res_supplier)) {
		$supplier[$row_supplier['id']] = $row_supplier['name'];
	}

	if (empty($_POST['pass_id'])) {
		$sql_con = ' 
				AND booking_id = "' . $db->quote($_POST['booking']) . '" 
				AND id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers 
					WHERE deleted = 0 AND add_type = 2
					AND booking_id = "' . $db->quote($_POST['booking']) . '" 
				)';
	} else if ($_POST['type'] == 'edit') {
		$sql_con = ' AND id = "' . $db->quote($_POST['pass_id']) . '"';
	} else {
		$passenger_arr = explode(',', $_POST['pass_id']);
		$sql_con = ' AND id IN ("' . implode('","', $passenger_arr) . '")';
	}
	$sql = 'SELECT * FROM ec_booking_passengers WHERE deleted = 0' . $sql_con;
	$res = $db->query($sql);

	$html = "";
	if ($_POST['type'] != 'insert') {
		$html .= '<div class="line_pass d-flex flex-column gap-2 p-2 border border-radius mt-3">
					<h2 class="change-title">Thông tin hành khách đã chọn:</h2>
					<table id="passenger_tbl" class="table-change-passengers" cellpadding="0" cellspacing="0"><tbody>';
	}
	$i = 0;

	while ($row = $db->fetchByAssoc($res)) {
		// Giới tính, họ tên, ngày sinh
		$styleFirst = $i > 0 ? "border-top:8px solid var(--bgdecs-color)" : "";
		$html .= '<tr class="line_pass' . $row['id'] . '" style="' . $styleFirst . '">
			<td colspan="4" class="' . ($i > 0 ? "pt-2" : "") . '">
				<b class="pass_order" style="font-size:14px">Hành khách ' . ($i + 1) . ':</b> ' . $app_list_strings['passenger_type_list'][$row['type']] . '
				<div class="d-flex align-items-center gap-2 mt-1">
					<select class="box-select box-select2-non-search" name="pass_salutation[]">' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int) $row['salutation']) . '</select>
					<input class="box-input" type="text" value="' . $row['name'] . '" name="pass_name[]" />
					<input class="box-input pass_birthday" type="text" value="' . (!empty($row['birthday']) ? date('d-m-Y', strtotime($row['birthday'])) : '') . '" name="pass_birthday" style="width:15%" />
					<input type="hidden" name="pass_id[]" value="' . $row['id'] . '">
				</div>
			</td>
		</tr>';

		// Thông tin lượt về nếu có
		if (empty($booking->flight_type)) {
			// PNR lượt về
			$pnr_inbound = '
				<td width="20%" class="text-label text-nowrap">PNR lượt về:</td>
				<td><input class="box-input" type="text" value="' . $row['pnr_inbound'] . '" name="pass_pnr_inbound[]"></td>
			';

			// Số vé lượt về
			$eticket_inbound = '
				<td width="20%" class="text-label text-nowrap">Số vé lượt về:</td>
				<td><input class="box-input" type="text" value="' . $row['eticket_inbound'] . '" name="pass_eticket_inbound[]"></td>
			';

			// Số vé HL lượt về
			$eluggage_inbound = '
				<td width="20%" class="text-label text-nowrap">Số vé HL lượt về:</td>
				<td><input class="box-input" type="text" value="' . $row['eluggage_inbound'] . '" name="pass_eluggage_inbound[]"></td>
			';

			// Lựa chọn HL lượt về
			$options_inbound = $booking->generateBaggageOptions($booking->airline_inbound, $ticket_class_ib, $row['luggage_purchase_text_inbound'], $row['luggage_purchase_inbound']);
			// pr($options_inbound);

			$baggage_inbound = '
				<td width="20%" class="text-label text-nowrap">Thêm HL lượt về:</td>
				<td class="pass_luggage_ln pass_luggage_right">
					<select class="pass_luggage pass_luggage_ib box-select box-select2-non-search" name="pass_luggage_ib[]" ln="' . $i . '" style="width:100%">
						' . $options_inbound . '
					</select>
				</td>
			';

			// Giá bán HL lượt về
			$selling_price_inbound = '
				<td width="20%" class="text-label text-nowrap">Giá bán HL lượt về:</td>
				<td><input type="text" name="pass_luggage_price_inbound[]" value="' . $row['luggage_price_inbound'] . '" class="box-input allow-number-only"></td>
			';

			// Giá mua HL lượt về
			$purchase_price_inbound = '
				<td width="20%" class="text-label text-nowrap">Giá mua HL lượt về:</td>
				<td><input type="text" name="pass_luggage_purchase_inbound[]" value="' . $row['luggage_purchase_inbound'] . '" class="box-input allow-number-only"></td>
			';

			// Nhà cung cấp HL lượt đi
			$supplier_inbound = '
				<td width="20%"><label class="text-label text-nowrap">NCC HL lượt về:</label></td>
				<td class="supplier_line supplier_line_right">
					<select name="supplier_inbound[]" class="box-select box-select2">' . get_select_options_with_id($supplier, trim($row['supplier_inbound_id'])) . '</select>
					<input type="hidden" name="iti_ib" value="' . $row_t['iti_id_ib'] . '">
				</td>
			';
		} else {
			$eticket_inbound = '<td></td><td></td>';
			$eluggage_inbound = '<td></td><td></td>';
			$pnr_inbound = '<td></td><td></td>';
			$baggage_inbound = '<td></td><td></td>';
			$selling_price_inbound = '<td></td><td></td>';
			$purchase_price_inbound = '<td></td><td></td>';
			$supplier_inbound = '<td></td><td></td>';
		}

		// PNR lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
			<td class="text-label text-nowrap" width="20%">PNR lượt đi:</td>
			<td><input class="box-input" type="text" value="' . $row['pnr_outbound'] . '" name="pass_pnr_outbound[]"></td>
			' . $pnr_inbound . '
		</tr>';

		// Số vé lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
			<td class="text-label text-nowrap" width="20%">Số vé lượt đi:</td>
			<td><input class="box-input" type="text" value="' . $row['eticket_outbound'] . '" name="pass_eticket_outbound[]"></td>
			' . $eticket_inbound . '
		</tr>';

		// Số vé HL lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
			<td class="text-label text-nowrap" width="20%">Số vé HL lượt đi:</td>
			<td><input class="box-input" type="text" value="' . $row['eluggage_outbound'] . '" name="pass_eluggage_outbound[]"></td>
			' . $eluggage_inbound . '
		</tr>';

		// Lựa chọn HL lượt đi
		$options_ob = $booking->generateBaggageOptions($booking->airline, $ticket_class_ob, $row['luggage_purchase_text'], $row['luggage_purchase']);
		// pr($options_ob);

		$html .= '<tr class="line_pass' . $row['id'] . '">
					<td width="20%" class="text-label text-nowrap">Thêm HL lượt đi:</td>
					<td class="pass_luggage_ln pass_luggage_left">
						<select class="pass_luggage pass_luggage_ob box-select box-select2-non-search" name="pass_luggage_ob[]" ln="' . $i . '" style="width:100%">
							' . $options_ob . '
						</select>
					</td>
					' . $baggage_inbound . '
				</tr>';

		// Giá bán HL lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
			<td width="20%" class="text-label text-nowrap">Giá bán HL lượt đi:</td>
			<td><input type="text" name="pass_luggage_price[]" value="' . $row['luggage_price'] . '"  class="box-input allow-number-only"></td>
			' . $selling_price_inbound . '
		</tr>';

		// Giá mua HL lượt đi
		$html .= '
			<td width="20%" class="text-label text-nowrap">Giá mua HL lượt đi:</td>
			<td><input type="text" name="pass_luggage_purchase[]" value="' . $row['luggage_purchase'] . '" class="box-input allow-number-only"></td>
			' . $purchase_price_inbound . '
		';

		// Nhà cung cấp HL lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
			<td width="20%" class="text-label text-nowrap">NCC HL lượt đi:</td>
			<td class="supplier_line supplier_line_left">
				<select name="supplier_outbound[]" class="box-select box-select2">' . get_select_options_with_id($supplier, trim($row['supplier_id'])) . '</select>
				<input type="hidden" name="iti_ob" value="' . $row_t['iti_id_ob'] . '">
			</td>
			' . $supplier_inbound . '
		</tr>';

		$html .= "<input type='hidden' name='pass_type[]' value='" . $row['type'] . "' />";
		$html .= "<input type='hidden' name='pass_ticket_class_ob[]' value='" . $ticket_class_ob . "' />";
		$html .= "<input type='hidden' name='pass_ticket_class_ib[]' value='" . (isset($ticket_class_ib) ? $ticket_class_ib : '') . "' />";
		$i++;
	}
	if ($_POST['type'] != 'insert') {
		$html .= '</tbody></table></div>';
	}

	// thêm dòng id để biết mà edit
	if ($_POST['type'] == 'edit') {
		$html .= '<input type="hidden" name="edit_pass_id" />';
	}

	$html .= "<input type='hidden' name='pass_airline_ob' value='{$booking->airline}' />";
	$html .= "<input type='hidden' name='pass_airline_ib' value='{$booking->airline_inbound}' />";
	$html .= "<input type='hidden' name='bk_date_entered' value='{$booking->date_entered}' />";
	echo $html;
	exit;
}

/*
	Sửa trên chi tiết hành khách
		- 0: sửa chi tiết booking: mở ô số vé lượt đi - về
		- 1: thêm hành lý: mở ô thêm hành lý, lưu thành một dòng chi tiết mới
		- 2: đổi tên: mở ô tên hành khách, lưu thành một dòng chi tiết mới

	Sửa trên chi tiết hành trình
		- 3: đổi ngày bay đi: mở ô ngày h bay đi, lưu thành một dòng chi tiết mới
*/
if (isset($_POST['for']) && $_POST['for'] == 'populateBookingDetail') {
	$line_detail = populateLineDetails($_POST['id']);
	echo json_encode(array('line_html' => $line_detail));
	exit;
}

// thêm hành lý
if (isset($_POST['for']) && $_POST['for'] == 'addLuggage') {
	echo populateLinePassengers($_POST['id'], $_POST['flight_type'], '1', $_POST['airline_in'], $_POST['airline_out'], $_POST['ticket_class0'], $_POST['ticket_class1'], $_POST['contact_name'], $_POST['contact_phone']);
	exit;
}

// đổi ngày h bay dùng cho edit
if (isset($_POST['for']) && $_POST['for'] == 'changeFlightTime') {
	$iti_detail = populateLineItineraries($_POST['id']);
	echo $iti_detail;
	exit;
}

// lấy thông tin yêu cầu xuất hoá đơn
if (isset($_POST['for']) && $_POST['for'] == 'getInvoiceInfJson') {
	/** @var EC_Flight_Bookings **/
	$booking = new EC_Flight_Bookings();
	$booking->retrieve($_POST['booking_id']);

	$inv_inf = json_decode(str_replace("&quot;", "\"", $booking->shipping_address), 1) ?: [];

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'success' => true,
		'data' => [
			'iv_account_name' => $inv_inf['iv_account_name'] ?? '',
			'company_name' => $booking->company_name,
			'tax_code' => $booking->tax_code,
			'iv_email' => $inv_inf['iv_email'] ?? '',
			'iv_identity_number' => $inv_inf['iv_identity_number'] ?? '',
			'company_address' => $booking->company_address,
			'iv_payment_method' => $inv_inf['iv_payment_method'] ?? '',
			'iv_name_banks' => $inv_inf['iv_name_banks'] ?? '',
			'iv_bank_account' => $inv_inf['iv_bank_account'] ?? '',
		],
		'banks' => EC_Flight_Bookings::getInvoiceBankList(),
	], JSON_UNESCAPED_UNICODE);
	exit;
}

// lưu thông tin yêu cầu xuất hoá đơn (inline edit) - không chạy qua pipeline save() nặng của booking
if (isset($_POST['for']) && $_POST['for'] == 'saveInvoiceInfInline') {
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['booking_id']);

	if (empty($booking->id)) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['success' => false, 'message' => 'Không tìm thấy booking']);
		exit;
	}

	// company_name/tax_code/company_address là field thật của bean -> lưu qua save2() (bỏ qua các hook nặng: KPI, doanh thu, auto-assign)
	$booking->company_name = $_POST['company_name'] ?? '';
	$booking->tax_code = $_POST['tax_code'] ?? '';
	$booking->company_address = $_POST['company_address'] ?? '';
	$booking->save2();

	$existing = json_decode(str_replace("&quot;", "\"", $booking->shipping_address), 1) ?: [];
	$invoice_inf = [
		'iv_account_name' => $_POST['iv_account_name'] ?? '',
		'iv_email' => $_POST['iv_email'] ?? '',
		'iv_identity_number' => $_POST['iv_identity_number'] ?? '',
		'iv_payment_method' => $_POST['iv_payment_method'] ?? '',
		'iv_bank_account' => $_POST['iv_bank_account'] ?? '',
		'iv_name_banks' => $_POST['iv_name_banks'] ?? '',
	];
	$json = json_encode($invoice_inf, JSON_UNESCAPED_UNICODE);
	$booking->db->query("UPDATE ec_flight_bookings SET shipping_address = '" . $booking->db->quote($json) . "' WHERE id = '" . $booking->db->quote($booking->id) . "' AND deleted = 0");

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['success' => true]);
	exit;
}

// tạo mới / cập nhật trạng thái của 1 user 
if (isset($_POST['for']) && $_POST['for'] == 'updateUsrStt') {
	// chỉ cập nhật cho user là người dùng thông thường
	if (!is_admin($current_user)) {
		// kiếm tra đã có online hôm nay chưa

		$today_vn = (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
		$sql_exist = "SELECT id
			FROM ec_online_report
			WHERE assigned_user_id = '{$current_user->id}'
				AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = '$today_vn'
				AND deleted = 0";

		$res_exist = $db->query($sql_exist);
		$row_exist = $db->fetchByAssoc($res_exist);
		$online = new EC_Online_Report;

		if (!empty($row_exist['id'])) {
			$online->retrieve($row_exist['id']);
			$online->status = $_POST['stt'];
			$online->last_online = $GLOBALS['timedate']->nowDb();
			$online->save();

			if (strtotime($online->start_online) === false) {
				$db->query(
					"UPDATE ec_online_report
					SET start_online = NOW()
					WHERE id = '{$online->id}'"
				);
			}
		}
	} else {
		echo 'is_admin';
	}
}

// thay đổi vị trí trong bảng online
if (isset($_POST['for']) && $_POST['for'] == 'changeOnlinePosition') {
	$onl = new EC_Online_Report;
	// changeOnlinePosition đã tự đồng bộ users.agent_status + agent_change_status theo nút bấm
	$onl_res = $onl->changeOnlinePosition($_POST['onl'], $_POST['type']);

	echo $onl_res;
	exit;
}

// Cập nhật danh sách ec_online_report (thêm user còn thiếu trong ngày)
if (isset($_POST['for']) && $_POST['for'] == 'updateOnlineReport') {
	if (!is_admin($current_user)) {
		echo 0;
		exit;
	}

	$onl = new EC_Online_Report;
	$onl->populateOnlineReport();
	echo 1;
	exit;
}

// Lấy dữ liệu bảng online cho auto-refresh dashboard assignbk
if (isset($_GET['for']) && $_GET['for'] == 'getOnlineStatus') {
	require_once('modules/EC_Flight_Bookings/views/view.assignbk.php');
	$view = new Viewassignbk();
	$view->bean = BeanFactory::getBean('EC_Flight_Bookings');
	echo $view->getUserSttInf();
	exit;
}

// Nhắc nhở khách hàng lịch bay - button Remind
if (isset($_POST['for']) && $_POST['for'] == 'remindFlightSchedules') {
	$journey_id = (isset($_POST["journey_id"]) && !empty($_POST["journey_id"])) ? $_POST["journey_id"] : null;
	$booking_id = (isset($_POST["booking_id"]) && !empty($_POST["booking_id"])) ? $_POST["booking_id"] : null;

	if (is_null($journey_id) || is_null($booking_id)) {
		echo 0;
		exit();
	}

	if (!empty($journey_id) && !empty($booking_id)) {
		$update_remind = "UPDATE ec_booking_itineraries SET is_remind = 1 WHERE id = '" . trim($journey_id) . "'";
		$result = $db->query($update_remind);

		if ($result) {
			$sql_booking = "SELECT id, name, booking_status FROM ec_flight_bookings WHERE id = '" . trim($booking_id) . "' AND deleted = 0";
			$res = $db->query($sql_booking);
			$row = $db->fetchByAssoc($res);
			$booking_status = $row['booking_status'];
			$record_name = $row['name'];
			$id = $row['id'];

			$note = new Note();
			$note->id = '';
			$note->name = $record_name ?? '';
			$note->description = 'Đã nhắc lịch bay cho khách (remind)';
			$note->parent_type = 'EC_Flight_Bookings';
			$note->parent_id = $booking_id ?? $id;
			$note->booking_status = $booking_status ?? '';
			$note->save();
		}

		echo 1;
		exit();
	} else {
		echo 0;
		exit();
	}
}

// Nhắc nhở khách hàng lịch bay - button Remind
if (isset($_POST['for']) && $_POST['for'] == 'changeCheckinStatus') {
	$status     = isset($_POST['status']) ? (int)$_POST['status'] : 0;
	$journey_id = isset($_POST['journey_id']) ? trim($_POST['journey_id']) : '';
	$journey_name = isset($_POST['journey_name']) ? trim($_POST['journey_name']) : '';
	$booking_id = isset($_POST['booking_id']) ? trim($_POST['booking_id']) : '';
	$record_name = isset($_POST['record_name']) ? trim($_POST['record_name']) : '';
	$module_name = 'EC_Flight_Bookings';

	if ($journey_id === '' && $booking_id === '') {
		$GLOBALS['log']->fatal('changeCheckinStatus FAILED: missing journey_id or booking_id | POST=' . json_encode($_POST));

		echo 0;
		exit();
	}

	$journey_id = $db->quote($journey_id);

	// Lấy tình trạng checkin trước đó để chỉ tính KPI khi thực sự chuyển sang "đã checkin"
	// (tránh tạo KPI trùng nếu bấm checkin nhiều lần / request bị gửi lại cho cùng 1 journey)
	$prev_checkin_status = (int) $db->getOne("SELECT checkin_status FROM ec_booking_itineraries WHERE id = '{$journey_id}'");

	$sql = "
        UPDATE ec_booking_itineraries
        SET checkin_status = {$status}
        WHERE id = '{$journey_id}'
    ";
	$result = $db->query($sql);

	if ($result) {
		if ($status === 2 && $prev_checkin_status !== 2) {
			// Lưu KPI
			$description = 'Đã Check in hành trình ' . $journey_name . ' (Checkin)';
			myCreateWorkingProcess($module_name, $booking_id, $record_name, $description, $current_user->id, 'checkin_journey');

			$note = new Note();
			$note->id = '';
			$note->name = $record_name ?? '';
			$note->description = $description;
			$note->parent_type = $module_name;
			$note->parent_id = $booking_id;
			$note->save();
		}

		echo 1;
	} else {
		$GLOBALS['log']->fatal(
			'changeCheckinStatus FAILED | SQL=' . $sql
		);
		echo 0;
	}
	exit;
}

// Cập nhật doanh số booking
if (isset($_POST['for']) && $_POST['for'] == 'updateRevenueBooking') {
	$booking_id = (isset($_POST["booking_id"]) && !empty($_POST["booking_id"])) ? $_POST["booking_id"] : null;

	if (empty($booking_id) || is_null($booking_id)) {
		echo 0;
		exit();
	}

	saveRevenueBooking($booking_id);
	echo 1;
	exit();
}

// Save itinerary notes
if (isset($_POST['for']) && $_POST['for'] == 'saveItineraryNotes') {
	header('Content-Type: application/json');

	$itinerary_id = !empty($_POST['itinerary_id']) ? trim($_POST['itinerary_id']) : '';
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

	if (empty($itinerary_id)) {
		echo json_encode(['success' => false, 'message' => 'Invalid itinerary ID']);
		exit();
	}

	$notes_escaped = $db->quote($notes);
	$itinerary_id_escaped = $db->quote($itinerary_id);

	$sql = "UPDATE ec_booking_itineraries SET description = '{$notes_escaped}' WHERE id = '{$itinerary_id_escaped}' AND deleted = 0";
	$result = $db->query($sql);

	if ($result !== false) {
		if (!empty($notes)) {
			$sql_booking = "SELECT b.id AS booking_id, b.name AS booking_name
                FROM ec_booking_itineraries i
                INNER JOIN ec_flight_bookings b ON b.id = i.booking_id AND b.deleted = 0
                WHERE i.id = '{$itinerary_id_escaped}' AND i.deleted = 0";
			$res_booking = $db->query($sql_booking);
			$row_booking = $db->fetchByAssoc($res_booking);

			if (!empty($row_booking)) {
				$note = new Note();
				$note->id = '';
				$note->name = $row_booking['booking_name'] ?? '';
				$note->description = $notes;
				$note->parent_type = 'EC_Flight_Bookings';
				$note->parent_id = $row_booking['booking_id'];
				$note->save();
			}
		}

		echo json_encode(['success' => true]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to save notes']);
	}
	exit();
}


function populateLineDetails($booking_id)
{
	global $app_list_strings, $db;
	$supplier_cus_sql = " AND account_type='Supplier' AND is_stop_tracking=0 ";

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
					   ,date_entered
					   ,IF(supplier_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.deleted=0 AND a.id=supplier_id LIMIT 1), '') AS supplier
					   ,supplier_discount
				FROM ec_booking_details
				WHERE booking_id='" . $booking_id . "' 
				AND deleted=0
				ORDER BY direction, passenger_type, date_entered ";

	$res = $db->query($sql);
	$row_count = $db->countRows($res);

	$html = '';
	$html .= '<thead><tr id="bkd_first_row">
					<th style="width:8%;" class="text-center fw-semibold">Chiều</th>
					<th style="width:8%;" class="text-center fw-semibold">Loại HK</th>
					<th style="width:3%;" class="text-center fw-semibold">SL</th>
					<th style="width:8%;" class="text-center fw-semibold">Giá cơ bản</th>					
					<th style="width:8%;" class="text-center fw-semibold">Thuế VAT</th>
					<th style="width:8%;" class="text-center fw-semibold">Phí sân bay</th>
					<th style="width:8%;" class="text-center fw-semibold">Phí admin</th>
					<th style="width:8%;" class="text-center fw-semibold">Phí dịch vụ</th>
					<th style="width:8%;" class="text-center fw-semibold">Thành tiền</th>
					<th style="width:8%;" class="text-center fw-semibold">Giá mua</th>
					<th style="width:8%;" class="text-center fw-semibold">Chiết khấu</th>
					<th style="width:8%;" class="text-center fw-semibold">Phí xuất vé</th>
					<th style="width:10%;" class="text-center fw-semibold">NCC</th>
				  </tr></thead>';

	$i = 0;
	$total_qty_loop = 0;
	$total_price_loop = 0;
	$subtotal_amount_loop = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$detail_id = $row['detail_id'];
		$html .= '<tr id="bk_edit_line_' . $i . '" class="bkd_line fw-semibold">';

		$html .= '<td>
					<select name="bkd_direction[]" id="bk_edit_direction' . $i . '" >' . get_select_options_with_id($app_list_strings['bk_direction_list'], (int) $row['direction']) . '</select>
					<input type="hidden" value="0" name="bkd_deleted[]" id="bk_edit_deleted' . $i . '" />
					<input type="hidden" name="bkd_detail_id[]" id="bk_edit_detail_id' . $i . '" value="' . $detail_id . '" />
				</td>';
		$html .= '<td><select class="text-start" name="bkd_passenger_type[]" id="bk_edit_passenger_type' . $i . '">' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int) $row['passenger_type']) . '</select></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_quantity[]" id="bk_edit_quantity' . $i . '" value="' . format_number($row['quantity']) . '" maxlength="3" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" onkeyup="calculateLineEditDetails(' . $i . ', 0, 1)" type="text" name="bkd_unit_price[]" id="bk_edit_unit_price' . $i . '" value="' . format_number($row['unit_price']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_tax_and_fee[]" id="bk_edit_tax_and_fee' . $i . '" value="' . format_number($row['tax_and_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_airport_fee[]" id="bk_edit_airport_fee' . $i . '" value="' . format_number($row['airport_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ', 1)" type="text" name="bkd_admin_fee[]" id="bk_edit_admin_fee' . $i . '" value="' . format_number($row['admin_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_service_fee[]" id="bk_edit_service_fee' . $i . '" value="' . format_number($row['service_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_total_price[]" id="bk_edit_total_price' . $i . '" value="' . format_number($row['total_price']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" type="text" name="bkd_total_bought_price[]" id="bk_edit_total_bought_price' . $i . '" value="' . ((float) $row['total_bought_price'] ? format_number($row['total_bought_price']) : format_number($row['total_price'] - ($row['service_fee']) * $row['quantity'])) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" type="text" name="bkd_supplier_discount[]" id="bk_edit_supplier_discount' . $i . '" value="' . format_number($row['supplier_discount']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" id="bk_edit_supplier_ticketing_fee' . $i . '" type="text" name="bkd_supplier_ticketing_fee[]" value="' . (isset($row['fee_bought']) ? format_number($row['fee_bought']) : 0) . '" maxlength="25"></td>';
		$html .= '<td><select id="bk_edit_supplier_id' . $i . '" name="bkd_supplier_id[]"><option value=""></option>' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $supplier_cus_sql) . '</select></td>';

		// Thêm dòng phí admin chưa VAT
		$html .= '<tr id="bk_edit_admin_line_' . $i . '">';
		$html .= '<td colspan="13">
					<div class="addmin-fee-wrap d-flex gap-3 align-items-center">
						<div class="d-flex gap-1 align-items-center admin-fee-not-vat">
							<span class="text-label">Phí admin chưa VAT:</span>
							<input type="text" class="allow-number-only w-unset detail_ticket_input" name="bkd_admin_fee_no_vat[]" id="bk_edit_admin_fee_no_vat' . $i . '" onkeyup="calculateRelateAdminFee(' . $i . ');" onpaste="setTimeout(function(){calculateRelateAdminFee(' . $i . ');}, 10);" value="' . format_number($row['admin_fee_no_vat']) . '"/> 
						</div>
						<div class="d-flex gap-1 align-items-center admin-fee-vat">
							<span class="text-label">VAT admin: </span>
							<input type="text" class="allow-number-only w-unset detail_ticket_input" name="bkd_vat_admin[]" id="bk_edit_vat_admin' . $i . '" onkeyup="calculateRelateAdminFee(' . $i . ', 1);" onpaste="setTimeout(function(){calculateRelateAdminFee(' . $i . ', 1);}, 10);" value="' . format_number($row['vat_admin']) . '"/>
						</div>
					</div>
				</td>';

		$html .= '</tr>';

		$total_qty_loop += $row['quantity'];
		$total_price_loop += $row['total_price'];
		$subtotal_amount_loop += ((float) $row['total_bought_price'] ? $row['total_bought_price'] : ($row['total_price'] - ($row['service_fee']) * $row['quantity']));
		$i++;
	}

	$total_qty = isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : $total_qty_loop;
	$subtotal_amount = isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : $total_price_loop;
	$total_bought_amount = isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : $subtotal_amount_loop;
	// User permission
	$supplier_list = str_replace('"', "'", myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', '', 'id', $supplier_cus_sql));

	// Tổng
	$html .= '<tr id="bkd_last_row" class="footer-tr">
				<td colspan="2" style="margin-top: 3px;">
					<input type="hidden" name="bkd_row_count" id="bkd_row_count" value="' . $row_count . '" />
					<input type="hidden" name="supplier_list" id="supplier_list" value="' . $supplier_list . '" />
					<div class="d-flex align-items-center">
						<p>Số dòng = <span id="lbl_bkd_row_count">' . $row_count . '</span></p>
					</div>
				</td>
				<td><input type="text" readonly="readonly" name="total_qty" id="total_qty" value="' . format_number($total_qty) . '" /></td>
				<td colspan="5"></td>
				<td class="text-center">
					<input type="text" class="text-danger" readonly="readonly" name="subtotal_amount" id="subtotal_amount" value="' . format_number($subtotal_amount) . '" />
				</td>
				<td class="text-center">
					<input type="text" class="text-danger" readonly="readonly" name="total_bought_amount" id="total_bought_amount" value="' . format_number($total_bought_amount) . '" />
				</td>
				<td colspan="3">
					<input type="hidden" name="total_amount" id="bk_edit_total_amount" size="30" maxlength="26" title="" tabindex="0"  value="0" class="allow-number-only">
				</td>
			</tr>';

	$html1 = '<script>calculateTotal();</script>';

	return $html . $html1;
}

function populateLinePassengers($booking_id, $flight_type, $type, $airline_in = '', $airline_out = '', $ticket_class_out = '', $ticket_class_in = '', $contact_name = '', $contact_phone = '')
{
	global $app_list_strings, $timedate, $db;

	$sql = "SELECT p.id AS detail_id 
				,p.type
				,p.salutation
				,p.name
				,p.birthday
				,p.eticket_outbound
				,p.eticket_inbound
				,p.eluggage_outbound
				,p.eluggage_inbound
				,p.pnr_outbound
				,p.pnr_inbound
				,p.luggage_price
				,p.luggage_price_inbound
				,p.luggage_purchase
				,p.luggage_purchase_inbound
				,p.supplier_id
				,p.supplier_inbound_id
				,p.add_type
				,p.luggage_index_outbound
				,p.luggage_index_inbound
		FROM ec_booking_passengers p
		WHERE p.deleted=0 
		AND p.booking_id='" . $booking_id . "'
		AND p.add_type IS NULL
		ORDER BY p.type, p.date_entered ";
	$res = $db->query($sql);
	$html = '';
	$html .= '<table id="tbl_line_passengers" style="width:100%; line-height:25px" cellpadding="0" cellspacing="0" border="0">';

	$html .= '<thead>
		<tr id="psg_first_row">
			<th style="width:6%;">Loại HK</th>
			<th style="width:6%;">Danh xưng</th>
			<th style="width:20%;">Họ tên</th>
			<th style="width:8%;">Ngày sinh</th>
			<th style="width:12%;">Số vé lượt đi</th>
			<th style="width:12%;">Số vé lượt về</th>
			<th style="width:6%;">PNR lượt đi</th>
			<th style="width:6%;">PNR lượt về</th>
			<th style="width:12%;">Hành lý lượt đi</th>
			<th style="width:12%;">Hành lý lượt về</th>
			<th></th>
		</tr></thead>';

	$i = 0;

	while ($row = $db->fetchByAssoc($res)) {
		// kiểm tra có tên mới hay chưa
		$new_name = checkNewPassengerName($row['detail_id']);

		// line 1
		$html .= '<tr id="psg_line_' . $i . '">';

		$html .= '<td>' . $app_list_strings['passenger_type_list'][$row['type']] . '<input type="hidden" id="psg_traveller_type' . $i . '" name="psg_traveller_type[]" value="' . $row['type'] . '"></td>';

		$html .= '<td class="text-center">' . $app_list_strings['passenger_salutation_list'][$row['salutation']] . '<input type="hidden" name="psg_salutation[]" id="psg_salutation' . $i . '" value="' . $row['salutation'] . '"></td>';

		if ($type == '2') {
			$html .= '<td class="text-center"><input class="box-input" type="text" id="new_psg_full_name' . $i . '" name="psg_full_name[]" value="' . (!empty($new_name) ? $new_name : $row['name']) . '"><input type="hidden" id="psg_full_name' . $i . '" value="' . (!empty($new_name) ? $new_name : $row['name']) . '" old_name="' . $row['name'] . '"></td>';
		} else {
			$html .= '<td class="text-start" id="psg_new_full_name' . $i . '">' . (!empty($new_name) ? $new_name : $row['name']) . '<input type="hidden" name="psg_full_name[]" id="psg_full_name' . $i . '" value="' . $row['name'] . '"></td>';
		}

		$html .= '<td class="text-center">' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date('d-m-Y', strtotime($row['birthday'])) : '') . '<input type="hidden" name="psg_birthday[]" id="psg_birthday' . $i . '" value="' . $row['birthday'] . '"></td>';

		// Nếu code vé đã nhập thì không cho sửa trừ kế toán, admin
		if ((empty($row['eticket_outbound']) || ACLController::checkAccess("EC_Flight_Bookings", "edit", true)) && $type == '0') {
			$html .= '<td class="text-center"><input class="text-center box-input detail_ticket_input" type="text" maxlength="25" name="psg_eticket_outbound[]" id="psg_eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . $row['eticket_outbound'] . '<input type="hidden" id="psg_eticket_outbound' . $i . '" name="psg_eticket_outbound[]" value="' . strtoupper($row['eticket_outbound']) . '"></td>';
		}

		if ((empty($row['eticket_inbound']) || ACLController::checkAccess("EC_Flight_Bookings", "edit", true)) && $flight_type == '0' && $type == '0') {
			$html .= '<td><input class="box-input text-center" type="text" maxlength="25" name="psg_eticket_inbound[]" id="psg_eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . strtoupper($row['eticket_inbound']) . '<input type="hidden" id="psg_eticket_inbound' . $i . '" name="psg_eticket_inbound[]" value="' . strtoupper($row['eticket_inbound']) . '"></td>';
		}

		if ((empty($row['eluggage_outbound']) || ACLController::checkAccess("EC_Flight_Bookings", "edit", true)) && $type == '0') {
			$html .= '<td class="text-center"><input class="text-center box-input detail_ticket_input" type="text" maxlength="25" name="psg_eluggage_outbound[]" id="psg_eluggage_outbound' . $i . '" value="' . strtoupper($row['eluggage_outbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . $row['eluggage_outbound'] . '<input type="hidden" id="psg_eluggage_outbound' . $i . '" name="psg_eluggage_outbound[]" value="' . strtoupper($row['eluggage_outbound']) . '"></td>';
		}

		if ((empty($row['eluggage_inbound']) || ACLController::checkAccess("EC_Flight_Bookings", "edit", true)) && $flight_type == '0' && $type == '0') {
			$html .= '<td><input class="box-input text-center" type="text" maxlength="25" name="psg_eluggage_inbound[]" id="psg_eluggage_inbound' . $i . '" value="' . strtoupper($row['eluggage_inbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . strtoupper($row['eluggage_inbound']) . '<input type="hidden" id="psg_eluggage_inbound' . $i . '" name="psg_eluggage_inbound[]" value="' . strtoupper($row['eluggage_inbound']) . '"></td>';
		}

		$html .= '<td class="text-center">' . strtoupper($row['pnr_outbound']) . '<input type="hidden" name="psg_pnr_outbound[]" id="psg_pnr_outbound' . $i . '" value="' . strtoupper($row['pnr_outbound']) . '"></td>';

		$html .= '<td class="text-center">' . strtoupper($row['pnr_inbound']) . '<input type="hidden" name="psg_pnr_inbound[]" id="psg_pnr_inbound' . $i . '" value="' . strtoupper($row['pnr_inbound']) . '" /></td>';

		$html .= '<td></td><td></td>';

		$html .= '<td style="vertical-align:middle; text-align:center;">
					<input type="hidden" value="0" name="psg_deleted[]" id="psg_deleted' . $i . '" />';

		$html .= '<input type="hidden" name="bkd_supplier_discount[]" id="bkd_supplier_discount' . $i . '">
					<input type="hidden" name="psg_detail_id[]" id="psg_detail_id' . $i . '" value="' . $row['detail_id'] . '" />
					<input type="hidden" name="psg_add_type[]" id="psg_add_type' . $i . '" value="' . $row['add_type'] . '">';

		if ($type != 0) {
			$html .= '<input type="hidden" name="psg_parent_detail_id[]" id="psg_parent_detail_id' . $i . '" value="' . $row['detail_id'] . '">';
		}

		$html .= '</td>';
		$html .= '</tr>';

		$i++;
	} // while

	$html .= '</table>';
	return $html;
}

function checkNewPassengerName($parent_id)
{
	global $db;
	$sql = 'SELECT name FROM ec_booking_passengers WHERE add_type = 2 AND parent_detail_id = "' . $parent_id . '" ORDER BY date_entered DESC LIMIT 1';
	$res = $db->query($sql);
	$row = $db->fetchByAssoc($res);

	// check - haihugn 14/06/2023
	$row['name'] = (!isset($row['name']) || empty($row['name']) ? '' : $row['name']);

	return $row['name'];
}

// hiện thông tin ngày bay / hành trình để nhập, 
// hiện thêm thông tin hành khách
// vì có trường hợp khách đổi khách không,
// nếu chọn khách nào hiện thêm thông tin của khách đó
function populateLineItineraries($booking_id)
{
	global $db;
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($booking_id);

	if (trim($booking->airline) == 'VNA' || trim($booking->airline) == 'VNP') {
		if (trim($booking->airline) == 'VNA')
			$selected1 = 'selected';
		else
			$selected2 = 'selected';
		$booking_airline = '
					<select class="box-select" name="bk_airline0">
						<option value="VNA" ' . $selected1 . '>VNA</option>
						<option value="VNP" ' . $selected2 . '>VNP</option>
					</select>';
	} else
		$booking_airline = $booking->airline;

	if (trim($booking->airline_inbound) == 'VNA' || trim($booking->airline_inbound) == 'VNP') {
		if (trim($booking->airline_inbound) == 'VNA') {
			$selected1 = 'selected';
			$selected2 = '';
		} else
			$selected2 = 'selected';
		$booking_airline_inbound = '
					<select class="box-select" name="bk_airline1">
						<option value="VNA" ' . $selected1 . '>VNA</option>
						<option value="VNP" ' . $selected2 . '>VNP</option>
					</select>';
	} else
		$booking_airline_inbound = $booking->airline_inbound;

	$html = '<div class="d-flex flex-column gap-2 p-2 border border-radius">';
	$html .= '<h2 class="change-title">Thông tin ngày bay / hành trình mới:</h2>';

	$html .= '<table class="table-config table-change-itineraries" cellpadding="0" cellspacing="0"><tbody>';
	$html .= '<tr><td colspan="4"><h4 class="sub-change-title">1. Thông tin lượt đi: Hãng ' . $booking_airline . '</h4></td></tr>';

	$html .= '
			<tr>
				<td width="18%" class="label">Số hiệu:</td>
				<td width="32%"><input type="text" class="box-input" name="flight_number0"></td>
				<td width="18%" class="label">Hạng vé:</td>
				<td width="32%"><input type="text" class="box-input" name="ticket_class0" id="ticket_class0"></td>
			</tr>
			<tr>
				<td width="18%" class="label">Nơi đi:</td>
				<td width="32%"><input class="box-input" type="text" name="departure0" id="departure0"></td>
				<td width="18%" class="label">Nơi đến:</td>
				<td width="32%"><input class="box-input" type="text" name="arrival0" id="arrival0"></td>
			</tr>
			<tr>
				<td width="18%" class="label">Ngày giờ đi:</td>
				<td width="32%">
					<div class="d-flex gap-1 align-items-center">
						<input type="text" name="departure_date0" value="' . date('d-m-Y') . '">
						<input type="text" name="departure_hour0" class="input_hour" value="00" maxlength="2">
						<input type="text" name="departure_minute0" class="input_minute" value="00" maxlength="2">
					</div>
				</td>
				<td width="18%" class="label">Ngày giờ đến:</td>
				<td width="32%">
					<div class="d-flex gap-1 align-items-center">
						<input type="text" name="arrival_date0"  value="' . date('d-m-Y') . '">
						<input type="text" name="arrival_hour0" class="input_hour" value="00" maxlength="2">
						<input type="text" name="arrival_minute0" class="input_minute" value="00" maxlength="2">
					</div>
				</td>
				<input type="hidden" name="airline_code" value="' . $booking->airline . '">
			</tr>';

	// nếu là khứ hồi thì hiện thêm lượt về
	if (empty($booking->flight_type)) {
		$html .= '<tr><td colspan="4"><h4 class="sub-change-title">2. Thông tin lượt về: Hãng ' . $booking_airline_inbound . '</h4></td></tr>';
		$html .= '
				<tr>
					<td width="18%" class="label">Số hiệu:</td>
					<td width="32%"><input class="box-input" type="text" name="flight_number1"></td>
					<td width="18%" class="label">Hạng vé:</td>
					<td width="32%"><input class="box-input" type="text" name="ticket_class1" id="ticket_class1"></td>
				</tr>
				<tr>
					<td width="18%" class="label">Nơi đi:</td>
					<td width="32%"><input class="box-input" type="text" name="departure1" id="departure1"></td>
					<td width="18%" class="label">Nơi đến:</td>
					<td width="32%"><input class="box-input" type="text" name="arrival1" id="arrival1"></td>
				</tr>
				<tr>
					<td width="18%" class="label">Ngày giờ đi:</td>
					<td width="32%">
						<div class="d-flex gap-1 align-items-center">
							<input class="box-input" type="text" name="departure_date1" value="' . date('d-m-Y') . '">
							<input type="text" name="departure_hour1" class="input_hour" value="00" maxlength="2">
							<input type="text" name="departure_minute1" class="input_minute" value="00" maxlength="2">
						</div>
					</td>
					<td width="18%" class="label">Ngày giờ đến:</td>
					<td width="32%">
						<div class="d-flex gap-1 align-items-center">
							<input class="box-input" type="text" name="arrival_date1" value="' . date('d-m-Y') . '">
							<input type="text" name="arrival_hour1" class="input_hour" value="00" maxlength="2">
							<input type="text" name="arrival_minute1" class="input_minute" value="00" maxlength="2">
						</div>
					</td>
					<input type="hidden" name="airline_code_inbound" value="' . $booking->airline_inbound . '">
				</tr>';
	}

	$html .= '</tbody></table>';
	$html .= '</div>';

	// phần áp dụng cho khách
	$html .= '<div class="d-flex flex-column gap-2 p-3 border border-radius mt-3">';
	$html .= '<div class="d-flex flex-column gap-2">
					<h2 class="change-title">Áp dụng cho khách:</h2>
					<div class="change-notes">(Để đổi tên hành khách / thêm hành lý / thêm code vé số vé mới, vui lòng chọn mục 1 hoặc 2 trong phần này)</div>
				</div>';
	$html .= '<table class="table-config table-change-itineraries" cellpadding="0" cellspacing="0"><tbody>';
	// áp dụng cho tất cả
	$html .= '<tr>
					<td colspan="4">
						<div class="d-flex align-items-center gap-2">
							<span class="label w-50">1. Áp dụng cho tất cả:</span>
							<input type="checkbox" name="applied_all" id="applied_all">
						</div>
					</td>
				</tr>';
	// áp dụng cho một vài khách:
	$html .= '<tr>
					<td colspan="4">
						<div class="d-flex align-items-center gap-2">
							<span class="label w-50">2. Áp dụng cho một số khách:</span>
							<select class="box-select" id="applied_passenger" multiple data-placeholder="Chọn hành khách áp dụng" name="applied_passenger[]">' . getAllPassengers($booking_id) . '</select>
						</div>
					</td>
				</tr>';
	$html .= '</div>';

	return $html;
}

function checkNewLineItineraries($parent_id)
{
	global $db;
	$sql = 'SELECT departure_date, arrival_date 
			FROM ec_booking_itineraries 
			WHERE add_type = 3 AND parent_detail_id = "' . $parent_id . '" 
			ORDER BY date_entered DESC LIMIT 1';
	$res = $db->query($sql);
	$row = $db->fetchByAssoc($res);
	return $row;
}

function getAllPassengers($booking_id)
{
	global $db;
	$sql = 'SELECT id, name
			FROM ec_booking_passengers 
			WHERE deleted = 0 AND booking_id = "' . $booking_id . '"
				AND id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers 
					WHERE deleted = 0 AND booking_id = "' . $booking_id . '"
					AND add_type = 2
				)';
	$res = $db->query($sql);
	$html = '';
	while ($row = $db->fetchByAssoc($res)) {
		$html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
	}
	return $html;
}

function populatePassLuggage($airline, $ticket_class, $pass_type, $luggage_index = null)
{
	global $app_list_strings;
	$arr_replace = array('VNA' => 'vietnamair', 'JET' => 'jetstar', 'VJA' => 'vietjet', 'BBA' => 'bambooair', 'VJ' => 'vietjet', 'BL' => 'jetstar', 'JQ' => 'jetstar', '3K' => 'jetstar', 'VNP' => 'pacificair', 'VTA' => 'vietravelair');
	if ($airline != 'VJA' && $airline != 'VJ') {
		if ($airline == 'BBA' && !empty($ticket_class) && $pass_type != '2') {
			$pass_ticket_class = '_' . strtolower(str_replace(' ', '', $ticket_class));
			$luggage_list = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
			if (empty($luggage_list))
				$luggage_list = array();
			$luggage_arr = $luggage_list + $app_list_strings['bambooair_advanced_luggage_price_list'];
		} else if (($airline == 'VNA' || $airline == 'VNP' || $airline == 'BBA') && $pass_type == '2') {
			$pass_ticket_class = '_infant';
			$luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
		} else if ($airline == 'VNA' && strpos($ticket_class, 'Business') !== false && $pass_type != '2') {
			$ticket_class_arr = explode(" ", $ticket_class);
			$pass_ticket_class = '_' . strtolower($ticket_class_arr[0]);
			$luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
		} else {
			$pass_ticket_class = '';
			$luggage_arr = $app_list_strings[$arr_replace[$airline] . $pass_ticket_class . '_luggage_price_list'];
		}
	} else {
		$luggage_arr = $app_list_strings['new_' . $arr_replace[$airline] . '_luggage_price_list'];
	}
	return $luggage_arr;
}

// BLOCK - UNBLOCK - WHITELIST IP ON WEBSITE
if (isset($_POST['for']) && $_POST['for'] == 'block_ip') {
	$ip = isset($_POST['ip']) ? $_POST['ip'] : '';
	$duration = isset($_POST['duration']) ? $_POST['duration'] : 0;
	$domain = isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->blockIP($domain, $ip, $duration);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'unblock_ip') {
	$ip = isset($_POST['ip']) ? $_POST['ip'] : '';
	$domain = isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->unblockIP($domain, $ip);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'whitelist_ip') {
	$ip = isset($_POST['ip']) ? $_POST['ip'] : '';
	$duration = isset($_POST['duration']) ? $_POST['duration'] : 0;
	$domain = isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->whitelistIP($domain, $ip, $duration);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'get_blocking_history') {
	$ip = isset($_POST['ip']) ? $_POST['ip'] : '';
	$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
	$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
	$domain = isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->getInfoLog($domain, $from_date, $to_date, $ip);
	exit();
}
// END BLOCK - UNBLOCK - WHITELIST IP ON WEBSITE

// lấy ds vé cận
if (isset($_POST['for']) && $_POST['for'] == 'getPriorBooking') {
	$html2 = '
			<table id="prior_bk_tbk" class="detail_bk_tbl table-details__booking table-getPriorBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="5%">Vé</th>
					<th width="15%">Doanh số</th>
					<th width="15%" class="hide-mobile">Ngày bay đi</th>
					<th width="15%" class="hide-mobile">Ngày bay về</th>
					<th class="hide-mobile">Ngày đặt</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.arrival_date IS NULL, NULL, DATE_FORMAT(i.arrival_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, rv.total_profit AS bk_sales 
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			INNER JOIN ec_booking_itineraries i ON i.deleted = 0 AND i.booking_id = bk.id
			WHERE bk.deleted = 0
			AND bk.is_prior = 1
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			AND bk.created_by = "' . $db->quote($_POST['user']) . '"
			GROUP BY bk.id
			ORDER BY FIELD(bk.booking_status, 8, 7, 3, 2, 6, 1, 4), bk.date_entered DESC
		';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));
		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</font>
					</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center hide-mobile">' . $departure_date . '</td>
					<td class="text-center hide-mobile">' . $arrival_date . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		$total_sale += $row['bk_sales'];
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 3:
				$confirmed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total, 1);
	$completed_txt = getComparePercentTxt($completed, $total, 1);
	$exported_txt = getComparePercentTxt($exported, $total, 1);
	$confirmed_txt = getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt = getComparePercentTxt($paidwait, $total, 1);
	$called_txt = getComparePercentTxt($called, $total, 1);
	$created_txt = getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-getPriorBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Xác nhận</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Booking huỷ</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</span>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center"><span class="text-success fw-semibold">' . format_number($confirmed) . $confirmed_txt . '</span></td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// lấy ds bk 3 vé trở xuống
if (isset($_POST['for']) && $_POST['for'] == 'get3TicketBooking') {
	$html2 = '
			<table id="threeticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get3TicketBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="10%" class="hide-mobile">Phí DV</th>
					<th width="5%">Vé</th>
					<th width="12%">Doanh số</th>
					<th width="12%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Giao cho</th>
					<th width="15%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Ngày xuất vé</th>
				</thead>
				<tbody>';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
				, IFNULL(SUM(d.quantity), 0) AS total_ticket
				, rv.total_profit AS bk_sales
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			LEFT JOIN ec_booking_details d ON d.booking_id = bk.id AND d.deleted = 0
			LEFT JOIN users u ON u.id = bk.assigned_user_id AND u.title = "Bot" AND u.deleted = 0
			WHERE bk.created_by = "' . $db->quote($_POST['user']) . '"
			AND bk.deleted = 0
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			AND bk.is_prior = 0
			GROUP BY bk.id
			HAVING total_ticket <= 3
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4)
		';

	// pr($sql);

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$service_fee = implode("&nbsp;/&nbsp;", array_map(function ($val) {
			return format_number($val);
		}, explode(',', $row['service_fee'])));

		// Ngày xuất vé
		$date_ticket_issue = empty($row['bk_date_ticket_issue']) ? '' : date('d-m-Y', strtotime($row['bk_date_ticket_issue']));

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</font>
					</td>
					<td class="text-center service_fee hide-mobile">' . $service_fee . '</td>
					<td class="text-center total_ticket">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center bk_sales">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center average_fee">' . format_number($row['bk_sales'] / $row['total_ticket']) . '</td>
					<td class="text-center hide-mobile">' . $row['user_name'] . '</td>
					<td class="text-center bk_date_entered hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center date_ticket_issue hide-mobile">
						' . $date_ticket_issue . '
					</td>
				</tr>
			';

		$i++;
		$total++;
		$total_sale += $row['bk_sales'];
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 3:
				$confirmed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total, 1);
	$confirmed_txt = getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt = getComparePercentTxt($paidwait, $total, 1);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-get3TicketBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Booking huỷ</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center">' . format_number($total_sale / $ticket_completed) . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// lấy ds bk 4-8 vé
if (isset($_POST['for']) && $_POST['for'] == 'get4To8TicketBooking') {
	$html2 = '
			<table id="feticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get4To8TicketBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="10%" class="hide-mobile">Phí DV</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Giao cho</th>
					<th class="hide-mobile">Ngày đặt</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, IFNULL(SUM(d.quantity), 0) AS total_ticket
				, rv.total_profit AS bk_sales
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			LEFT JOIN ec_booking_details d ON d.booking_id = bk.id AND d.deleted = 0
			LEFT JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			AND bk.created_by = "' . $db->quote($_POST['user']) . '"
			AND bk.is_prior = 0
			GROUP BY bk.id
			HAVING total_ticket >= 4 AND total_ticket <= 8
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4)
		';
	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$service_fee = implode("&nbsp;/&nbsp;", array_map(function ($val) {
			return format_number($val);
		}, explode(',', $row['service_fee'])));

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</font>
					</td>
					<td class="text-center hide-mobile">' . $service_fee . '</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales'] / $row['total_ticket']) . '</td>
					<td class="text-center hide-mobile">' . $row['user_name'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		$total_sale += $row['bk_sales'];
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 3:
				$confirmed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total, 1);
	$confirmed_txt = getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt = getComparePercentTxt($paidwait, $total, 1);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-get4To8TicketBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%">Xác nhận</th>
					<th width="10%" class="hide-mobile">Booking huỷ</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center">' . format_number($total_sale / $ticket_completed) . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center">' . format_number($confirmed) . $confirmed_txt . '</td>
					<td class="text-center hide-mobile"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// Lấy ds bk vé quốc tế
if (isset($_POST['for']) && $_POST['for'] == 'getInterBooking') {
	$html2 = '
			<table id="inter_bk_tbl" class="detail_bk_tbl table-details__booking table-getinter_booking" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="9%">Trạng thái</th>
					<th width="14%">Hành trình</th>
					<th width="9%" class="hide-mobile">Phí DV</th>
					<th width="5%">Vé</th>
					<th width="9%">Doanh số</th>
					<th width="9%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Giao cho</th>
					<th width="12%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Ngày xuất vé</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
				, IFNULL(SUM(d.quantity), 0) AS total_ticket
				, rv.total_profit AS bk_sales
				, bk.id as booking_id
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
				, rt.departure, rt.arrival
				, ap_dep.city_name AS dep_city
				, ap_dep.country AS dep_country
				, ap_arr.city_name AS arr_city
				, ap_arr.country AS arr_country
				, IFNULL(rtn.cnt, 0) AS return_leg_count
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			LEFT JOIN ec_booking_details d ON d.booking_id = bk.id AND d.deleted = 0
			INNER JOIN users u ON u.id = bk.assigned_user_id
			LEFT JOIN (
				SELECT
					i.booking_id
					, SUBSTRING_INDEX(GROUP_CONCAT(i.departure ORDER BY i.departure_date ASC), ",", 1) AS departure
					, SUBSTRING_INDEX(GROUP_CONCAT(i.arrival ORDER BY i.departure_date DESC), ",", 1) AS arrival
				FROM ec_booking_itineraries i
				WHERE i.deleted = 0 AND i.direction = 0
				GROUP BY i.booking_id
			) rt ON rt.booking_id = bk.id
			LEFT JOIN ec_airports ap_dep ON ap_dep.iata_code = rt.departure AND ap_dep.deleted = 0
			LEFT JOIN ec_airports ap_arr ON ap_arr.iata_code = rt.arrival AND ap_arr.deleted = 0
			LEFT JOIN (
				SELECT booking_id, COUNT(*) AS cnt
				FROM ec_booking_itineraries
				WHERE deleted = 0 AND direction = 1
				GROUP BY booking_id
			) rtn ON rtn.booking_id = bk.id
			WHERE bk.deleted = 0
			AND bk.ticket_type = 2
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			AND bk.created_by = "' . $db->quote($_POST['user']) . '"
			GROUP BY bk_id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4)
		';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$service_fee = implode("&nbsp;/&nbsp;", array_map(function ($val) {
			return format_number($val);
		}, explode(',', $row['service_fee'])));

		// Ngày xuất vé
		$date_ticket_issue = ($row['bk_date_ticket_issue'] == '') ? '' : date('d-m-Y', strtotime($row['bk_date_ticket_issue']));

		// Hành trình: mã sân bay đi/đến (ec_booking_itineraries) + thành phố/quốc gia điểm đến (ec_airports)
		$itinerary_html = '';
		if (!empty($row['departure']) && !empty($row['arrival'])) {
			$arr_country_name = !empty($row['arr_country']) && isset($app_list_strings['region_dom'][$row['arr_country']])
				? $app_list_strings['region_dom'][$row['arr_country']]
				: $row['arr_country'];
			$dep_country_name = !empty($row['dep_country']) && isset($app_list_strings['region_dom'][$row['dep_country']])
				? $app_list_strings['region_dom'][$row['dep_country']]
				: $row['dep_country'];

			$itinerary_html = '<div class="fw-semibold">' . $row['departure'] . ' → ' . $row['arrival'] . '</div>';
			$itinerary_html .= '<div>' . $row['dep_city'] . ($dep_country_name ? ' (' . $dep_country_name . ')' : '') . ' → ' . $row['arr_city'] . ($arr_country_name ? ' (' . $arr_country_name . ')' : '') . '</div>';
			if ($row['return_leg_count'] > 0) {
				$itinerary_html .= '<div class="fw-semibold text-primary">Khứ hồi</div>';
			}
		}

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</font>
					</td>
					<td class="text-center" data-field="itinerary">' . $itinerary_html . '</td>
					<td class="text-center hide-mobile" data-field="service_fee">' . $service_fee . '</td>
					<td class="text-center" data-field="total_ticket">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center" data-field="bk_sales">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center" data-field="average_fee">' . format_number($row['bk_sales'] / $row['total_ticket']) . '</td>
					<td class="text-center hide-mobile">' . $row['user_name'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center hide-mobile">
						' . $date_ticket_issue . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		$total_sale += $row['bk_sales'];
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 3:
				$confirmed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total, 1);
	$confirmed_txt = getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt = getComparePercentTxt($paidwait, $total, 1);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	if ($ticket_completed == '') {
		$fee_average = 0;
	} else {
		$fee_average = format_number($total_sale / $ticket_completed);
	}
	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-get_inter_booking mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Booking huỷ</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center fee-average">' . $fee_average . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit;
}

// Xem chi tiết cuộc gọi đến - nhỡ theo site
if (isset($_POST['for']) && $_POST['for'] == 'getDetailCallBookingQtyReport') {
	$html = '
			<table class="detail_bk_tbl table-details__booking table-get_detail_call mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="12%">Mã cuộc gọi</th>
					<th width="10%">Gọi từ</th>
					<th width="10%">Gọi đến</th>
					<th width="10%" class="hide-mobile">Thời gian</th>
					<th width="10%" class="hide-mobile">Thời lượng</th>
					<th width="10%" class="hide-mobile">Nguồn</th>
					<th width="10%" class="hide-mobile">Booking</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
		';

	// Màu trạng thái cuộc gọi (call_status_dom không có bảng màu riêng)
	$call_status_colors = ['new' => '#6c757d', 'processing' => '#CF822E', 'done' => '#26A86A'];

	$from_date = $_POST['fdate'];
	$to_date = $_POST['tdate'];
	$direction = $_POST['direction'];

	$where_user = '';
	if (isset($_POST['user'])) {
		$where_user = 'AND u.id = "' . $db->quote($_POST['user']) . '"';
	}

	$where_call_bk = '';
	if (isset($_POST['is_booking']) && $_POST['is_booking']) {
		$where_call_bk = 'AND c.booking_id IS NOT NULL AND c.booking_id <> ""';
	}

	$sql = 'SELECT
				c.id,
				c.name,
				c.status,
				c.call_from,
				c.call_to,
				c.direction,
				c.date_start,
				c.call_duration,
				c.call_sources,
				c.booking_id,
				c.description
			FROM calls c
			LEFT JOIN users u ON c.call_sources = u.last_name
			WHERE
			c.deleted = 0
			AND DATE_ADD(c.date_entered, INTERVAL 7 HOUR) BETWEEN "' . date('Y-m-d', strtotime($from_date)) . '" AND "' . date('Y-m-d', strtotime($to_date)) . ' 23:59:59"
			AND c.direction = "' . $direction . '"
			' . $where_user . '
			' . $where_call_bk . '
			';

	$res = $db->query($sql);

	$i = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$booking = new EC_Flight_Bookings();
		$booking->retrieve($row['booking_id']);
		$booking_name = $booking->name;

		// Trạng thái cuộc gọi + trạng thái booking (kèm màu) để hiển thị dưới mã tương ứng
		$call_stt_txt = $app_list_strings['call_status_dom'][$row['status']] ?? $row['status'];
		$call_stt_color = $call_status_colors[$row['status']] ?? '#6c757d';
		$call_stt_html = $call_stt_txt !== '' ? '<div class="fw-semibold" style="color:' . $call_stt_color . ';font-size:12px;">' . $call_stt_txt . '</div>' : '';

		$bk_stt = $booking->booking_status;
		$bk_stt_txt = $bk_stt !== '' ? ($app_list_strings['booking_status_list'][$bk_stt] ?? '') : '';
		$bk_stt_color = $app_list_strings['booking_status_color_list'][$bk_stt] ?? '#000000';
		$bk_stt_html = ($row['booking_id'] && $bk_stt_txt !== '') ? '<div class="fw-semibold" style="color:' . $bk_stt_color . ';font-size:12px;">' . $bk_stt_txt . '</div>' : '';

		$html .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=Calls&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a>' . $call_stt_html . '
					</td>
					<td class="text-center">' . $row['call_from'] . '</td>
					<td class="text-center">' . $row['call_to'] . '</td>
					<td class="text-center">' . $row['date_start'] . '</td>
					<td class="text-center">' . global_secondsToTimeFormat($row['call_duration']) . '</td>
					<td class="text-center hide-mobile">
						' . $row['call_sources'] . '
					</td>
					<td class="text-center hide-mobile">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '" target="_blank">' . $booking_name . '</a>' . $bk_stt_html . '
					</td>
					<td class="text-start text-warp hide-mobile">
						' . $row['description'] . '
					</td>
				</tr>
			';
		$i++;
	}

	$html .= '</table>';

	echo '<div class="box-section detail_bk--wrap">' . $html . '</div>';
	exit;
}

function genNoteBKStt($num, $num_per, $stt_name, $init_note = '')
{
	$note = '';
	if ($num > 0) {
		if (!empty($init_note))
			$note .= '<br>';
		$note .= '-&nbsp;&nbsp;' . $stt_name . ': ' . format_number($num);
		$note .= $num_per;
	}

	return $note;
}

function getComparePercentTxt($ts, $ms, $return_type = 0, $params = array())
{
	$percent = ($ms == 0) ? 0 : $ts / $ms * 100;

	if ($percent > 0) {
		if ($return_type == 1) {
			$percent_txt = '&nbsp;(' . format_number($percent) . '%)';
		} else if ($return_type == 2) {
			$percent_txt = '&nbsp;(' . format_number($percent) . '% booking)<br>' . $params['total_com'] . '/' . $ts . ' hoàn tất<br>DS: ' . format_number($params['total_sale']);
		} else {
			$percent_txt = '<br>(' . format_number($percent) . '%)';
		}
	} else
		$percent_txt = '';
	return $percent_txt;
}

// lấy ds booking do booker đặt
if (isset($_POST['for']) && $_POST['for'] == 'getBookerBooking') {
	$html2 = '
			<table id="booker_bk_tbl" class="detail_bk_tbl table-details__booking table-getBookerBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="15%" class="hide-mobile">Người liên hệ</th>
					<th width="15%" class="hide-mobile">SĐT</th>
					<th width="15%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Xuất vé</th>
				</thead>
				<tbody>
		';

	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, rv.total_profit AS bk_sales
				, bk.contact_name
				, bk.phone
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			LEFT JOIN ec_flight_bookings_audit a ON bk.id = a.parent_id
			WHERE bk.deleted = 0
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			AND bk.created_by = "' . $db->quote($_POST['user']) . '"
			AND (
				(a.field_name = "contact_name" AND a.before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi"))
				OR bk.contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi")
			)
			GROUP BY bk.id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), bk.date_entered DESC
		';

	$res = $db->query($sql);
	$i = $total = $exported = $canceled = $completed = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$date_ticket_issue = (empty($row['bk_date_ticket_issue'])) ? '' : date('d-m-Y', strtotime($row['bk_date_ticket_issue']));

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][(int) $row['booking_status']] . '">' . $app_list_strings['booking_status_list'][(int) $row['booking_status']] . '</font>
					</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-start hide-mobile">' . $row['contact_name'] . '</td>
					<td class="text-center hide-mobile">' . $row['phone'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center hide-mobile">
						' . $date_ticket_issue . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				break;
			case 3:
				$confirmed++;
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total);
	$confirmed_txt = getComparePercentTxt($confirmed, $total);
	$paidwait_txt = getComparePercentTxt($paidwait, $total);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total);

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-getBookerBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng</th>
					<th width="10%">Huỷ</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé</th>
					<th width="10%">Xác nhận</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Chờ TT</th>
					<th width="10%" class="hide-mobile">Mới tạo</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center">' . format_number($total) . '</td>
					<td class="text-center"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($completed) . $completed_txt . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($confirmed) . $confirmed_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($paidwait) . $paidwait_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($created) . $created_txt . '</td>
					<td class="text-center hide-mobile"></td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// list KhachHang booking
if (isset($_POST['for']) && $_POST['for'] == 'getKhachHangBooking') {
	$extracted_shortcode = $sugar_config['short_domain_code'] ?? [];
	$domain_code_conditions = array_map(function ($code) use ($db) {
		return 'UPPER(before_value_string) LIKE "' . $db->quote(strtoupper(trim($code))) . '\_%"';
	}, $extracted_shortcode);
	$domain_code_sql = !empty($domain_code_conditions) ? ' OR ' . implode(' OR ', $domain_code_conditions) : '';

	// Kiểm tra trực tiếp trên contact_name hiện tại — không chỉ dựa vào is_reference,
	// vì is_reference chỉ được set khi booking tạo qua CustomController::save_booking.
	$domain_code_conditions_current = array_map(function ($code) use ($db) {
		return 'UPPER(bk.contact_name) LIKE "' . $db->quote(strtoupper(trim($code))) . '\_%"';
	}, $extracted_shortcode);
	$domain_code_sql_current = !empty($domain_code_conditions_current) ? ' OR ' . implode(' OR ', $domain_code_conditions_current) : '';

	$html2 = '
			<table id="khachhang_bk_tbl" class="detail_bk_tbl table-details__booking table-getKhachHangBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="15%" class="hide-mobile">Người liên hệ</th>
					<th width="15%" class="hide-mobile">SĐT</th>
					<th width="15%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Xuất vé</th>
				</thead>
				<tbody>
		';

	$sql = '
		SELECT 
			bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
			, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id), 0) AS total_ticket
			, rv.total_profit AS bk_sales
			, bk.contact_name
			, bk.phone
			, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			, bk.date_ticket_issue AS bk_date_ticket_issue
		FROM ec_flight_bookings bk
		LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
		WHERE bk.deleted = 0
		AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
		AND bk.created_by = "' . $db->quote($_POST['user']) . '"
		AND NOT (IFNULL(bk.is_reference, 0) = 1 OR bk.contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")' . $domain_code_sql_current . ')
		AND bk.contact_name IS NOT NULL 
		AND bk.contact_name != ""
		AND NOT EXISTS (
			SELECT 1 FROM ec_flight_bookings_audit 
			WHERE parent_id = bk.id 
			AND field_name = "contact_name"
			AND (before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi", "Tham Khao")' . $domain_code_sql . ')
		)
		ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), bk.date_entered DESC
	';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $exported = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$date_ticket_issue = (empty($row['bk_date_ticket_issue'])) ? '' : date('d-m-Y', strtotime($row['bk_date_ticket_issue']));

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][(int) $row['booking_status']] . '">' . $app_list_strings['booking_status_list'][(int) $row['booking_status']] . '</font>
					</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-start hide-mobile">' . $row['contact_name'] . '</td>
					<td class="text-center hide-mobile">' . $row['phone'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center hide-mobile">
						' . $date_ticket_issue . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				break;
			case 3:
				$confirmed++;
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total);
	$confirmed_txt = getComparePercentTxt($confirmed, $total);
	$paidwait_txt = getComparePercentTxt($paidwait, $total);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total);

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-getKhachHangBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng</th>
					<th width="10%">Huỷ</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé</th>
					<th width="10%">Xác nhận</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Chờ TT</th>
					<th width="10%" class="hide-mobile">Mới tạo</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center">' . format_number($total) . '</td>
					<td class="text-center"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($completed) . $completed_txt . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($confirmed) . $confirmed_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($paidwait) . $paidwait_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($created) . $created_txt . '</td>
					<td class="text-center hide-mobile"></td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// list ThamKhao booking
if (isset($_POST['for']) && $_POST['for'] == 'getThamKhaoBooking') {
	$html2 = '
			<table id="thamkhao_bk_tbl" class="detail_bk_tbl table-details__booking table-getThamKhaoBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="15%" class="hide-mobile">Người liên hệ</th>
					<th width="15%" class="hide-mobile">SĐT</th>
					<th width="15%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Xuất vé</th>
				</thead>
				<tbody>
		';

	$sql = '
		SELECT 
			bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
			, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id), 0) AS total_ticket
			, rv.total_profit AS bk_sales
			, bk.contact_name
			, bk.phone
			, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
			, bk.date_ticket_issue AS bk_date_ticket_issue
		FROM ec_flight_bookings bk
		LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
		WHERE bk.deleted = 0
		AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
		AND bk.created_by = "' . $db->quote($_POST['user']) . '"
		AND IFNULL(bk.is_reference, 0) = 1
		ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), bk.date_entered DESC
	';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $exported = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$date_ticket_issue = (empty($row['bk_date_ticket_issue'])) ? '' : date('d-m-Y', strtotime($row['bk_date_ticket_issue']));

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][(int) $row['booking_status']] . '">' . $app_list_strings['booking_status_list'][(int) $row['booking_status']] . '</font>
					</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-start hide-mobile">' . $row['contact_name'] . '</td>
					<td class="text-center hide-mobile">' . $row['phone'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center hide-mobile">
						' . $date_ticket_issue . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				break;
			case 3:
				$confirmed++;
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total);
	$completed_txt = getComparePercentTxt($completed, $total);
	$exported_txt = getComparePercentTxt($exported, $total);
	$confirmed_txt = getComparePercentTxt($confirmed, $total);
	$paidwait_txt = getComparePercentTxt($paidwait, $total);
	$called_txt = getComparePercentTxt($called, $total);
	$created_txt = getComparePercentTxt($created, $total);

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-getThamKhaoBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng</th>
					<th width="10%">Huỷ</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé</th>
					<th width="10%">Xác nhận</th>
					<th width="10%" class="hide-mobile">Đã gọi</th>
					<th width="10%" class="hide-mobile">Chờ TT</th>
					<th width="10%" class="hide-mobile">Mới tạo</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center">' . format_number($total) . '</td>
					<td class="text-center"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($completed) . $completed_txt . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</td>
					<td class="text-center text-conpleted-status fw-semibold">' . format_number($confirmed) . $confirmed_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($paidwait) . $paidwait_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($created) . $created_txt . '</td>
					<td class="text-center hide-mobile"></td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// thống kê chi tiết booking trong ngày
if (isset($_POST['for']) && $_POST['for'] == 'getToTalBKInOneDay') {
	$html2 = '
			<table id="getToTalBKInOneDay_lbl" class="detail_bk_tbl table-details__booking table-getToTalBKInOneDay2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="3%">#</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="10%" class="hide-mobile">Phí DV</th>
					<th width="5%" class="hide-mobile">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Loại BK</th>
					<th width="10%" class="hide-mobile">Giao cho</th>
					<th width="13%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Ngày xuất vé</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT 
				bk.id AS bk_id
				, bk.name AS bk_name
				, bk.booking_status
				, bk.ticket_type
				, bk.is_prior
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id), 0) AS total_ticket
				, rv.total_profit AS bk_sales
				, (
					SELECT GROUP_CONCAT(DISTINCT service_fee)
					FROM ec_booking_details
					WHERE deleted = 0 AND booking_id = bk.id
				) AS service_fee
				, (
					SELECT MIN(service_fee)
					FROM ec_booking_details
					WHERE deleted = 0 AND booking_id = bk.id
					GROUP BY booking_id
				) AS min_service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			LEFT JOIN ec_revenue rv ON rv.booking_id = bk.id AND rv.deleted = 0
			INNER JOIN ec_booking_itineraries i ON i.deleted = 0 AND i.booking_id = bk.id
			INNER JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 
			AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) BETWEEN "' . date('Y-m-d', strtotime($_POST['fdate'])) . '" AND "' . date('Y-m-d', strtotime($_POST['tdate'])) . '"
			GROUP BY bk_id
			ORDER BY FIELD(bk.booking_status, 8, 7, 3, 2, 6, 1, 4)
		';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	$total0 = $total100 = $total150 = $total200 = 0;
	$total0_com = $total100_com = $total150_com = $total200_com = 0;
	$total0_sale = $total100_sale = $total150_sale = $total200_sale = 0;

	while ($row = $db->fetchByAssoc($res)) {
		// loại bk
		$type = '';
		// là vé quốc tế
		if ($row['ticket_type'] == 2) {
			$type = 'Vé Quốc tế';
		}

		// là vé cận
		if ($row['is_prior']) {
			if (!empty($type))
				$type .= ' - ';
			$type .= 'Vé Cận';
		}

		// phí dịch vụ trong mỗi bk
		$service_fee_arr = explode(',', $row['service_fee']);
		$service_fee_arr = array_map(function ($val) {
			return format_number($val);
		}, $service_fee_arr);
		$service_fee = implode(" / ", $service_fee_arr);

		// phân loại bk theo phí dv tối thiểu
		if ($row['min_service_fee'] >= 100000 && $row['min_service_fee'] < 150000) {
			$total100++;
			$total100_sale += $row['bk_sales'];
			if (in_array($row['booking_status'], array(3, 7, 8))) {
				$total100_com++;
			}
		} else if ($row['min_service_fee'] >= 150000 && $row['min_service_fee'] < 200000) {
			$total150++;
			$total150_sale += $row['bk_sales'];
			if (in_array($row['booking_status'], array(3, 7, 8))) {
				$total150_com++;
			}
		} else if ($row['min_service_fee'] >= 200000) {
			$total200++;
			$total200_sale += $row['bk_sales'];
			if (in_array($row['booking_status'], array(3, 7, 8))) {
				$total200_com++;
			}
		} else {
			$total0++;
			$total0_sale += $row['bk_sales'];
			if (in_array($row['booking_status'], array(3, 7, 8))) {
				$total0_com++;
			}
		}

		if (empty($row['bk_date_ticket_issue']) || $row['bk_date_ticket_issue'] == '') {
			$bk_date_ticket_issue = '';
		} else {
			$bk_date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
		}

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold booking_status">
						<font color="' . $app_list_strings['booking_status_color_list'][$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][$row['booking_status']] . '</font>
					</td>
					<td class="text-center service_fee hide-mobile">' . $service_fee . '</td>
					<td class="text-center total_ticket hide-mobile">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales'] / $row['total_ticket']) . '</td>
					<td class="text-center hide-mobile">' . $type . '</td>
					<td class="text-center hide-mobile">' . $row['user_name'] . '</td>
					<td class="text-center hide-mobile">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
					<td class="text-center hide-mobile">
						' . $bk_date_ticket_issue . '
					</td>
				</tr>
			';
		$i++;
		$total++;
		$total_sale += $row['bk_sales'];
		switch ($row['booking_status']) {
			case 4:
				$canceled++;
				break;
			case 8:
				$completed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 7:
				$exported++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 3:
				$confirmed++;
				$ticket_completed += $row['total_ticket'];
				break;
			case 6:
				$called++;
				break;
			case 2:
				$paidwait++;
				break;
			case 1:
				$created++;
				break;
			default:
				break;
		}
	}
	$html2 .= '</tbody></table>';

	// so sánh %
	$canceled_txt = getComparePercentTxt($canceled, $total, 1);
	$completed_txt = getComparePercentTxt($completed, $total, 1);
	$exported_txt = getComparePercentTxt($exported, $total, 1);
	$confirmed_txt = getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt = getComparePercentTxt($paidwait, $total, 1);
	$called_txt = getComparePercentTxt($called, $total, 1);
	$created_txt = getComparePercentTxt($created, $total, 1);
	$total0_txt = getComparePercentTxt($total0, $total, 1);

	// tính % của những mức phí
	$fee100_txt = getComparePercentTxt($total100, $total, 2, array(
		'total_sale' => $total100_sale,
		'total_com' => $total100_com
	));
	$fee150_txt = getComparePercentTxt($total150, $total, 2, array(
		'total_sale' => $total150_sale,
		'total_com' => $total150_com
	));
	$fee200_txt = getComparePercentTxt($total200, $total, 2, array(
		'total_sale' => $total200_sale,
		'total_com' => $total200_com
	));

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé', $note);
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận', $note);
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT', $note);
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo', $note);
	$note .= genNoteBKStt($canceled, $canceled_txt, 'Huỷ', $note);
	$note .= genNoteBKStt($called, $called_txt, 'Đã gọi', $note);
	if ($total0_com > 0) {
		$note .= genNoteBKStt($total0_sale, '', 'Phí < 100: ' . format_number($total0) . ' - Hoàn tất: ' . format_number($total0_com) . ' - DS', $note);
	} else {
		$note .= genNoteBKStt($total0, '', 'Phí < 100', $note);
	}

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-getToTalBKInOneDay1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="7%">Tổng BK</th>
					<th width="8%">BK hoàn tất</th>
					<th width="8%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="12%" class="hide-mobile">100-150</th>
					<th width="12%" class="hide-mobile">150-200</th>
					<th width="12%" class="hide-mobile">Trên 200</th>
					<th class="hide-mobile">Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="color-red fw-semibold"><b>' . format_number($total_sale) . '</b></span></td>
					<td class="text-center">' . format_number($total_sale / $ticket_completed) . '</td>
					<td class="text-center hide-mobile">' . format_number($total100) . $fee100_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($total150) . $fee150_txt . '</td>
					<td class="text-center hide-mobile">' . format_number($total200) . $fee200_txt . '</td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
	exit();
}

// Thống kê chi tiết booking inter trong doanh số vé xuất
if (isset($_POST['for']) && $_POST['for'] == 'getInfoBookingInter') {
	global $current_user;

	$html = '<div class="box-section infor-booking__inter--wrap">
				<table id="tbl-infor-booking__inter" class="table-infor-booking__inter table-details__booking" border="0" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th width="10%" align="center">Booking</th>
							<th width="10%" align="center">Tình trạng</th>
							<th width="15%" align="center" class="hide-mobile">Liên hệ</th>
							<th width="10%" align="center" class="hide-mobile">Điện thoại</th>
							<th width="10%" align="center" class="hide-mobile">Phí dịch vụ</th>
							<th width="5%" align="center">Vé</th>
							<th width="10%" align="center">Doanh số</th>
							<th width="10%" align="center">Phí bình quân</th>
							<th width="10%" align="center" class="hide-mobile">Ngày tạo</th>
							<th align="center" class="hide-mobile">Ngày xuất vé</th>
						</tr>
					</thead>';

	$sql_search = "";
	// Từ ngày
	if (!empty($_POST['fdate']) && strtotime($_POST['fdate']) !== false) {
		$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d', strtotime($_POST['fdate'])) . "' ";
		$post_from_date = $_POST['fdate'];
	} else {
		$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d') . "' ";
		$post_from_date = date('d-m-Y');
	}

	// Đến ngày
	if (!empty($_POST['tdate']) && strtotime($_POST['tdate']) !== false) {
		$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d', strtotime($_POST['tdate'])) . "' ";
		$post_to_date = $_POST['tdate'];
	} else {
		$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d') . "' ";
		$post_to_date = date('d-m-Y');
	}

	$sql_inter = 'SELECT 
					bk.id AS parent_id
					, bk.name AS parent_name
					, "EC_Flight_Bookings" AS parent_type
					,SUM(bkd.quantity) AS total_quantity 
					,bk.total_amount AS subtotal_amount 
					,(SUM(IFNULL(bkd.total_bought_price,0)) 
					+
					IFNULL((
						SELECT IF(bk.flight_type="0", SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0) + IF(px.luggage_price_inbound>0, IFNULL(px.luggage_purchase_inbound,0), 0)), SUM(IF(px.luggage_price>0, IFNULL(px.luggage_purchase,0), 0)))
						FROM ec_booking_passengers px
						WHERE px.booking_id=bk.id AND px.deleted=0 AND px.add_type IS NULL
					),0)) AS total_bought_price
					, bk.flight_type
					, bk.ticket_type
					, bk.description AS booking_description
					, bk.booking_status AS booking_status
					, "booking_status_list" AS status_list
					, "booking_status_color_list" AS color_status_list
					, bk.is_ticket_exported
					, bk.assigned_user_id AS user_id
					, (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
					, IFNULL((
					SELECT SUM(IFNULL(r.amount_converted,0))
					FROM ec_receipt_voucher r
					WHERE r.booking_id=bkd.booking_id
						AND r.rv_status="1"
						AND r.loai_thu="1"
						AND r.deleted=0
					GROUP BY r.booking_id
					), 0) AS receipt_amount
					,DATE_FORMAT(bk.date_ticket_issue, "%d-%m-%Y") AS date_ticket_issue
					,(SELECT amount FROM ec_payment_voucher WHERE booking_id=bkd.booking_id AND pv_status="3" AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5" AND deleted=0 LIMIT 1) AS discount_amt
					,bk.phone AS contact_mobile
					,bk.contact_name AS contact_name
					,DATE_FORMAT(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS bk_date_entered
					,DATE_FORMAT(bk.date_ticket_issue, "%d-%m-%Y") AS bk_date_ticket_issue
					, bkd.service_fee AS service_fee
					, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0)) AS average_fee 
				FROM ec_booking_details bkd 
				LEFT JOIN ec_flight_bookings bk ON bkd.booking_id=bk.id AND bk.deleted=0 
				WHERE bk.booking_status IN ("7", "8")
					AND bk.ticket_type = 2
					' . $sql_search . ' 
					AND bkd.deleted=0 
				GROUP BY bk.id
							
				UNION
				SELECT 
					p.id AS parent_id
					,p.name AS parent_name
					,"EC_Receipt_Voucher" AS parent_type
					,0 AS total_quantity
					,SUM(IF(p.rv_status IN (1, 2), p.amount, 0))  AS subtotal_amount
					,SUM(
					IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount, 0), 0) 
					+ IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount2, 0), 0) 
					+ IF(p.rv_status IN (1, 2), IFNULL(p.bought_amount3, 0), 0)
					) AS total_bought_price
					,"" AS flight_type
					,"" AS ticket_type
					,p.description AS booking_description
					,p.rv_status AS parent_status
					,"receipt_voucher_status_list" AS status_list
					, "receipt_voucher_status_color_list" AS color_status_list
					,"" AS is_ticket_exported
					, p.assigned_user_id AS user_id
					,(SELECT u.user_name FROM users u WHERE u.id=p.assigned_user_id) AS user_name
					,SUM(IF(p.rv_status IN (1, 2), p.amount, 0)) AS receipt_amount
					,DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_ticket_issue
					,0 AS discount_amt
					,p.guest_phone AS contact_mobile
					,p.guest_name AS contact_name
					,DATE_FORMAT(DATE_ADD(p.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS bk_date_entered
					,"" AS bk_date_ticket_issue
					,"" AS service_fee
					,"" AS average_fee
				FROM ec_receipt_voucher p
				LEFT JOIN ec_flight_bookings bk ON bk.id = p.booking_id AND bk.deleted = 0
				WHERE bk.ticket_type = 2
				AND p.loai_thu IN ("4", "5", "10", "11", "12", "13", "14", "16") 
				AND DATE(p.ngayhachtoan)>="' . date("Y-m-d", strtotime($post_from_date)) . '"
				AND DATE(p.ngayhachtoan)<="' . date("Y-m-d", strtotime($post_to_date)) . '"
				AND p.deleted=0
				AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
				GROUP BY p.id
					
				-- hoan ve
				UNION
				SELECT hv_t.parent_id, hv_t.parent_name, hv_t.parent_type
				, SUM(hv_t.total_quantity) AS total_quantity
				, SUM(hv_t.subtotal_amount) AS subtotal_amount
				, SUM(hv_t.total_bought_price) AS total_bought_price, hv_t.flight_type
				, hv_t.ticket_type, hv_t.booking_description
				, hv_t.parent_status, hv_t.status_list, hv_t.color_status_list
				, hv_t.is_ticket_exported, hv_t.user_id, hv_t.user_name
				, hv_t.receipt_amount
				, hv_t.date_ticket_issue, hv_t.discount_amt, hv_t.contact_mobile, hv_t.contact_name
				, hv_t.date_entered AS bk_date_entered
				, "" AS bk_date_ticket_issue
				,"" AS service_fee
				,"" AS average_fee
				FROM 
				(
					SELECT 
						p.id AS parent_id
						,p.name AS parent_name
						,"EC_HoanVe" AS parent_type
						, -(SELECT COUNT(id) FROM ec_chitiethoanve WHERE deleted = 0 AND hoanve_id = p.id) AS total_quantity
						,IF( SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienhang,0)), 0)  AS subtotal_amount
						,IF( SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) <= 0, SUM(IFNULL(p.tongtienkhach,0)), 0) AS total_bought_price
						,"" AS flight_type
						,"" AS ticket_type
						,p.description AS booking_description
						,p.tinhtrang AS parent_status
						,"tinhtranghoanve_list" AS status_list
						, "tinhtranghoanvecolor_list" AS color_status_list
						,"" AS is_ticket_exported
						, bk.assigned_user_id AS user_id
						, (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
						, 0 AS receipt_amount
						,DATE_FORMAT(p.ngayhachtoan, "%d-%m-%Y") AS date_ticket_issue
						,0 AS discount_amt
						,bk.phone AS contact_mobile
						,bk.contact_name AS contact_name
						,DATE_FORMAT(DATE_ADD(p.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_entered
					FROM ec_hoanve p
					INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id
					WHERE p.deleted=0
					AND bk.ticket_type = 2
					AND p.tinhtrang="1"
					AND DATE(p.ngayhachtoan)>="' . date("Y-m-d", strtotime($post_from_date)) . '"
					AND DATE(p.ngayhachtoan)<="' . date("Y-m-d", strtotime($post_to_date)) . '"
					GROUP BY p.id

					-- hoan ve > 0
					UNION
					SELECT 
						p.id AS parent_id
						,p.name AS parent_name
						,"EC_HoanVe" AS parent_type
						, 0 AS total_quantity
						, SUM(IFNULL(p.tongtienhang,0))  AS subtotal_amount
						, SUM(IFNULL(p.tongtienkhach,0)) AS total_bought_price
						,"" AS flight_type
						,"" AS ticket_type
						,p.description AS booking_description
						,p.tinhtrang AS parent_status
						,"tinhtranghoanve_list" AS status_list
						, "tinhtranghoanvecolor_list" AS color_status_list
						,"" AS is_ticket_exported
						, p.assigned_user_id AS user_id
						, (SELECT u.user_name FROM users u WHERE u.id=bk.assigned_user_id) AS user_name
						, 0 AS receipt_amount
						,DATE_FORMAT(p.ngayhachtoan, "%d-%m-%Y") AS date_ticket_issue
						,0 AS discount_amt
						,bk.phone AS contact_mobile
						,bk.contact_name AS contact_name
						,DATE_FORMAT(DATE_ADD(p.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_entered
					FROM ec_hoanve p
					INNER JOIN ec_flight_bookings bk ON bk.deleted = 0 AND bk.id = p.booking_id
					WHERE p.deleted=0
					AND bk.ticket_type = 2
					AND p.tinhtrang="1" 
					AND p.ngayhachtoan>="' . date('Y-m-d', strtotime($post_from_date)) . '"
					AND p.ngayhachtoan<="' . date('Y-m-d', strtotime($post_to_date)) . '"
					GROUP BY p.id
					HAVING SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) > 0
					) AS hv_t
		GROUP BY hv_t.parent_id
		ORDER BY total_quantity DESC';

	// if($current_user->user_name == 'hungnh'){
	// 	pr($sql_inter);
	// }

	$res_inter = $db->query($sql_inter);
	$i = 0;
	$total_ticket = 0;
	$total_bk_sales = 0;
	$total_amount_inter = 0;
	while ($row = $db->fetchByAssoc($res_inter)) {

		if ($row['bk_date_ticket_issue'] == '' || empty($row['bk_date_ticket_issue'])) {
			$date_ticket_issue = '';
		} else {
			$date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
		}

		$html .= '<tr>
					<td align="center"><a href="index.php?module=' . $row['parent_type'] . '&amp;action=DetailView&amp;record=' . $row['parent_id'] . '" target="_blank">' . $row['parent_name'] . '</a></td>
					<td align="center" class="fw-bold" style="color:' . $app_list_strings[$row['color_status_list']][$row['booking_status']] . '">' . $app_list_strings[$row['status_list']][$row['booking_status']] . '</td>
					<td align="left" class="hide-mobile">' . $row['contact_name'] . '</td>
					<td align="left" class="hide-mobile">' . $row['contact_mobile'] . '</td>
					<td align="center" class="hide-mobile">' . format_number($row['service_fee']) . '</td>
					<td align="center">' . $row['total_quantity'] . '</td>
					<td align="right" class="fw-bold">' . format_number(($row['subtotal_amount'] - $row['total_bought_price'])) . '</td>
					<td align="center">' . format_number($row['average_fee']) . '</td>
					<td align="center" class="hide-mobile">' . date('d-m-Y', strtotime($row['bk_date_entered'])) . '</td>
					<td align="center" class="hide-mobile">' . $date_ticket_issue . '</td>
				</tr>';
		$i++;
		$total_ticket += $row['total_quantity'];
		$total_bk_sales += ($row['subtotal_amount'] - $row['total_bought_price']);
		$total_amount_inter += $row['subtotal_amount'];
	}

	$html .= '<tr class="footer-tr">
				<td colspan="2" class="text-start fw-bold">Số dòng = ' . $i . '</td>
				<td class="hide-mobile">&nbsp;</td>
				<td class="hide-mobile">&nbsp;</td>
				<td class="fee-service hide-mobile">&nbsp;</td>
				<td align="center">' . format_number($total_ticket) . '</td>
				<td align="right" class="text-end fw-bold color-red">' . format_number($total_bk_sales) . '</td>
				<td align="right" class="text-end fw-bold color-red">' . format_number($total_bk_sales / $total_ticket) . '</td>
				<td class="hide-mobile">&nbsp;</td>
				<td class="hide-mobile">&nbsp;</td>
			</tr>';

	$html .= '</table></div>';

	echo $html;
}

// Thống kê chi tiết booking inter trong doanh số vé xuất
if (isset($_POST['for']) && $_POST['for'] == 'getInfoBookingDomestic') {
	global $current_user;

	$sql_search = "";
	// Từ ngày
	if (!empty($_POST['fdate']) && strtotime($_POST['fdate']) !== false) {
		$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d', strtotime($_POST['fdate'])) . "' ";
		$post_from_date = $_POST['fdate'];
	} else {
		$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d') . "' ";
		$post_from_date = date('d-m-Y');
	}

	// Đến ngày
	if (!empty($_POST['tdate']) && strtotime($_POST['tdate']) !== false) {
		$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d', strtotime($_POST['tdate'])) . "' ";
		$post_to_date = $_POST['tdate'];
	} else {
		$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d') . "' ";
		$post_to_date = date('d-m-Y');
	}

	$sql_domestic = '
		SELECT 
			name as parent_name,
			booking_id AS parent_id, 
			"EC_Flight_Bookings" AS parent_type, 
			date_entered_bk as bk_date_entered,
			ticket_type,
			date_ticket_issue,
			ticket_qty as total_quantity,
			total_amount as subtotal_amount,
			total_purchase as total_bought_price,
			date_ticket_issue as bk_date_ticket_issue,
			total_profit
			FROM ec_revenue bk
			WHERE
			deleted = 0
			' . $sql_search . ' 
			ORDER BY ticket_qty DESC, total_profit DESC
	';

	// if($current_user->user_name == 'hungnh'){
	// 	pr($sql_domestic);
	// }

	$html = '<div class="box-section infor-booking__domestic--wrap">
			<table id="tbl-infor-booking__domestic" class="table-infor-booking__domestic table-details__booking" border="0" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th width="3%" align="center">STT</th>
						<th width="6%" align="center">Booking</th>
						<th width="3%" align="center">Vé</th>
						<th width="3%" align="center">Loại vé</th>
						<th width="8%" align="center">Doanh số</th>
						<th width="8%" align="center">Phí bình quân</th>
						<th width="8%" align="center" class="hide-mobile">Ngày tạo</th>
						<th class="hide-mobile" width="8%" align="center">Ngày xuất vé</th>
					</tr>
				</thead>';

	$res_domestic = $db->query($sql_domestic);
	$i = 0;
	$total_ticket = 0;
	$total_bk_sales = 0;
	while ($row = $db->fetchByAssoc($res_domestic)) {
		if ($row['bk_date_ticket_issue'] == '' || empty($row['bk_date_ticket_issue'])) {
			$date_ticket_issue = '';
		} else {
			$date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
		}

		$ticket_type_text = ((int) $row['ticket_type'] === 1 ? '<span class="text-dark fw-semibold">Nội địa</span>' : '<span class="text-success fw-semibold">Quốc tế</span>');

		$html .= '<tr>
					<td align="center">' . ($i + 1) . '</td>
					<td align="center"><a href="index.php?module=' . $row['parent_type'] . '&amp;action=DetailView&amp;record=' . $row['parent_id'] . '" target="_blank">' . $row['parent_name'] . '</a></td>
					<td align="center">' . $row['total_quantity'] . '</td>
					<td align="center">' . $ticket_type_text . '</td>
					<td align="right" class="fw-bold">' . format_number(($row['subtotal_amount'] - $row['total_bought_price'])) . '</td>
					<td align="right" class="fw-bold">' . format_number(($row['total_profit'] / $row['total_quantity'])) . '</td>
					<td align="center" class="hide-mobile">' . date('d-m-Y', strtotime($row['bk_date_entered'])) . '</td>
					<td align="center" class="hide-mobile">' . $date_ticket_issue . '</td>
				</tr>';
		$i++;
		$total_ticket += $row['total_quantity'];
		$total_bk_sales += ($row['subtotal_amount'] - $row['total_bought_price']);
	}

	$html .= '<tr class="footer-tr">
				<td class="text-start fw-bold" colspan="2">Số dòng = ' . $i . '</td>
				<td align="center">' . format_number($total_ticket) . '</td>
				<td class="hide-mobile">&nbsp;</td>
				<td align="right" class="text-end fw-bold color-red">' . format_number($total_bk_sales) . '</td>
				<td align="right" class="text-end fw-bold color-red">' . format_number($total_bk_sales / $total_ticket) . '</td>
				<td class="hide-mobile">&nbsp;</td>
				<td class="hide-mobile">&nbsp;</td>
			</tr>';

	$html .= '</table></div>';

	echo $html;
}

/*
	Danh sách booking của liên hệ - QUY ĐỊNH 1 SDT LÀ 1 LIÊN HỆ
*/
if (isset($_POST['for']) && $_POST['for'] == 'showHistoryBookingContact') {
	global $current_user;

	$contact_id = isset($_POST['contact_id']) ? $_POST['contact_id'] : '';
	$where = (isset($_POST['purpose']) && $_POST['purpose'] == 'get_completed_status') ? ' AND booking_status = 8' : '';
	$booking_id = $_POST['booking_id'] ?? '';

	$html = $html_summary = '';
	$type_contact = '';
	$name_contact = '';
	$phone_contact = '';
	$email_contact = '';

	$total_ = 0;
	$count_booking = 0;
	$count_booking_completed = 0;
	$count_booking_cancel = 0;
	$count_booking_other = 0;

	$total_revenue = 0;
	$total_profit = 0;

	if ($contact_id) {
		$html = '<div class="list-booking-customer">
					<table class="tbl-check-contact-info table-details__booking">
						<thead>
							<tr>
								<th class="hide-mobile">STT</th>
								<th>Booking</th>
								<th class="hide-mobile">Hành trình</th>
								<th class="hide-mobile">Tình trạng</th>
								<th>Ngày đặt</th>
								<th>Ngày xuất vé</th>
								<th>Liên hệ</th>
								<th>Số vé</th>
								<th>Doanh thu</th>
								<th>Doanh số</th>
							</tr>
						</thead>';

		$sql = "SELECT id, name, contact_name, phone, email, journey, booking_status, total_amount, total_qty, date_entered, date_ticket_issue
				FROM ec_flight_bookings
				WHERE contact_id = '$contact_id' AND deleted = 0
				" . $where . "
				ORDER BY date_entered DESC";

		$res = $db->query($sql);
		$count_booking = $db->countRows($res);

		$con = new Contact();
		$con->retrieve($contact_id);
		$name_contact = $con->last_name;
		$phone_contact = $con->phone_mobile;
		$email_contact = $con->email1;
		$points_contact = $con->points ?? 0;

		if ($count_booking > 0) {
			$i = 1;
			while ($row = $db->fetchByAssoc($res)) {
				// Infor contact
				$current_date = date('Y-m-d');
				if ($row['booking_status'] == 2) { // CHỜ THANH TOÁN
					$class_color = 'text-warning';
				} elseif ($row['booking_status'] == 3 || $row['booking_status'] == 7) { // XÁC NHẬN
					$class_color = 'text-success';
				} elseif ($row['booking_status'] == 4) { // HỦY
					$class_color = 'text-danger';
				} elseif ($row['booking_status'] == 6) { // ĐÃ GỌI
					$class_color = 'text-info';
				} elseif ($row['booking_status'] == 8) { // HOÀN TẤT
					$class_color = 'text-primary';
				} else {
					$class_color = 'text-dark';
				}

				// Journey
				if ($row['journey']) {
					$journey = $row['journey'];
				} else {
					$journey_array = journeyOfBooking($row['id']);
					$journey = $journey_array["departure"] . '-' . $journey_array["arrival"];
				}

				if ($row['booking_status'] == 8) {
					$count_booking_completed++;

					$total_revenue += (int) $row['total_amount'];
					$total_profit += (int) calculateBKTotalAmt($row['id']);
				} else if ($row['booking_status'] == 4) {
					$count_booking_cancel++;
				} else {
					$count_booking_other++;
				}

				$current_booking = ($row['id'] == $booking_id) ? 'current_booking' : '';

				$html .= '<tr>
							<td class="' . $current_booking . ' hide-mobile fw-bold text-center">' . $i . '</td>
							<td class="' . $current_booking . '"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a></td>
							<td class="' . $current_booking . ' hide-mobile text-center">' . $journey . '</td>
							<td class="' . $current_booking . ' hide-mobile text-center fw-bold ' . $class_color . '">' . $app_list_strings['booking_status_list'][(int) $row['booking_status']] . '</td>
							<td class="' . $current_booking . ' text-center">' . date('H:i d-m-Y', strtotime('+7 hours', strtotime($row['date_entered']))) . '</td>
							<td class="' . $current_booking . ' text-center">' . (!empty($row['date_ticket_issue']) ? date('d-m-Y', strtotime($row['date_ticket_issue'])) : '') . '</td>
							<td class="' . $current_booking . '">' . $row['contact_name'] . '</td>
							<td class="' . $current_booking . ' text-center fw-bold">' . $row['total_qty'] . '</td>
							<td class="' . $current_booking . ' text-end fw-bold">' . format_number((int) $row['total_amount']) . '</td>
							<td class="' . $current_booking . ' text-end fw-bold">' . format_number((int) calculateBKTotalAmt($row['id'])) . '</td>
						</tr>';
				$i++;
			}
		}

		// $type_contact = classifyContact($contact_id);
		$type_contact = classifyContactv2($contact_id);

		$html .= '</table></div>';

		$denominator_booking_completed = ($count_booking_completed == 0) ? 1 : $count_booking_completed;
		$html_summary .= '<div class="d-flex gap-2 mb-3 flex-nowrap">
			<div class="flex-fill lh-base">
				<p>👤 *Họ tên: ' . $name_contact . '</p>  
				<p>📞 *SĐT: ' . $phone_contact . '</p>
				<p>📧 *Email: ' . $email_contact . '</p>
				<p>⭐ *Điểm tích lũy: <span style="color:red">' . $points_contact . ' điểm</span></p>
			</div>
			<div class="flex-fill lh-base">
				<p>🏷️ *Loại khách hàng: <span class="fw-bold">' . ($type_contact['label'] ?? '') . '</span></p>  
				<p>📊 *Tổng booking: ' . $count_booking . ' (<span class="text-primary fw-bold">' . $count_booking_completed . ' hoàn tất</span>, <span class="text-danger fw-bold">' . $count_booking_cancel . ' hủy</span>, <span class="text-dark fw-bold">' . $count_booking_other . ' Khác</span>)</p>
				<p>✅ *Tỷ lệ hoàn tất: <span class="fw-bold">' . round(($count_booking_completed / $count_booking * 100), 2) . '%</span></p>
				<p>❌ *Tỷ lệ hủy: <span class="fw-bold">' . round(($count_booking_cancel / $count_booking * 100), 2) . '%</span></p>
				<p>🏷️ *Mô tả: ' . ($type_contact['desc'] ?? '') . '</p>
			</div>
			<div class="flex-fill lh-base">
				<p>💰 *Tổng doanh thu: <span class="fw-bold">' . format_number($total_revenue) . '</span></p>  
				<p>💸 *Doanh thu trung bình: <span class="fw-bold">' . format_number($total_revenue / $denominator_booking_completed) . '</span></p>
				<p>💰 *Tổng doanh số: <span class="fw-bold">' . format_number($total_profit) . '</span></p>  
				<p>💸 *Doanh số trung bình: <span class="fw-bold">' . format_number($total_profit / $denominator_booking_completed) . '</span></p>
			</div>
		</div>';
	}


	echo $html_summary . $html;
}

// Handle points to booking
if (isset($_POST['for']) && $_POST['for'] == 'apply_points') {
	$apply_points = $_POST['apply_points'] ?? 0;
	$contact_id = $_POST['contact_id'] ?? '';
	$booking_id = $_POST['booking_id'] ?? '';

	if (strlen($contact_id) == 36 && strlen($booking_id) == 36) {
		$Booking = new EC_Flight_Bookings();
		$total_points = $db->getOne("SELECT points FROM contacts WHERE id = '$contact_id' AND deleted = 0");

		if ($apply_points < $Booking->point_step || $apply_points > $total_points || $apply_points % $Booking->point_step != 0) {
			echo json_encode([
				'error' => 1,
				'message' => 'Số điểm áp dụng không hợp lệ',
				'data' => [
					'apply_points' => $apply_points,
					'total_points' => $total_points,
					'point_step' => $Booking->point_step,
				]
			]);
			exit();
		}

		$discount = $apply_points * 1000;
		$sql_update_contact = "UPDATE contacts SET points = points - $apply_points WHERE id = '$contact_id' AND deleted = 0;";
		$sql_update_booking = "UPDATE ec_flight_bookings SET discount_amount = discount_amount + $discount, total_amount = total_amount - $discount WHERE id = '$booking_id' AND deleted = 0;";
		$res_contact = $db->query($sql_update_contact);
		$res_booking = $db->query($sql_update_booking);

		if ($res_contact === false) {
			echo json_encode([
				'error' => 1,
				'message' => 'Lỗi! Vui lòng thử lại sau',
				'data' => $sql_update_contact
			]);
			exit();
		}
		if ($res_booking === false) {
			// Rollback
			$sql_update_contact = "UPDATE contacts SET points = points + $apply_points WHERE id = '$contact_id' AND deleted = 0;";
			$res_contact = $db->query($sql_update_contact);

			echo json_encode([
				'error' => 1,
				'message' => 'Lỗi! Vui lòng thử lại sau',
				'data' => $sql_update_booking
			]);
			exit();
		}

		// Record point log
		$point_log = new EC_Contact_Points_Log();
		$point_log->id = '';
		$point_log->name = 'Dùng điểm tích lũy cho booking';
		$point_log->contact_id = $contact_id;
		$point_log->contact_phone = $db->getOne("SELECT phone_mobile FROM contacts WHERE id = '$contact_id' AND deleted = 0");
		$point_log->up = 0;
		$point_log->down = $apply_points;
		$point_log->current_point = $total_points - $apply_points;
		$point_log->parent_type = 'EC_Flight_Bookings';
		$point_log->parent_id = $booking_id;
		$point_log->save();

		echo json_encode(['error' => 0, 'message' => 'Success']);
		exit();
	}

	echo json_encode(['error' => 1, 'message' => 'Invalid params']);
	exit();
}

if (isset($_POST['for']) && $_POST['for'] == 'refund_points') {
	$parent_id = $_POST['parent_id'] ?? '';
	$contact_id = $_POST['contact_id'] ?? '';
	$reason = $_POST['reason'] ?? '';

	if (strlen($contact_id) == 36 && strlen($parent_id) == 36) {
		$point_log = new EC_Contact_Points_Log();
		$point_log->retrieve($parent_id);
		if ($point_log->id == $parent_id && $point_log->down > 0) {
			$refund_points = (int) $point_log->down; // Used point
			$refund_amount = $refund_points * 1000;

			if ($point_log->parent_type == 'EC_Flight_Bookings') {
				$sql_refund_discount = "UPDATE ec_flight_bookings
					SET discount_amount = discount_amount - $refund_amount, total_amount = total_amount + $refund_amount
					WHERE id = '$point_log->parent_id'
						AND booking_status IN('1', '2', '6')
						AND discount_amount >= $refund_amount
						AND deleted = 0";

				$sql_refund_point = "UPDATE contacts SET points = points + $refund_points WHERE id = '$contact_id' AND deleted = 0";

				if ($db->query($sql_refund_discount) && $db->query($sql_refund_point)) {
					$total_points = $db->getOne("SELECT points FROM contacts WHERE id = '$contact_id' AND deleted = 0");
					$booking_name = $db->getOne("SELECT name FROM ec_flight_bookings WHERE id = '$point_log->parent_id' AND deleted = 0");

					$point_log_refund = new EC_Contact_Points_Log();
					$point_log_refund->id = '';
					$point_log_refund->name = "Hoàn điểm từ booking $booking_name";
					$point_log_refund->contact_id = $contact_id;
					$point_log_refund->contact_phone = $db->getOne("SELECT phone_mobile FROM contacts WHERE id = '$contact_id' AND deleted = 0");
					$point_log_refund->up = $refund_points;
					$point_log_refund->down = 0;
					$point_log_refund->current_point = $total_points + $refund_points;
					$point_log_refund->parent_type = 'EC_Contact_Points_Log';
					$point_log_refund->parent_id = $parent_id;
					$point_log_refund->description = $reason;
					$point_log_refund->save();

					echo json_encode(['error' => 0, 'message' => 'Success']);
					exit();
				}

				echo json_encode(['error' => 1, 'message' => 'Booking cannot be refunded points']);
				exit();
			}

			echo json_encode(['error' => 1, 'message' => 'There is no refund policy']);
			exit();
		}
	}

	echo json_encode(['error' => 1, 'message' => 'Failed']);
	exit();
}

/**
 * Preview send mail
 */
if (isset($_POST['for']) && $_POST['for'] == 'previewSendMail') {
	$booking_id = $_POST['booking_id'] ?? '';
	/** @var EC_Flight_Bookings **/
	$bk = BeanFactory::getBean('EC_Flight_Bookings', $booking_id);

	$contact_name 		= ucwords(myRemoveUnicodeChars($bk->contact_name));
	$bk_name 			= ucwords(myRemoveUnicodeChars($bk->name));
	$bk_status 			= $app_list_strings['booking_status_list'][$bk->booking_status];
	$trip_type 			= $app_list_strings['bk_flight_type_list'][$bk->flight_type];
	$payment_type 		= $app_list_strings['booking_payment_type_list'][$bk->payment_type];
	$total_amount		= format_number($bk->total_amount) . ' VND';

	$html = '';

	// Block infor booking
	$html = '<div class="container text-dark">
				<div class="row mb-3">
					<div class="col-4">
						<span>Mã đơn hàng</span>
					</div>
					<div class="col-8">
						<span class="text-danger fw-semibold">' . $bk_name . '</span>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-4">
						<span>Loại vé</span>
					</div>
					<div class="col-8">
						<span class="text-dark fw-semibold">' . $trip_type . '</span>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-4">
						<span>Hình thức thanh toán</span>
					</div>
					<div class="col-8">
						<span class="text-dark fw-semibold">' . $payment_type . '</span>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-4">
						<span>Tổng số tiền</span>
					</div>
					<div class="col-8">
						<span class="text-danger fw-semibold">' . $total_amount . '</span>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-4">
						<span>Số điện thoại</span>
					</div>
					<div class="col-8">
						<span class="text-dark fw-semibold">' . $bk->phone . '</span>
					</div>
				</div>
			</div>
		';

	// Block infor Passenger Mail Confirm
	$pas_info = $bk->getPassengerInfoMailConfirm($booking_id, $bk->flight_type);
	$html .= '<div class="container text-dark">
				<div class="row mb-3">
					<div class="col-12">
						<table align="center" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								' . $pas_info . '
							</tbody>
						</table>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-12">
						<img src="themes/SuiteP/images/modules/ec_flight_booking/row-dash.png" class="w-100">
					</div>
				</div>
			</div>
		';

	// Block infor Route Mail Confirm
	$route_infos = $bk->getRouteInfosMailConfirm($booking_id, 'preview');
	$html .= '<div class="container text-dark">
				<div class="row mb-3">
					<div class="col-12">
						' . $route_infos['html'] . '
					</div>
				</div>
			</div>
		';

	echo $html;
	exit();
}

/**
 * Chi tiết hành trình
 */
if (isset($_POST['for']) && $_POST['for'] == 'getDetailsAirportStatistics') {
	global $app_list_strings;

	$from_date = isset($_POST['from_date']) && strtotime($_POST['from_date']) !== false ? date('Y-m-d', strtotime($_POST['from_date'])) : '';
	$to_date = isset($_POST['to_date']) && strtotime($_POST['to_date']) !== false ? date('Y-m-d', strtotime($_POST['to_date'])) : '';

	$departure     = $_POST['departure'] ?? '';
	$arrival       = $_POST['arrival'] ?? '';
	$raw_status    = isset($_POST['status_filter']) ? (string)$_POST['status_filter'] : '';
	$scope         = $_POST['scope'] ?? '';

	$user_list = get_user_array(true, 'Active', '', true);

	// Khớp với report (bk = tổng tất cả BK; Tham khảo là tập con is_reference=1):
	//   'reference' → chỉ BK tham khảo (khớp cột Tham khảo)
	//   'completed' → BK hoàn tất (8/7/3), gồm cả tham khảo (khớp bk_ok)
	//   'booker'/'customer' → lọc thêm ở PHP sau khi fetch (xem ec_classify_booking_source bên dưới)
	//   digit       → theo 1 trạng thái
	//   '' (tất cả) → tất cả BK, không lọc gì (khớp mẫu số bk)
	if ($raw_status === 'reference') {
		$status_where = "AND IFNULL(bk.is_reference, 0) = 1";
	} elseif ($raw_status === 'completed') {
		$status_where = "AND bk.booking_status IN ('8','7','3')";
	} elseif (ctype_digit($raw_status)) {
		$status_where = "AND bk.booking_status = '" . (int)$raw_status . "'";
	} else {
		$status_where = '';
	}

	// Chuyển khoảng ngày sang UTC để dùng index trên date_entered (stored in UTC)
	$from_utc_detail = gmdate('Y-m-d H:i:s', strtotime($from_date . ' 00:00:00'));
	$to_utc_detail   = gmdate('Y-m-d H:i:s', strtotime($to_date . ' 23:59:59'));

	// Subquery: pre-filter booking theo date trước, rồi mới tính route qua window function
	$route_subquery = "
		SELECT DISTINCT
			i.booking_id,
			FIRST_VALUE(i.departure) OVER (PARTITION BY i.booking_id ORDER BY i.departure_date ASC) AS departure,
			LAST_VALUE(i.arrival) OVER (PARTITION BY i.booking_id ORDER BY i.departure_date ASC ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) AS arrival
		FROM ec_booking_itineraries i
		JOIN ec_flight_bookings b_f ON b_f.id = i.booking_id AND b_f.deleted = 0
			AND b_f.date_entered BETWEEN '{$from_utc_detail}' AND '{$to_utc_detail}'
		WHERE i.direction=0 AND i.add_type=0 AND i.deleted=0
	";

	$dom_keys = array_keys(EC_Airports::getAirportList(EC_Airports::AIRPORT_SCOPE_DOMESTIC));
	if ($scope === 'domestic') {
		$dom_in   = "'" . implode("','", $dom_keys) . "'";
		$route_filter = "AND route.departure IN ({$dom_in}) AND route.arrival IN ({$dom_in})";
	} elseif ($scope === 'international') {
		$dom_in   = "'" . implode("','", $dom_keys) . "'";
		$route_filter = "AND (route.departure NOT IN ({$dom_in}) OR route.arrival NOT IN ({$dom_in}))";
	} elseif ($scope === 'country' && !empty($_POST['dest_country'])) {
		$dest_country = $db->quote($_POST['dest_country']);
		$cond = "(SELECT country FROM ec_airports WHERE iata_code = route.arrival AND deleted = 0 LIMIT 1) = '{$dest_country}'";
		// Ràng buộc theo nhóm (report tách Nội địa/Quốc tế theo cả điểm đi & đến)
		$grp = $_POST['grp'] ?? '';
		if ($grp === 'dom' || $grp === 'intl') {
			$dom_in   = "'" . implode("','", $dom_keys) . "'";
			if ($grp === 'dom') {
				$cond .= " AND route.departure IN ({$dom_in}) AND route.arrival IN ({$dom_in})";
			} else {
				$cond .= " AND (route.departure NOT IN ({$dom_in}) OR route.arrival NOT IN ({$dom_in}))";
			}
		}
		$route_filter = "AND {$cond}";
	} elseif ($scope === 'all') {
		$route_filter = "";
	} else {
		$route_filter = "AND route.departure = '{$departure}' AND route.arrival = '{$arrival}'";
	}

	$sql = "
		SELECT
			bk.id,
			bk.name,
			bk.booking_status,
			bk.contact_name,
			bk.date_entered,
			bk.date_ticket_issue,
			bk.total_qty,
			bk.created_by,
			bk.description,
			MAX(u.last_name) AS site_name
		FROM ec_flight_bookings bk
		INNER JOIN ({$route_subquery}) route ON route.booking_id = bk.id
			{$route_filter}
		LEFT JOIN users u ON u.id = bk.created_by AND u.deleted = 0
		WHERE bk.date_entered BETWEEN '{$from_utc_detail}' AND '{$to_utc_detail}'
		AND bk.deleted = 0
		{$status_where}
		GROUP BY bk.id
		ORDER BY bk.created_by, bk.date_entered DESC
	";

	// pr($sql);

	$res = $db->query($sql);

	// Lấy toàn bộ dòng trước, tính doanh số real-time 1 lần (batch) để nhanh
	$rows = [];
	$bk_ids = [];
	while ($row = $db->fetchByAssoc($res)) {
		$rows[]   = $row;
		$bk_ids[] = $row['id'];
	}

	// 'booker'/'customer' không lọc được thuần SQL (cần đối chiếu audit) nên lọc ở PHP,
	// dùng chung hàm phân loại với report (1 query phẳng, không EXISTS tương quan).
	if ($raw_status === 'booker' || $raw_status === 'customer') {
		$contact_names = array_column($rows, 'contact_name', 'id');
		$source_map    = ec_classify_booking_source($bk_ids, $contact_names);
		$want_booker   = ($raw_status === 'booker');
		$rows = array_values(array_filter($rows, function ($row) use ($source_map, $want_booker) {
			$src = $source_map[$row['id']] ?? ['is_booker' => false, 'is_customer' => false];
			return $want_booker ? $src['is_booker'] : $src['is_customer'];
		}));
		$bk_ids = array_column($rows, 'id');
	}

	$revenue_map = calculateBKTotalAmtBatch($bk_ids); // [booking_id => doanh số ròng] (chỉ status 8/7/3)

	$html = '<table class="tbl-check-details-airport-analysis table-details__booking">
				<thead>
					<tr>
						<th class="hide-mobile">STT</th>
						<th>Booking</th>
						<th class="hide-mobile">Tình trạng</th>
						<th>Ngày đặt</th>
						<th>Ngày xuất vé</th>
						<th title="Site/tài khoản tạo booking">Trang web</th>
						<th>Liên hệ</th>
						<th>Ghi chú</th>
						<th>Số vé</th>
						<th>Doanh số</th>
					</tr>
				</thead>
				<tbody>';
	$i             = 1;
	$total_qty     = 0;
	$total_revenue = 0;
	foreach ($rows as $row) {
		if ($row['booking_status'] == 2) { // CHỜ THANH TOÁN
			$class_color = 'text-warning';
		} elseif ($row['booking_status'] == 3 || $row['booking_status'] == 7) { // XÁC NHẬN
			$class_color = 'text-success';
		} elseif ($row['booking_status'] == 4) { // HỦY
			$class_color = 'text-danger';
		} elseif ($row['booking_status'] == 6) { // ĐÃ GỌI
			$class_color = 'text-info';
		} elseif ($row['booking_status'] == 8) { // HOÀN TẤT
			$class_color = 'text-primary';
		} else {
			$class_color = 'text-dark';
		}

		$bk_revenue     = (int)($revenue_map[$row['id']] ?? 0);
		$total_qty     += (int)$row['total_qty'];
		$total_revenue += $bk_revenue;

		$html .= '<tr>
					<td class=" hide-mobile fw-bold text-center">' . $i . '</td>
					<td class=""><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a></td>
					<td class=" hide-mobile text-center fw-bold ' . $class_color . '">' . $app_list_strings['booking_status_list'][(int) $row['booking_status']] . '</td>
					<td class=" text-center">' . date('H:i d-m-Y', strtotime('+7 hours', strtotime($row['date_entered']))) . '</td>
					<td class=" text-center">' . (!empty($row['date_ticket_issue']) ? date('d-m-Y', strtotime($row['date_ticket_issue'])) : '') . '</td>
					<td class="">' . htmlspecialchars((string)($row['site_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>
					<td class="">' . $row['contact_name'] . '</td>
					<td class="text-wrap">' . $row['description'] . '</td>
					<td class="text-center fw-bold">' . $row['total_qty'] . '</td>
					<td class="text-end fw-bold">' . format_number($bk_revenue) . '</td>
				</tr>';
		$i++;
	}

	$html .= '</tbody>
			<tfoot>
				<tr class="fw-bold">
					<td colspan="8" class="text-end"><i>Tổng cộng</i></td>
					<td class="text-center">' . format_number($total_qty) . '</td>
					<td class="text-end">' . format_number($total_revenue) . '</td>
				</tr>
			</tfoot>
		</table>';
	echo $html;
	exit();
}

/**
 * Lưu chi phí quảng cáo theo ngày (báo cáo DS theo ngày xuất vé). Chỉ admin; chỉnh trong 3 ngày gần nhất.
 */
if (isset($_POST['for']) && $_POST['for'] === 'saveDailyAdCost') {
	if (empty($current_user->id) || !is_admin($current_user)) {
		echo json_encode(['ok' => false, 'message' => 'Chỉ quản trị viên mới được nhập chi phí quảng cáo.']);
		exit();
	}

	date_default_timezone_set('Asia/Ho_Chi_Minh');
	$cost_date = isset($_POST['cost_date']) ? trim((string) $_POST['cost_date']) : '';
	if ($cost_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $cost_date) !== 1) {
		$ts = strtotime($cost_date);
		$cost_date = $ts ? date('Y-m-d', $ts) : '';
	}

	if ($cost_date === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $cost_date) !== 1) {
		echo json_encode(['ok' => false, 'message' => 'Ngày không hợp lệ.']);
		exit();
	}

	$amount = ad_cost_parse_amount($_POST['amount'] ?? '0');
	if ($amount < 0) {
		$amount = 0;
	}

	ad_cost_ensure_table($db);
	$qdate = $db->quote($cost_date);
	$res = $db->query("SELECT id FROM ec_daily_ad_cost WHERE cost_date = '{$qdate}' AND deleted = 0 LIMIT 1");
	$row = $res ? $db->fetchByAssoc($res) : null;
	$now = gmdate('Y-m-d H:i:s');
	$uid = $db->quote($current_user->id);
	$amtSql = number_format((float) $amount, 2, '.', '');

	if (!empty($row['id'])) {
		// Đã có record → chỉ cho sửa trong 3 ngày gần nhất
		if (!ad_cost_is_editable_date($cost_date)) {
			echo json_encode(['ok' => false, 'message' => 'Chỉ được sửa chi phí quảng cáo trong 3 ngày gần nhất (hôm nay và 2 ngày trước).']);
			exit();
		}
		$rid = $db->quote($row['id']);
		$db->query("UPDATE ec_daily_ad_cost SET amount = {$amtSql}, modified_user_id = '{$uid}', date_modified = '{$now}' WHERE id = '{$rid}'");
	} else {
		// Chưa có record → cho phép thêm mới không giới hạn ngày
		$id = create_guid();
		$qid = $db->quote($id);
		$sql_insert = "INSERT INTO ec_daily_ad_cost (id, cost_date, amount, created_by, modified_user_id, date_entered, date_modified, deleted)
			VALUES ('{$qid}', '{$qdate}', {$amtSql}, '{$uid}', '{$uid}', '{$now}', '{$now}', 0)";

		$db->query($sql_insert);
	}

	$formatted = format_number($amount);
	$canEdit = ad_cost_is_editable_date($cost_date);
	echo json_encode(['ok' => true, 'message' => 'Đã lưu.', 'amount' => $amount, 'formatted' => $formatted, 'can_edit' => $canEdit]);
	exit();
}
