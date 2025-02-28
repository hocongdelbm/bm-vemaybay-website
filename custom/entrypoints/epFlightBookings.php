<?php

global $db, $current_user, $app_list_strings;

if (isset($_POST['for']) && $_POST['for'] == 'getBookingStatus') {
	$user_list = get_user_array(true, 'Active', '', true);
	$tbl_detail = '<table id="status-detail" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff"><tbody>';

	// $sql = 'SELECT *, DATE_ADD(date_created, INTERVAL 7 HOUR) AS date_modified FROM ec_flight_bookings_audit WHERE parent_id = "' . $_POST['booking_id'] . '" AND field_name = "booking_status" ORDER BY date_created DESC';
	$sql = 'SELECT created_by,
				after_value_string, 
				DATE_ADD(date_created, INTERVAL 7 HOUR) AS date_modified 
			FROM ec_flight_bookings_audit 
			WHERE parent_id = "' . $_POST['booking_id'] . '" AND field_name = "booking_status" 
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
}

if (isset($_POST['for']) && $_POST['for'] == 'editDescription') {
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['id']);
	$booking->description = $_POST['description'];
	$booking->save2();
}

// lấy các dòng điểm về tác phong / ý thức / hiệu quả của nhân viên
if (isset($_POST['for']) && $_POST['for'] == 'populateDetailMark') {
	$sql_type = ' AND curr.parent_type = "' . $_POST['type'] . '"';
	$sql_sum 	= 'IFNULL(curr.' . $_POST['type'] . ', 0)';

	if ($_POST['type'] == 'effected') {
		$sql_type = ' AND (curr.parent_type = "' . $_POST['type'] . '" OR curr.ticket_delivery = 1)';
		$sql_sum .= ' + IFNULL(curr.ticket_delivery, 0)';
	}

	$sql_mark = '
			SELECT SUM(' . $sql_sum . ') AS type_value	
			FROM ec_working_process curr 
			WHERE curr.deleted = 0  
			AND curr.assigned_user_id = "' . $_POST['employee'] . '"
			AND DATE(DATE_ADD(date_entered, INTERVAL 7 HOUR)) = "' . date('Y-m-d', strtotime($_POST['date_search'])) . '"
			' . $sql_type . '
			LIMIT 1
		';

	// $res_mark = $db->getOne($sql_mark);
	$res_mark_query 	= $db->query($sql_mark);
	$res_mark 		= $db->fetchByAssoc($res_mark_query);

	if ($res_mark['type_value'] == '') {
		$curr = 0;
	} else $curr = $res_mark['type_value'];


	$html .= '
		<tr>
			<td align="center" id="curr_mark">' . $curr . '</td>
			<td><input type="text" class="box-input text-center allow-number-only2" id="mark" onblur="calculateLeftMark()"></td>
			<td id="left_mark" align="center"></td>
			<td><textarea rows="1" class="box-textarea" id="user_remark"></textarea></td>
		</tr>';
	$html .= '<tr class="footer-tr">
				<td colspan="5" align="center" class="showall">
					<div class="d-flex align-items-center justify-content-end gap-2">
						<span class="showhidehis btn btn-secondary">Xem lịch sử</span>
						<input id="done_btn" type="button" value="OK" class="btn btn-primary">
					</div>
					<input type="hidden" id="mark_type" value="' . $_POST['type'] . '">
					<input type="hidden" id="assigned_user" value="' . $_POST['employee'] . '">
					<input type="hidden" id="line" value="' . $_POST['line'] . '">
				</td>
			</tr>';
	echo $html;
}

// chấm điểm tác phong, ý thức, hiệu quả
if (isset($_POST['for']) && $_POST['for'] == 'saveMark') {
	if ($_POST['mark'] != '') {
		$sql = 'UPDATE ec_working_process SET deleted = 1 
					WHERE deleted = 0 AND parent_type = "' . $_POST['type'] . '" 
					AND DATE(date_entered) = "' . date('Y-m-d', strtotime($_POST['mark_date'])) . '"
					AND assigned_user_id = "' . $_POST['assigned_user'] . '"';

		$db->query($sql);
		$wp 		= new EC_Working_Process;
		$type 	= $_POST['type']; //manner, effected, awareness, minus

		$wp->name 				= $_POST['type'];
		$wp->assigned_user_id 		= $_POST['assigned_user'];
		$wp->parent_type 			= $_POST['type']; //manner, 
		$wp->$type				= $_POST['mark']; //7, 8, 9, 10đ
		$wp->description 			= $_POST['remark'];

		$wpid = $wp->save();

		// thay đổi ngày h tạo
		$sql_upt = 'UPDATE ec_working_process SET date_entered = "' . date('Y-m-d', strtotime($_POST['mark_date'])) . '" WHERE id = "' . $wpid . '"';
		$db->query($sql_upt);
	} else {
		$result = 'rejected';
	}

	echo $result;
}

// tìm lịch sử chấm điểm 
if (isset($_POST['for']) && $_POST['for'] == 'findingHistory') {

	$sql_type = ' AND wp.parent_type = "' . $_POST['type'] . '"';

	if ($_POST['type'] == 'effected') {
		$sql_type = ' AND (wp.parent_type = "' . $_POST['type'] . '" OR wp.ticket_delivery = 1)';
	}

	$sql = 'SELECT wp.created_by
					, DATE_FORMAT(DATE_ADD(wp.date_modified, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_mark
					, CONCAT_WS(" ", u.last_name, u.first_name) AS mark_person
					, IF(wp.parent_type = "EC_Flight_Bookings", wp.ticket_delivery, wp.' . $_POST['type'] . ') AS mark
					, IF(wp.parent_type = "EC_Flight_Bookings", CONCAT("Giao vé booking ", wp.name), wp.description) AS description
				FROM ec_working_process wp
				LEFT JOIN users u ON u.id = wp.created_by AND u.deleted = 0
				WHERE DATE(DATE_ADD(wp.date_entered, INTERVAL 7 HOUR)) = "' . date('Y-m-d', strtotime($_POST['date'])) . '"
				AND wp.assigned_user_id = "' . $_POST['assigned_user'] . '"
				' . $sql_type . '
				ORDER BY wp.date_modified';

	// echo $sql; exit;

	$res 	= $db->query($sql);
	$html 	= '<table width="100%" class="table-findingHistory mt-3" cellpadding="0" cellspacing="0"><thead><th width="5%">STT</th><th width="20%">Ngày chấm</th><th width="15%">Số điểm</th><th width="40%">Lý do</th><th width="20%">Người chấm</th></thead><tbody>';
	$i 		= 0;

	while ($row = $db->fetchByAssoc($res)) {
		$html .= '<tr>
				<td align="center">' . ++$i . '</td>
				<td align="center">' . $row['date_mark'] . '</td>
				<td align="center">' . $row['mark'] . '</td>
				<td align="center">' . $row['description'] . '</td>
				<td align="center">' . $row['mark_person'] . '</td>
			</tr>';
	}
	$html .= '</tbody></table>';

	echo $html;
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
		if (trim($row['airline_code']) == 'VNA') $selected1 = 'selected';
		else $selected2 = 'selected';
		$booking_airline = '
				<select name="bk_airline">
					<option value="VNA" ' . $selected1 . '>VNA</option>
					<option value="VNP" ' . $selected2 . '>VNP</option>
				</select>';
	} else $booking_airline = $row['airline_code'];

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
	// $html .= '<input type="hidden" name="applied_pass_name" value="' . $row['applied_pass_name'] . '">';
	$html .= '</div>';
	echo $html;
}

// Insert passenger line info
if (isset($_POST['for']) && $_POST['for'] == 'getPassengerLine') {
	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['booking']);

	// lấy thông tin hành trình cũ -> để lấy hạng vé
	// if ($_POST['type'] == 'edit') {
	// 	$sql_con_t = ' 
	// 			AND (add_type IS NULL OR assigned_user_id = "' . $_POST['pass_id'] . '")
	// 		';
	// } else {
	// 	$sql_con_t = ' AND add_type IS NULL';
	// }
	if ($_POST['type'] == 'edit') {
		$sql_con_t = ' 
				AND (add_type = 0 OR assigned_user_id = "' . $_POST['pass_id'] . '")
			';
	} else {
		$sql_con_t = ' AND add_type = 0';
	}

	$sql_t = '
			SELECT GROUP_CONCAT(iti_id_ob SEPARATOR "") AS iti_id_ob
				 , GROUP_CONCAT(iti_ticket_class_ob SEPARATOR "") AS iti_ticket_class_ob
				 , GROUP_CONCAT(iti_id_ib SEPARATOR "") AS iti_id_ib
				 , GROUP_CONCAT(iti_ticket_class_ib SEPARATOR "") AS iti_ticket_class_ib
			FROM (
				SELECT
					IF(direction = 0, id, "") AS iti_id_ob 
					, IF(direction = 0, ticket_class, "") AS iti_ticket_class_ob
					, IF(direction = 1, id, "") AS iti_id_ib
					, IF(direction = 1, ticket_class, "") AS iti_ticket_class_ib
					, booking_id, MAX(sabre_logs)
				FROM ec_booking_itineraries 
				WHERE deleted = 0 AND booking_id = "' . $booking->id . '" 
				' . $sql_con_t . '
				GROUP BY direction
			) AS t
			GROUP BY booking_id';
	$res_t = $db->query($sql_t);
	$row_t = $db->fetchByAssoc($res_t);

	// lấy hạng vé lượt đi
	if (!empty($_POST['ticket_class_ob'])) {
		$ticket_class_ob = $_POST['ticket_class_ob'];
	} else {
		$ticket_class_ob = $row_t['iti_ticket_class_ob'];
	}

	// nếu 2 chiều, lấy thêm hạng vé lượt về
	if (empty($booking->flight_type)) {
		if (isset($_POST['ticket_class_ib']) && !empty($_POST['ticket_class_ib'])) {
			$ticket_class_ib = $_POST['ticket_class_ib'];
		} else {
			$ticket_class_ib = $row_t['iti_ticket_class_ib'];
		}
	}

	// lấy thông tin nhà cung cấp hành lý
	$sql_supplier = "
			SELECT id, name FROM accounts 
			WHERE deleted = 0 AND account_type = 'Supplier' 
			AND is_stop_tracking = 0 
			ORDER BY ticker_symbol
		";
	$res_supplier = $db->query($sql_supplier);
	$supplier = array('' => '--Trống--');
	while ($row_supplier = $db->fetchByAssoc($res_supplier)) {
		$supplier[$row_supplier['id']] = $row_supplier['name'];
	}

	if (empty($_POST['pass_id'])) {
		$sql_con = ' 
				AND booking_id = "' . $_POST['booking'] . '" 
				AND id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers 
					WHERE deleted = 0 AND add_type = 2
					AND booking_id = "' . $_POST['booking'] . '" 
				)';
	} else if ($_POST['type'] == 'edit') {
		$sql_con = ' AND id = "' . $_POST['pass_id'] . '"';
	} else {
		$passenger_arr = explode(',', $_POST['pass_id']);
		$sql_con = ' AND id IN ("' . implode('","', $passenger_arr) . '")';
	}
	$sql = 'SELECT * FROM ec_booking_passengers 
				WHERE deleted = 0' . $sql_con;
	$res = $db->query($sql);

	if ($_POST['type'] != 'insert') {
		$html = '<div class="line_pass d-flex flex-column gap-2 p-2 border border-radius mt-3">
						<h2 class="change-title">Thông tin hành khách đã chọn:</h2>
						<table id="passenger_tbl" class="table-change-passengers" cellpadding="0" cellspacing="0"><tbody>';
	} else {
		$html = "";
	}
	$i = 0;

	while ($row = $db->fetchByAssoc($res)) {
		// thông tin lượt về nếu có
		if (empty($booking->flight_type)) {
			// số vé lượt về
			$eticket_inbound = '
					<td width="20%" class="text-label text-nowrap">Số vé lượt về:</td>
					<td><input class="box-input" type="text" value="' . $row['eticket_inbound'] . '" name="pass_eticket_inbound[]"></td>';

			// pnr lượt về
			$pnr_inbound = '
					<td width="20%" class="text-label text-nowrap">PNR lượt về:</td>
					<td><input class="box-input" type="text" value="' . $row['pnr_inbound'] . '" name="pass_pnr_inbound[]"></td>';

			// đánh dấu hành lý của VJ thì lưu cách khác
			// lượt về
			if (
				$booking->airline_inbound == 'VJ' || $booking->airline_inbound == 'VJA'
			) {
				$vj_luggage_inbound = '<input type="hidden" id="pass_luggage_ib_ind' . $i . '" name="pass_luggage_ib_ind[]" value="1" />';
			} else $vj_luggage_inbound = '';

			// thêm hành lý lượt về
			if (is_null($row['luggage_index_inbound'])) {
				$luggage_price_inb = (int)$row['luggage_price_inbound'];
			} else {
				$luggage_price_inb = (int)$row['luggage_index_inbound'];
			}
			$luggage_inbound = '
					<td width="20%" class="text-label text-nowrap">Thêm HL lượt về:</td>
					<td class="pass_luggage_ln pass_luggage_right">
						<select class="pass_luggage pass_luggage_ib box-select" name="pass_luggage_ib[]" ln="' . $i . '">' . generateLuggage($booking->date_entered, $booking->airline_inbound, $ticket_class_ib, $row['type'], (int)$row['luggage_index_inbound'], 1, (int)$row['luggage_price_inbound']) . '</select>
						' . $vj_luggage_inbound . '
					</td>';

			// giá mua hành lý lượt về
			$bought_price_inbound = '
					<td width="20%" class="text-label text-nowrap">Giá mua HL lượt về:</td>
					<td><input type="text" value="' . $row['luggage_purchase_inbound'] . '" name="bought_price_inbound[]" class="box-input allow-number-only"></td>';

			// nhà cung cấp lượt về
			$supplier_inbound = '
					<td width="20%"><label class="text-label text-nowrap">NCC HL lượt về:</label></td>
					<td class="supplier_line supplier_line_right">
						<select class="box-select" name="supplier_inbound[]">' . get_select_options_with_id($supplier, trim($row['supplier_inbound_id'])) . '</select>
						<input type="hidden" name="iti_ib" value="' . $row_t['iti_id_ib'] . '">
					</td>';
		} else {
			$eticket_inbound 		= '<td></td><td></td>';
			$pnr_inbound 			= '<td></td><td></td>';
			$luggage_inbound 		= '<td></td><td></td>';
			$bought_price_inbound 	= '<td></td><td></td>';
			$supplier_inbound 		= '<td></td><td></td>';
		}

		$html .= '<tr class="line_pass' . $row['id'] . '">
						<td colspan="4">
							<b class="pass_order">Hành khách ' . ($i + 1) . ':</b> ' . $app_list_strings['passenger_type_list'][$row['type']] . ', 
							<div class="d-flex align-items-center gap-2 mt-1">
								<select class="box-select" name="pass_salutation[]">' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int)$row['salutation']) . '</select>
								<input class="box-input" type="text" value="' . $row['name'] . '" name="pass_name[]">
								<input class="box-input pass_birthday" type="text" value="' . (!empty($row['birthday']) ? date('d-m-Y', strtotime($row['birthday'])) : '') . '" name="pass_birthday">
								<input type="hidden" name="pass_id[]" value="' . $row['id'] . '">
							</div>
						</td>
					</tr>';

		// số vé lượt đi
		$html .= '<tr class="line_pass' . $row['id'] . '">
				<td class="text-label text-nowrap" width="20%">Số vé lượt đi:</td>
				<td><input class="box-input" type="text" value="' . $row['eticket_outbound'] . '" name="pass_eticket_outbound[]"></td>
				' . $eticket_inbound . '
			</tr>';
		$html .= '<tr class="line_pass' . $row['id'] . '">
				<td class="text-label text-nowrap" width="20%">PNR lượt đi:</td>
				<td><input class="box-input" type="text" value="' . $row['pnr_outbound'] . '" name="pass_pnr_outbound[]"></td>
				' . $pnr_inbound . '
			</tr>';

		// đánh dấu hành lý của VJ thì lưu cách khác
		// lượt đi
		if ($booking->airline == 'VJ' || $booking->airline == 'VJA') {
			$vj_luggage_outbound = '<input type="hidden" id="pass_luggage_ob_ind' . $i . '" name="pass_luggage_ob_ind[]" value="1" />';
		} else $vj_luggage_outbound = '';
		if (is_null($row['luggage_index_outbound'])) {
			$luggage_price = (int)$row['luggage_price'];
		} else {
			$luggage_price = (int)$row['luggage_index_outbound'];
		}
		$html .= '
			<tr class="line_pass' . $row['id'] . '">
				<td width="20%" class="text-label text-nowrap">Thêm HL lượt đi:</td>
				<td class="pass_luggage_ln pass_luggage_left">
					<select class="pass_luggage pass_luggage_ob box-select" name="pass_luggage_ob[]" ln="' . $i . '">' . generateLuggage($booking->date_entered, $booking->airline, $ticket_class_ob, $row['type'], (int)$row['luggage_index_outbound'], 1, (int)$row['luggage_price']) . '</select>
					' . $vj_luggage_outbound . '
				</td>
				' . $luggage_inbound . '
			</tr>';
		// giá mua lượt đi
		$html .= '
			<tr class="line_pass' . $row['id'] . '">
				<td width="20%" class="text-label text-nowrap">Giá mua HL lượt đi:</td>
				<td><input type="text" value="' . $row['luggage_purchase'] . '" name="bought_price_outbound[]" class="box-input allow-number-only"></td>
				' . $bought_price_inbound . '
			</tr>';
		$html .= '
			<tr class="line_pass' . $row['id'] . '">
				<td width="20%" class="text-label text-nowrap">NCC HL lượt đi:</td>
				<td class="supplier_line supplier_line_left">
					<select class="box-select" name="supplier_outbound[]">' . get_select_options_with_id($supplier, trim($row['supplier_id'])) . '</select>
					<input type="hidden" name="iti_ob" value="' . $row_t['iti_id_ob'] . '">
				</td>
				' . $supplier_inbound . '
			</tr>';

		$html .= "<input type='hidden' name='pass_type[]' value='" . $row['type'] . "'>";
		$html .= "<input type='hidden' name='pass_ticket_class_ob[]' value='" . $ticket_class_ob . "'>";
		$html .= "<input type='hidden' name='pass_ticket_class_ib[]' value='" . (isset($ticket_class_ib) ? $ticket_class_ib : '') . "'>";
		$i++;
	}
	if ($_POST['type'] != 'insert') {
		$html .= '</tbody></table></div>';
	}

	// thêm dòng id để biết mà edit
	if ($_POST['type'] == 'edit') {
		$html .= '<input type="hidden" name="edit_pass_id">';
	}

	$html .= "<input type='hidden' name='pass_airline_ob' value='" . $booking->airline . "'>";
	$html .= "<input type='hidden' name='pass_airline_ib' value='" . $booking->airline_inbound . "'>";
	$html .= "<input type='hidden' name='bk_date_entered' value='" . $booking->date_entered . "'>";
	echo $html;
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
	// $pass_detail = populateLinePassengers($_POST['id'], $_POST['flight_type'], '0', $_POST['airline_in'], $_POST['airline_out'], $_POST['ticket_class0'], $_POST['ticket_class1']);
	// echo json_encode(array('line_html' => $line_detail, 'pass_html' => $pass_detail));
	echo json_encode(array('line_html' => $line_detail));
}

// thêm hành lý
if (isset($_POST['for']) && $_POST['for'] == 'addLuggage') {
	echo populateLinePassengers($_POST['id'], $_POST['flight_type'], '1', $_POST['airline_in'], $_POST['airline_out'], $_POST['ticket_class0'], $_POST['ticket_class1'], $_POST['contact_name'], $_POST['contact_phone']);
}

// đổi tên
if (isset($_POST['for']) && $_POST['for'] == 'changeName') {
	$pass_detail = populateLinePassengers($_POST['id'], $_POST['flight_type'], '2', $_POST['airline_in'], $_POST['airline_out'], $_POST['ticket_class0'], $_POST['ticket_class1']);
	echo $pass_detail;
}

// đổi ngày h bay dùng cho edit
if (isset($_POST['for']) && $_POST['for'] == 'changeFlightTime') {
	$iti_detail = populateLineItineraries($_POST['id'], 3);
	echo $iti_detail;
}

// hiện thông tin ngày h bay / hành trình sau khi thay đổi (nếu có)
if (isset($_POST['for']) && $_POST['for'] == 'showEditedFlightTime' && isset($_POST['id'])) {

	$edited_iti_detail = populateEditedLineItineraries($_POST['id']);
	echo $edited_iti_detail;
}

// hiện thông tin hành khách sau khi thay đổi (nếu có)
if (isset($_POST['for']) && $_POST['for'] == 'showChangedPassenger' && isset($_POST['id'])) {
	$edited_pass_detail = populateEditedLinePassenger($_POST['id']);
	echo $edited_pass_detail;
}

// lấy thông tin yêu cầu xuất hoá đơn
if (isset($_POST['for']) && $_POST['for'] == 'getInvoiceInf') {

	$booking = new EC_Flight_Bookings;
	$booking->retrieve($_POST['booking']);

	$inv_inf = json_decode(str_replace("&quot;", "\"", $booking->shipping_address), 1);

	$iv_payment_method = array('' => '', 'Tiền mặt' => 'Tiền mặt', 'Chuyển khoản' => 'Chuyển khoản', 'Tiền mặt hoặc Chuyển khoản' => 'Tiền mặt hoặc Chuyển khoản');
	$iv_name_banks = array(
		'' => 'Chọn ngân hàng',
		'VPBank' => '(VPBank) NH TMCP Việt Nam Thịnh Vượng',
		'BIDV' => '(BIDV) NH TMCP Đầu tư và Phát triển Việt Nam',
		'VietinBank' => '(VietinBank) NH TMCP Công thương Việt Nam',
		'Vietcombank' => '(Vietcombank) NH TMCP Ngoại Thương Việt Nam',
		'MB' => '(MB) NH TMCP Quân Đội',
		'Techcombank' => '(Techcombank) NH TMCP Kỹ Thương',
		'Agribank' => '(Agribank) NH PT Nông thôn Việt Nam',
		'ACB' => '(ACB) NH TMCP Á Châu',
		'SHB' => '(SHB) NH TMCP Sài Gòn – Hà Nội',
		'VIB' => '(VIB) NH TMCP Quốc Tế',
		'HDBank' => '(HDBank) NH TMCP Phát triển TPHCM',
		'SeABank' => '(SeABank) NH TMCP Đông Nam Á',
		'VBSP' => '(VBSP) NH Chính sách xã hội Việt Nam',
		'Sacombank' => '(Sacombank) NH TMCP Sài Gòn Thương Tín',
		'LienVietPostBank' => '(LienVietPostBank) NH TMCP Bưu điện Liên Việt',
		'MSB' => '(MSB) NH TMCP Hàng Hải',
		'SCB' => '(SCB) NH TMCP Sài Gòn',
		'VDB' => '(VDB) NH Phát triển Việt Nam',
		'OCB' => '(OCB) NH TMCP Phương Đông',
		'Eximbank' => '(Eximbank) NH TMCP Xuất Nhập Khẩu',
		'TPBank' => '(TPBank) NH TMCP Tiên Phong',
		'PVcomBank' => '(PVcomBank)  NH TMCP Đại Chúng Việt Nam',
		'BacABank' => '(BacABank) NH TMCP Bắc Á',
		'Woori' => '(Woori) NH TNHH MTV Woori Việt Nam',
		'HSBC' => '(HSBC) NH TNHH MTV HSBC Việt Nam',
		'VietABank' => '(Vietbank) NH TMCP Việt Nam Thương Tín',
		'NamABank' => '(Nam A Bank) NH TMCP Nam Á',
		'IVB' => '(IVB) NH TNHH Indovina',
		'Kienlongbank' => '(Kienlongbank) NH TMCP Kiên Long',
	);

	// $html = '<h2 style="display: inline-block; padding: 5px 0; border-bottom: 1px solid #cbdae6;">Thông tin yêu cầu xuất hoá đơn:</h2>';
	$html = '<table class="table-config table-request-invoice" cellpadding="0" cellspacing="0"><tbody>';
	$html .= '
		<tr>
			<td width="30%" class="label">Họ tên KH:</td>
			<td width="70%">
				<input type="text" class="box-input" name="iv_account_name" value="' . $inv_inf['iv_account_name'] . '">
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Tên công ty:</td>
			<td>
				<textarea type="text" class="box-textarea" name="company_name" rows="4">' . $booking->company_name . '</textarea>
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Mã số thuế:</td>
			<td>
				<input type="text" class="box-input" name="tax_code" value="' . $booking->tax_code . '">
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Email:</td>
			<td>
				<input type="text" class="box-input" name="iv_email" value="' . $inv_inf['iv_email'] . '">
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Địa chỉ</td>
			<td>
				<textarea type="text" class="box-textarea" name="company_address" rows="5">' . $booking->company_address . '</textarea>
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Phương thức TT:</td>
			<td>
				<select name="iv_payment_method" id="iv_payment_method" class="box-select">
				' . get_select_options_with_id($iv_payment_method, mb_convert_encoding(trim($inv_inf['iv_payment_method']), 'UTF-8', 'HTML-ENTITIES')) . '
				</select>
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Ngân hàng:</td>
			<td>
				<select name="iv_name_banks" id="iv_name_banks" class="box-select w-100">
				' . get_select_options_with_id($iv_name_banks, mb_convert_encoding(trim($inv_inf['iv_name_banks']), 'UTF-8', 'HTML-ENTITIES')) . '
				</select>
			</td>
		</tr>';
	$html .= '
		<tr>
			<td class="label">Số tài khoản:</td>
			<td>
				<input type="text" class="box-input" name="iv_bank_account" value="' . $inv_inf['iv_bank_account'] . '">
			</td>
		</tr>';
	$html .= '</tbody></table>';
	echo $html;
}

// tạo mới / cập nhật trạng thái của 1 user 
if (isset($_POST['for']) && $_POST['for'] == 'updateUsrStt') {
	// chỉ cập nhật cho user là người dùng thông thường
	if (!is_admin($current_user)) {
		// kiếm tra đã có online hôm nay chưa
		$sql_exist = '
			SELECT id, IFNULL((
				SELECT MAX(round)
				FROM ec_online_report
				WHERE deleted = 0
				AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
			), 1) AS round 
			FROM ec_online_report
			WHERE deleted = 0
			AND assigned_user_id = "' . $current_user->id . '"
			AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"
		';

		$res_exist 	= $db->query($sql_exist);
		$row_exist 	= $db->fetchByAssoc($res_exist);
		$online 		= new EC_Online_Report;

		if (!empty($row_exist['id'])) {
			$online->retrieve($row_exist['id']);

			$online->status 	= $_POST['stt'];
			$online->round 	= $row_exist['round'];
			$online->save();
			// mới bắt đầu online, lưu thêm thời gian bắt đầu online
			// để lưu s lấy theo ngày chỉnh sửa

			if (strtotime($online->start_online) === false) {
				$sql = '
					UPDATE ec_online_report
					SET start_online = "' . date('Y-m-d H:i:s', strtotime($online->date_modified)) . '"
					WHERE id = "' . $online->id . '"
				';
				$db->query($sql);
			}
		}
	} else {
		echo 'is_admin';
	}
}

// nhắc nhở lên group nếu thay đổi trạng thái nhiều lần trong thời gian quy định (5 phút làm liên tục quá 5 lần)
// if (isset($_POST['for']) && $_POST['for'] == 'reportToGroup') {
// 	if (isset($_POST['type']) && $_POST['type'] == 'repeatChangeStt') {
// 		$post_fields = array(
// 			'bot_id' => 'bot706494755',
// 			'api_key' => 'AAHpTyV2fo8Jp_r0gCjrvskLyfed-ISKjb4',
// 			'chat_id' => '-1001311652274',
// 			'text' => '<b>' . trim($current_user->last_name) . ' ' . trim($current_user->first_name) . '</b> vui lòng không thay đổi trạng thái liên tục',
// 		);
// 		myTelegramSendMessage(json_encode($post_fields));
// 	}
// }

// thay đổi vị trí trong bảng online
if (isset($_POST['for']) && $_POST['for'] == 'changeOnlinePosition') {
	$onl = new EC_Online_Report;
	$onl_res = $onl->changeOnlinePosition($_POST['onl'], $_POST['type']);

	if (isset($_POST['agent']) && !empty($_POST['agent'])) {
		agent_change_status($_POST['agent'], 'Logged Out');
	}

	echo $onl_res;
}

// Nhắc nhở khách hàng lịch bay - button Remind
if (isset($_POST['for']) && $_POST['for'] == 'remindFlightSchedules') {
	$journey_id 	 = (isset($_POST["journey_id"]) && !empty($_POST["journey_id"])) ? $_POST["journey_id"] : null;

	if (is_null($journey_id)) {
		echo 0;
		exit();
	}

	if (!empty($journey_id)) {
		// Update is_remind trong bảng ec_booking_itineraries = true
		$update_remind = "UPDATE ec_booking_itineraries 
		SET is_remind = 1
		WHERE id = '" . trim($journey_id) . "'";
		$db->query($update_remind);

		echo 1;
		exit();
	} else {
		echo 0;
		exit();
	}
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
					<th style="width:1%;" class="text-center fw-semibold">&nbsp;</th>
				  </tr></thead>';

	$i = 0;
	$total_qty_loop = 0;
	$total_price_loop = 0;
	$subtotal_amount_loop = 0;

	while ($row = $db->fetchByAssoc($res)) {

		$detail_id = $row['detail_id'];
		$html .= '<tr id="bk_edit_line_' . $i . '" class="bkd_line fw-semibold">';

		$html .= '<td><select name="bkd_direction[]" id="bk_edit_direction' . $i . '" >' . get_select_options_with_id($app_list_strings['bk_direction_list'], (int)$row['direction']) . '</select></td>';
		$html .= '<td><select class="text-start" name="bkd_passenger_type[]" id="bk_edit_passenger_type' . $i . '">' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int)$row['passenger_type']) . '</select></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_quantity[]" id="bk_edit_quantity' . $i . '" value="' . format_number($row['quantity']) . '" maxlength="3" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" onkeyup="calculateLineEditDetails(' . $i . ', 0, 1)" type="text" name="bkd_unit_price[]" id="bk_edit_unit_price' . $i . '" value="' . format_number($row['unit_price']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_tax_and_fee[]" id="bk_edit_tax_and_fee' . $i . '" value="' . format_number($row['tax_and_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_airport_fee[]" id="bk_edit_airport_fee' . $i . '" value="' . format_number($row['airport_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ', 1)" type="text" name="bkd_admin_fee[]" id="bk_edit_admin_fee' . $i . '" value="' . format_number($row['admin_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_service_fee[]" id="bk_edit_service_fee' . $i . '" value="' . format_number($row['service_fee']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ')" type="text" name="bkd_total_price[]" id="bk_edit_total_price' . $i . '" value="' . format_number($row['total_price']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" type="text" name="bkd_total_bought_price[]" id="bk_edit_total_bought_price' . $i . '" value="' . ((float)$row['total_bought_price'] ? format_number($row['total_bought_price']) : format_number($row['total_price'] - ($row['service_fee']) * $row['quantity'])) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" type="text" name="bkd_supplier_discount[]" id="bk_edit_supplier_discount' . $i . '" value="' . format_number($row['supplier_discount']) . '" maxlength="25" /></td>';
		$html .= '<td><input class="allow-number-only text-center" onblur="calculateLineEditDetails(' . $i . ');" id="bk_edit_supplier_ticketing_fee' . $i . '" type="text" name="bkd_supplier_ticketing_fee[]" value="' . (isset($row['fee_bought']) ? format_number($row['fee_bought']) : 0) . '" maxlength="25"></td>';
		$html .= '<td><select id="bk_edit_supplier_id' . $i . '" name="bkd_supplier_id[]"><option value=""></option>' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $supplier_cus_sql) . '</select></td>';
		$html .= '<td class="align-middle text-center">
					<input type="hidden" value="0" name="bkd_deleted[]" id="bk_edit_deleted' . $i . '" />
					<input type="hidden" name="bkd_detail_id[]" id="bk_edit_detail_id' . $i . '" value="' . $detail_id . '" />
				</td>';

		// Thêm dòng phí admin chưa VAT
		$html .= '<tr id="bk_edit_admin_line_' . $i . '">';
		$html .= '<td colspan="14">
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
		$subtotal_amount_loop += ((float)$row['total_bought_price'] ? $row['total_bought_price'] : ($row['total_price'] - ($row['service_fee']) * $row['quantity']));
		$i++;
	}

	$total_qty 			= isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : $total_qty_loop;
	$subtotal_amount 		= isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : $total_price_loop;
	$total_bought_amount 	= isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : $subtotal_amount_loop;
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
			<td colspan="4">
				<input type="hidden" name="total_amount" id="bk_edit_total_amount" size="30" maxlength="26" title="" tabindex="0"  value="0" class="allow-number-only">
			</td>
		</tr>';

	$html1 = '<script>calculateTotal();</script>';

	return $html . $html1;
}

function populateLinePassengers($booking_id, $flight_type, $type, $airline_in = '', $airline_out = '', $ticket_class_out = '', $ticket_class_in = '', $contact_name = '', $contact_phone = '')
{
	global $app_list_strings, $timedate, $db;
	$date_format = $timedate->get_date_format();
	$sql_supplier = " AND account_type='Supplier' AND is_stop_tracking=0 ";

	$sql = "SELECT p.id AS detail_id 
					  ,p.type
					  ,p.salutation
					  ,p.name
					  ,p.birthday
					  ,p.eticket_outbound
					  ,p.eticket_inbound
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

		if ($airline_out == 'BBA' && !empty($ticket_class_out) && $row['type'] != '2') {
			$ob_ticket_class = '_' . strtolower($ticket_class_out);
		} else if (($airline_out == 'VNA' || $airline_out == 'BBA') && $row['type'] == '2') {
			$ob_ticket_class = '_infant';
		} else {
			$ob_ticket_class = '';
		}

		if ($airline_in == 'BBA' && !empty($ticket_class_in) && $row['type'] != '2') {
			$ib_ticket_class = '_' . strtolower($ticket_class_in);
		} else if (($airline_in == 'VNA' || $airline_in == 'BBA') && $row['type'] == '2') {
			$ib_ticket_class = '_infant';
		} else {
			$ib_ticket_class = '';
		}

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
		if ((empty($row['eticket_outbound']) || ACLController::checkAccess("Bugs", "edit", true)) && $type == '0') {
			$html .= '<td class="text-center"><input class="text-center box-input detail_ticket_input" type="text" maxlength="25" name="psg_eticket_outbound[]" id="psg_eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . $row['eticket_outbound'] . '<input type="hidden" id="psg_eticket_outbound' . $i . '" name="psg_eticket_outbound[]" value="' . strtoupper($row['eticket_outbound']) . '"></td>';
		}

		if ((empty($row['eticket_inbound']) || ACLController::checkAccess("Bugs", "edit", true)) && $flight_type == '0' && $type == '0') {
			$html .= '<td><input class="box-input text-center" type="text" maxlength="25" name="psg_eticket_inbound[]" id="psg_eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '" /></td>';
		} else {
			$html .= '<td class="text-center">' . strtoupper($row['eticket_inbound']) . '<input type="hidden" id="psg_eticket_inbound' . $i . '" name="psg_eticket_inbound[]" value="' . strtoupper($row['eticket_inbound']) . '"></td>';
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


		// if($type == '1') {
		// 	// line 2
		// 	// $html .= '<tr id="psg_line_desc_'.$i.'">';
		// 	// $html .= '<td colspan="12" style="border-bottom:1px dashed #8D8D8D; padding:3px 0px;">
		// 	// 	Giá mua lượt đi: <input class="allow-number-only" style="width:150px; text-align:right;" type="text" maxlength="25" name="psg_luggage_purchase[]" id="psg_luggage_purchase'.$i.'" value="'.format_number($row['luggage_purchase']).'" />
		// 	// 	NCC lượt đi: <select style="width: 150px; font-family: monospace;" name="psg_luggage_supplier[]" id="psg_luggage_supplier'.$i.'" ><option value=""></option>'.myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $sql_supplier).'</select>';
		// 	// if($flight_type == 0) {
		// 	// 	$html .= ' -
		// 	// 		Giá mua lượt về: <input class="allow-number-only" style="width:150px; text-align:right;" type="text" maxlength="25" name="psg_luggage_purchase_inbound[]" id="psg_luggage_purchase_inbound'.$i.'" value="'.format_number($row['luggage_purchase_inbound']).'" />
		// 	// 		NCC lượt về: <select style="width: 150px; font-family: monospace;" name="psg_luggage_supplier_inbound[]" id="psg_luggage_supplier_inbound'.$i.'" ><option value=""></option>'.myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_inbound_id'], 'id', $sql_supplier).'</select>
		// 	// 	</td>';
		// 	// }
		// } else {
		// 	$html .= '<input type="hidden" name="psg_luggage_purchase[]" value="'.format_number($row['luggage_purchase']).'"><input type="hidden" name="psg_luggage_supplier[]" value="'.$row['supplier_id'].'"><input type="hidden" name="psg_luggage_purchase_inbound[]" value="'.format_number($row['luggage_purchase_inbound']).'"><input type="hidden" name="psg_luggage_supplier_inbound[]" value="'.$row['supplier_inbound_id'].'">';
		// }

		$html .= '</tr>';

		$i++;
	} // while

	$html .= '</table>';

	// line 3
	// thông tin người nộp tiền trên phiếu thu 
	if ($type == '1') {
		// $department_id = $GLOBALS['current_user']->department_id;
		// $html .= '<div style="padding:10px 0; font-weight:bold;">Thông tin người nộp tiền trên phiếu thu: </div>
		// <table width="100%" cellpadding="0" cellspacing="0">
		// 	<tbody>
		// 		<tr>
		// 			<td>Người nộp: <input type="text" id="receipt_contact_name" name="contact_name" value="'.$contact_name.'"></td>
		// 			<td>Điện thoại: <input type="text" id="receipt_contact_phone" name="contact_phone" value="'.$contact_phone.'"></td>
		// 			<td>Hình thức: <select id="receipt_type" name="receipt_type">'.get_select_options_with_id($app_list_strings['receipt_type_list'], '').'</select>&nbsp;<select style="font-family:monospace; font-size:14px; width:150px; display:none;" id="tknganhang_id" name="tknganhang_id"><option value=""></option>'.myGetBankAccountList('', $tknganhang_group).'</select>&nbsp;<select id="com_location_id" name="com_location_id">'.myGetLocationListByDepID($department_id, '').'</select></td>
		// 		</tr>
		// 	</tbody>
		// </table>
		// <div style="padding:10px 0; font-weight:bold;">Lưu ý: Khi bạn thêm hành lý, phiếu thu sẽ được tạo tự động. Vui lòng kiểm tra phiếu thu đã được tạo sau khi lưu.</div>';
	}
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
		if (trim($booking->airline) == 'VNA') $selected1 = 'selected';
		else $selected2 = 'selected';
		$booking_airline = '
					<select class="box-select" name="bk_airline0">
						<option value="VNA" ' . $selected1 . '>VNA</option>
						<option value="VNP" ' . $selected2 . '>VNP</option>
					</select>';
	} else $booking_airline = $booking->airline;

	if (trim($booking->airline_inbound) == 'VNA' || trim($booking->airline_inbound) == 'VNP') {
		if (trim($booking->airline_inbound) == 'VNA') {
			$selected1 = 'selected';
			$selected2 = '';
		} else $selected2 = 'selected';
		$booking_airline_inbound = '
					<select class="box-select" name="bk_airline1">
						<option value="VNA" ' . $selected1 . '>VNA</option>
						<option value="VNP" ' . $selected2 . '>VNP</option>
					</select>';
	} else $booking_airline_inbound = $booking->airline_inbound;

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

function populateEditedLineItineraries($booking_id)
{
	global $app_list_strings, $timedate, $db;
	$date_format 	= $timedate->get_date_format(); // d-m-Y
	$user_list 	= get_user_array(true, '', '', true);
	$airport_list 	= $app_list_strings['domestic_airport_list'] + $app_list_strings['africa_airport_list'] + $app_list_strings['americas_airport_list'] + $app_list_strings['australia_airport_list'] + $app_list_strings['europe_airport_list'] + $app_list_strings['northeast_asia_airport_list'] + $app_list_strings['southeast_asia_airport_list'];


	$booking = new EC_Flight_Bookings;
	$booking->retrieve($booking_id);

	// lấy sl hành khách trong booking
	$sql_qty = 'SELECT COUNT(id)
					FROM ec_booking_passengers
					WHERE deleted = 0 AND add_type IS NULL
					AND booking_id = "' . $booking_id . '"';
	$pass_qty = $db->getOne($sql_qty);

	$html = '';
	$sql  = "
			SELECT GROUP_CONCAT(iti.id) AS iti_id
				,iti.id
				,iti.name
				,iti.description
				,iti.airline_code
				,iti.flight_number
				,iti.ticket_class
				,iti.departure
				,iti.arrival
				,iti.departure_date
				,iti.arrival_date
				,iti.base_price
				,iti.total_price
				,iti.direction
				,iti.time_limit
				,iti.is_layover
				,iti.is_remind
				,bk.ticket_type
				,bk.phone as bk_phone
				,bk.name as bk_name
				,GROUP_CONCAT(TRIM(iti.name)) AS pass_name
				,GROUP_CONCAT(
					IF(iti.assigned_user_id IN (
						SELECT assigned_user_id FROM ec_booking_itineraries
						WHERE booking_id = '" . $booking_id . "' 
						AND deleted = 0 AND add_type = 3
						AND sabre_logs > iti.sabre_logs
					), NULL, iti.assigned_user_id)
				) AS applied_pass
				,iti.sabre_logs
				,iti.modified_user_id
				FROM ec_booking_itineraries iti
				LEFT JOIN ec_flight_bookings bk ON bk.id = iti.booking_id
				WHERE iti.booking_id = '" . $booking_id . "'
				AND iti.add_type = 3
				AND iti.deleted = 0
				GROUP BY iti.direction, iti.flight_number, iti.departure, iti.arrival, iti.departure_date, iti.sabre_logs
				ORDER BY iti.sabre_logs, iti.date_entered, iti.direction";

	// truy vấn SQL đến db.
	$res = $db->query($sql);

	// return thì j = 3, oneway chiều thì j = 2
	$i 	= 0;
	if ($booking->flight_type == 0) {
		$j = 3;
	} else $j = 2;

	$order_iti = 0;
	$print_iti = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$airline_code = $airline_code_logo = $row['airline_code'];
		$img_style = 'style="width:45px"';
		if ($row['airline_code'] == 'VNA') $airline_code = $airline_code_logo = 'VN';
		if ($row['airline_code'] == 'VJA') $airline_code = $airline_code_logo = 'VJ';
		if ($row['airline_code'] == 'VNP') {
			$airline_code = 'BL';
			$airline_code_logo = 'VNP';
		}
		if ($row['airline_code'] == 'BBA') $airline_code = $airline_code_logo = 'QH';
		if ($row['airline_code'] == 'VTA') {
			$airline_code = 'VU';
			$airline_code_logo = 'VTA';
			$img_style = 'style="width:55px"';
		}
		$img_src = $row['is_layover'] ? '' : '<img ' . $img_style . ' src="custom/themes/default/images/airline-icon-100x100/' . strtoupper($airline_code_logo) . '.png" alt="' . $airline_code . '" border="0" />';
		if ($row['ticket_type'] == '2') $img_src .= '<br />(<b>' . $row['airline_code'] . '</b>)';

		if ($order_iti != $row['sabre_logs']) {
			$order_iti = $row['sabre_logs'];
			$pass_name_arr = explode(',', $row['pass_name']);
			if (count($pass_name_arr) == $pass_qty) {
				if ($pass_qty <= 10) {
					$pass_dt = ' ( ' . implode(", ", $pass_name_arr) . ' )';
				} else {
					$pass_dt = '';
				}
				// $applied_pass = 'tất cả hành khách' . $pass_dt;
				$applied_pass = 'tất cả hành khách';
			} else {
				$applied_pass = implode(', ', $pass_name_arr);
			}

			// Tên các lần thay đổi ngày bay
			$html .= '<tr>
						<td colspan="14" class="bg-yellow">
							<b>
							Lần thay đổi thứ ' . $row['sabre_logs'] . ': Áp dụng cho ' . $applied_pass . '. 
							Thay đổi bởi: <b>' . $user_list[$row['modified_user_id']] . '
							</b>
						</td>
					</tr>';
			$i = 0;
		}

		$html .= '<tr class="edited_iti_line"> 
					<td class="hide-mobile"></td>
					<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td data-label="Chiều" class="text-center">' . $app_list_strings['bk_direction_list'][$row['direction']] . '</td>
					<td data-label="Mã hãng" class="text-center">' . $img_src . '</td>
					<td data-label="Số hiệu" class="text-center">' . $row['flight_number'] . '</td>
					<td data-label="Hạng vé" class="text-center ticket_class' . $row['direction'] . '">' . $row['ticket_class'] . '</td>
					<td data-label="Nơi đi" class="text-center">' . $row['departure'] . '</td>
					<td data-label="Nơi đến" class="text-center">' . ($row['is_layover'] ? '' : $row['arrival']) . '</td>
					<td data-label="Ngày giờ đi" class="text-center">' . (trim($row['departure_date']) != '' ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '') . '</td>
					<td data-label="Ngày giờ đến" class="text-center">' . (trim($row['arrival_date']) != '' ? date($date_format . ' H:i', strtotime($row['arrival_date'])) : '') . '</td>';

		// Nút in vé
		$print_ticket_btn = $send_ticket_btn = $remind_btn = '';
		if ($print_iti != $row['sabre_logs']) {
			$print_iti = $row['sabre_logs'];
			$print_ticket_btn = '<div class="d-flex align-items-center gap-2 justify-content-between"><input type="button" class="btn btn-primary-2 fw-semibold flex-fill" ln="' . $j . '" name="btnPrintEticket" value="In vé" title="In vé" /><input type="hidden" name="add_type" value="3">';
			$send_ticket_btn = '<input type="button" class="btn btn-primary-2 fw-semibold flex-fill" ln="' . $j . '" name="btnSendEticket" value="Gửi vé" title="Gửi vé" />';

			if ($row['is_remind'] == 0) {
				// $remind_btn .= '<input type="button" class="btn btn-primary-2 btn-remind btn-voiceip-calling" iti_id="' . $row['id'] . '" booking_id="'.$booking_id.'" booking_name="' . $row['bk_name'] . '" phone="' . $row['bk_phone'] . '" name="btnRemind" id="btnRemind" value="Remind" title="Send Remind" />';

				$remind_btn .= '<div class="dropdown">
					<button class="btn btn-primary-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
						Remind
					</button>
					<ul class="dropdown-menu dropdown-menu-end box-list">
						<li class="box-item">
							<a class="dropdown-item btn-remind btn-voiceip-calling" iti_id="' . $row['id'] . '" booking_id="' . $booking_id . '" booking_name="' . $row['bk_name'] . '" phone="' . $row['bk_phone'] . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay</a>
						</li>
						<li class="box-item">
							<a class="dropdown-item confirm-remind" iti_id="' . $row['id'] . '" booking_id="' . $booking_id . '" id="confirm-remind" href="javascript:void(0)">Đã nhắc nhở khách</a>
						</li>
					</ul>
				</div>';
			}
		}

		$sms_depdate = date('d/m/Y H:i', strtotime($row['departure_date']));
		$html .= '
				<td colspan="2" class="text-center">
					<form action="index.php?print=true" method="post" name="frmPrintEticket" id="frmPrintEticket' . $j . '" target="_blank">
						<input type="hidden" name="module" value="EC_Flight_Bookings" />
						<input type="hidden" name="action" value="printeticket" />
						<input type="hidden" name="record" value="' . $booking->id . '" />
						<input type="hidden" name="return_module" value="EC_Flight_Bookings" />
						<input type="hidden" name="return_action" value="" />
						<input type="hidden" name="return_id" value="' . $booking->id . '" />
						<input type="hidden" name="booking" value="' . $booking->name . '" />
						<input type="hidden" name="booking_id" value="' . $booking->id . '" />
						<input type="hidden" name="contact_email" value="' . $booking->email . '" />
						<input type="hidden" name="contact_name" value="' . $booking->contact_name . '" />
						<input type="hidden" name="itinerary_id" value="' . $row['id'] . '" />
						<input type="hidden" name="direction" value="' . $row['direction'] . '" />
						<input type="hidden" name="airline_code" value="' . $row['airline_code'] . '" />
						<input type="hidden" name="ticket_type" value="' . $booking->ticket_type . '" />
						' . $print_ticket_btn . '
						' . $send_ticket_btn . '
						<input class="btn btn-primary-2 fw-semibold flex-fill" type="button" 
							direction="' . $row['direction'] . '" 
							flightno="' . $row['flight_number'] . '" 
							journey="' . ucfirst(myRemoveUnicodeChars($airport_list[$row['departure']])) . ' - ' . ucfirst(myRemoveUnicodeChars($airport_list[$row['arrival']])) . '" 
							date="' . explode(' ', $sms_depdate)[0] . '" 
							time="' . explode(' ', $sms_depdate)[1] . '"
							applied_pass="' . $applied_pass . '" name="btnSendSMS" value="SMS" title="Send SMS" />
						' . $remind_btn . '
					</form>
				</td>';

		// Quá cảnh để trống
		$html .= '
				<td class="text-center p-2">
					<svg xmlns="http://www.w3.org/2000/svg" data-id="' . $row['iti_id'] . '" class="edit_iti_row cursor-pointer" width="24" height="24" viewBox="0 0 24 24" style="fill: #2a2a2a;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
				</td>';
		$html .= '</tr>';

		// Load description
		if (isset($row['description']) && !empty($row['description'])) {
			$html .= '<tr><td colspan="15" class="fw-semibold fst-italic">' . $row['description'] . '</td></tr>';
		}

		$j++;
		$i++;
	}

	return $html;
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
			if (empty($luggage_list)) $luggage_list = array();
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

function populateEditedLinePassenger($booking_id)
{
	global $app_list_strings, $db, $current_user;

	$booking = new EC_Flight_Bookings;
	$booking->retrieve($booking_id);

	$sql_supplier = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

	$sql = "
		SELECT 
			p.id
			,p.name
			,p.salutation
			,p.birthday
			,p.type
			,p.eticket_outbound
			,p.eticket_inbound
			,p.pnr_outbound
			,p.pnr_inbound
			,p.description
			,p.direction
			,p.luggage_price
			,p.luggage_price_inbound
			,p.luggage_purchase
			,p.luggage_purchase_inbound
			,p.luggage_index_outbound
			,p.luggage_index_inbound
			,p.supplier_id
			,IF(p.supplier_id IS NOT NULL, 
				(
					SELECT a.name FROM accounts a 
					WHERE a.deleted=0 AND a.id = p.supplier_id 
					LIMIT 1
				)
			, '') AS supplier
			,p.supplier_inbound_id
			,IF(p.supplier_inbound_id IS NOT NULL, 
				(
					SELECT a.name FROM accounts a 
					WHERE a.deleted = 0 AND a.id = p.supplier_inbound_id 
					LIMIT 1
				), 
			'') AS supplier_inbound
			,p.add_type
			,p.parent_detail_id
			,p.date_entered
			,p.go_with
			,(
				SELECT ticket_class FROM ec_booking_itineraries
				WHERE deleted = 0 AND direction = 0
				AND booking_id = p.booking_id
				AND (assigned_user_id = p.id OR assigned_user_id IS NULL OR assigned_user_id = '')
				ORDER BY sabre_logs DESC
				LIMIT 1
			) AS ticket_class_ob
			,(
				SELECT ticket_class FROM ec_booking_itineraries
				WHERE deleted = 0 AND direction = 1
				AND booking_id = p.booking_id
				AND (assigned_user_id = p.id OR assigned_user_id IS NULL OR assigned_user_id = '')
				ORDER BY sabre_logs DESC
				LIMIT 1
			) AS ticket_class_ib
			, (
				SELECT name 
				FROM ec_booking_passengers
				WHERE id = p.parent_detail_id
			) AS old_name
		FROM ec_booking_passengers p
		WHERE p.deleted = 0 
		AND p.booking_id = '" . $booking_id . "'
		AND p.add_type = 2
		ORDER BY p.go_with, p.type ";

	$res = $db->query($sql);
	$rowCount = $db->countRows($res);
	$html = $html1 = $html2 = '';

	$i = $k = 0;
	$pass_order = 0;
	$pass_changed_name_arr = array();
	while ($row = $db->fetchByAssoc($res)) {
		// gắn thông tin của lần thay đổi trước vào html2
		if ($pass_order != $row['go_with'] && !empty($html1)) {
			if (!empty($pass_changed_name_arr)) {
				$html1 .= 'Có ' . count($pass_changed_name_arr) . ' hành khách đổi tên, chi tiết: ' . implode(", ", $pass_changed_name_arr);
			}
			$html2 .= $html1 . '</b></td></tr>' . $html;
		}

		// đánh stt các dòng thay đổi thông tin hành khách
		if ($pass_order != $row['go_with']) {
			$pass_order = $row['go_with'];
			$html1 = '';
			$pass_changed_name_arr = array();
			$html = '';
			$html1 .= '<tr><td colspan="11" class="bg-yellow"><b>Lần thay đổi thứ ' . $row['go_with'] . ': ';
			$i = 0;
		}

		// lấy thông tin khách thay đổi tên
		if (trim($row['old_name']) != trim($row['name'])) {
			$pass_changed_name_arr[] = '<font color="blue">' . $row['old_name'] . '</font> <span style="font-size: 16px;">&rarr;</span> ' . $row['name'];
		}

		$html .= '<tr class="psg-line" data-id="' . $row['id'] . '">
					<td data-label="Chỉnh sửa" class="text-center align-middle"">
						<svg xmlns="http://www.w3.org/2000/svg" class="edit_pass_row cursor-pointer" data-id="' . $row['id'] . '" width="24" height="24" viewBox="0 0 24 24" style="#202020;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
					</td>
					<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td data-label="Loại HK" class="text-center passenger_type">' . $app_list_strings['passenger_type_list'][$row['type']] . '</td>
					<td data-label="Danh xưng" class="text-center passenger_salutation">' . $app_list_strings['passenger_salutation_list'][$row['salutation']] . '</td>
					<td data-label="Họ tên" class="text-start passenger_name">' . $row['name'] . '</td>
					<td data-label="Ngày sinh" class="text-center passenger_birthday">' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date('d-m-Y', strtotime($row['birthday'])) : '') . '</td>
					<td data-label="Ngày sinh" class="text-center passenger_id"></td>
				';

		$html .= '
			<td data-label="Số vé đi" class="text-center eticket_outbound" content="' . strtoupper($row['eticket_outbound']) . '" row_no="' . $row['id'] . '">
				' . strtoupper($row['eticket_outbound']) . '
				<svg xmlns="http://www.w3.org/2000/svg" class="editinline cursor-pointer" style="display:none;" data-id="' . $row['id'] . '" width="24" height="24" viewBox="0 0 24 24" style="#202020;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
				<input type="hidden" name="eticket_outbound[]" id="eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '"  />
			</td>';

		$html .= '
			<td data-label="Số vé về" class="text-center eticket_inbound" content="' . strtoupper($row['eticket_inbound']) . '" row_no="' . $row['id'] . '">
				' . strtoupper($row['eticket_inbound']) . '
				<svg xmlns="http://www.w3.org/2000/svg" class="editinline cursor-pointer" style="display:none;" data-id="' . $row['id'] . '" width="24" height="24" viewBox="0 0 24 24" style="#202020;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
				<input type="hidden" name="eticket_inbound[]" id="eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '"  />
			</td>';

		$html .= '
			<td data-label="PNR đi" class="text-center">
				' . strtoupper($row['pnr_outbound']) . '
				<input type="hidden" name="pnr_outbound[]" id="pnr_outbound' . $i . '" value="' . strtoupper($row['pnr_outbound']) . '"  />
			</td>';
		$html .= '
			<td data-label="PNR về" class="text-center">
				' . strtoupper($row['pnr_inbound']) . '
				<input type="hidden" name="pnr_inbound[]" id="pnr_inbound' . $i . '" value="' . strtoupper($row['pnr_inbound']) . '"  />
			</td>';

		/* Thông tin hành lý lượt đi*/
		// -------------------------------------
		$luggage_price = '';
		// luggage_price_arr là list option hành lý
		$luggage_price_arr = generateLuggage($booking->date_entered, $booking->airline, $row['ticket_class_ob'], $row['type'], (int)$row['luggage_index_outbound']);

		if (!empty($row['luggage_index_outbound'])) {
			$row['luggage_price'] = (int)$row['luggage_index_outbound'];
		}
		$bag_out2 = $luggage_price_arr[(int)$row['luggage_price']];

		$bag_weight_out = 0;
		if (isset($bag_out2) && !empty($bag_out2)) {
			preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
			$bag_weight_out = isset($ob_output[1]) ? (int)$ob_output[1] : 0;
		}

		if ($bag_weight_out > 0) {
			$luggage_price .= '<span class="text-conpleted-status fw-semibold fst-italic">Lượt đi</span>: ' . $bag_out2 . ' (Giá mua: ' . format_number($row['luggage_purchase']) . ' - Nhà cung cấp: ' . $row['supplier'] . ')<br>';
		}


		/* Thông tin hành lý lượt về*/
		// ------------------------------------
		$luggage_price_ib_arr = generateLuggage($booking->date_entered, $booking->airline_inbound, $row['ticket_class_ib'], $row['type'], (int)$row['luggage_index_inbound']);
		if (!empty($row['luggage_index_inbound'])) {
			$row['luggage_price_inbound'] = (int)$row['luggage_index_inbound'];
		}

		$bag_in = $luggage_price_ib_arr[(int)$row['luggage_price_inbound']];

		$bag_weight_in = 0;
		if (isset($bag_in) && !empty($bag_in)) {
			preg_match('/(\d+)kg/isU', $bag_in, $ib_output);

			$bag_weight_in = isset($ib_output[1]) ? (int)$ib_output[1] : 0;
		}
		if ($bag_weight_in > 0) {
			$luggage_price .= '<span class="color-red fst-italic fw-semibold">Lượt về</span>: ' . $bag_in . ' (Giá mua: ' . format_number($row['luggage_purchase_inbound']) . ' - Nhà cung cấp: ' . $row['supplier_inbound'] . ')';
		}
		/* END Thông tin hành lý lượt về*/

		$html .= '
				<tr ' . (trim($luggage_price) == '' ? 'style="display:none;"' : '') . '>
					<td class="text-center align-middle hide-mobile">&nbsp;</td>
					<td colspan="9" class="text-start align-middle fst-italic">' . $luggage_price . '</td>
				</tr>';

		$i++;
		$k++;

		// lấy thông tin thay đổi trước gắn vào html2
		if ($k == $rowCount && !empty($html1)) {
			if (!empty($pass_changed_name_arr)) {
				$html1 .= 'Có ' . count($pass_changed_name_arr) . ' hành khách đổi tên, chi tiết: ' . implode(", ", $pass_changed_name_arr);
			}
			$html2 .= $html1 . '</b></td></tr>' . $html;
		}
	} // while

	return $html2;
}

// lấy thông tin chia DS
if ($_POST['for'] == 'getShareProfit') {
	$bk = new EC_Flight_Bookings;
	// tính ds 1 booking
	$bk_profit = $bk->calculateBKTotalAmt($_POST['bk']);

	// bảng chia ds
	// $html = '<thead>
	// 	<tr>
	// 		<td width="5%" style="font-weight: bold;">STT</td>
	// 		<td width="55%" style="font-weight: bold;">Booker</td>
	// 		<td style="text-align: right;font-weight: bold;">Số tiền</td>
	// 		<td width="5%" style="text-align: center;font-weight: bold;"></td>
	// 	</tr></thead>
	// ';
	$html = '';

	$sql = '
			SELECT 
				com_bk.id AS com_bk_id, com_bk.total_amount, bk.name AS bk_name
				, u.last_name AS com_bk_ulname, u.first_name AS com_bk_ufname
				, com_bk.name AS com_bk_username, com_bk.assigned_user_id AS com_bk_userid 
			FROM ec_completed_bookings com_bk
			INNER JOIN ec_flight_bookings bk ON bk.id = com_bk.ec_flight_bookings_id_c
			INNER JOIN users u ON u.id = com_bk.assigned_user_id 
			WHERE com_bk.deleted = 0 AND com_bk.completed_bk_type = "SHARE_PROFIT"
			AND com_bk.ec_flight_bookings_id_c = "' . $_POST['bk'] . '"
		';

	$res 		= $db->query($sql);
	$total_share 	= 0;
	$i 			= 0;
	$total_share 	= 0;

	if ($db->countRows($res) > 0) {
		while ($row = $db->fetchByAssoc($res)) {
			if (isAllowedUser()) {
				$html .= '
						<tr class="profit_ln">
							<td class="fw-bold text-center align-middle">' . ($i + 1) . '</td>
							<td>
								<div class="d-flex align-items-center gap-2">
									<input class="box-input" type="text" name="share_profit_user[]" id="share_profit_user' . ($i + 1) . '" size="20" value="' . $row['com_bk_username'] . '" autocomplete="off">
									<input type="button" class="btn btn-primary" value="Chọn" onclick="open_popup(&quot;Users&quot;, 600, 400, &quot;&quot;, true, false, {&quot;call_back_function&quot;:&quot;set_return&quot;,&quot;form_name&quot;:&quot;share_profit_frm&quot;,&quot;field_to_name_array&quot;:{&quot;id&quot:&quot;share_profit_user_id' . ($i + 1) . '&quot;,&quot;user_name&quot;:&quot;share_profit_user' . ($i + 1) . '&quot;}},&quot;single&quot;, true);" style="vertical-align: baseline;">
									<input type="hidden" name="share_profit_userid[]" id="share_profit_user_id' . ($i + 1) . '" value="' . $row['com_bk_userid'] . '">
								</div>
							</td>
							<td>
								<input class="box-input text-end" type="text" name="share_profit_amt[]" id="share_profit_amt' . ($i + 1) . '" oninput="this.value = formatNumber(unformatNumber(this.value)); calculateTotalShareProfit();" value="' . format_number($row['total_amount']) . '">
							</td>
							<td class="text-center">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" onclick="markShareProfitDelete(' . ($i + 1) . ');" class="bi bi-x-lg cursor-pointer" viewBox="0 0 16 16">
									<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
								</svg>
								<input type="hidden" name="share_profit_delete[]" id="share_profit_delete' . ($i + 1) . '" value="0">
								<input type="hidden" name="share_profit_id[]" value="' . $row['com_bk_id'] . '">
							</td>
						</tr>
					';
			} else {
				$html .= '
						<tr class="profit_ln">
							<td class="fw-bold text-center align-middle">' . ($i + 1) . '</td>
							<td>' . replaceAllSpacesToSingleSpace($row['com_bk_ulname'] . ' ' .  $row['com_bk_ufname']) . '</td>
							<td style="text-align: right; padding-right: 3px;">' . format_number($row['total_amount']) . '</td>
							<td class="text-center">
								<input type="hidden" name="share_profit_delete[]" id="share_profit_delete' . ($i + 1) . '" value="0">
							</td>
						</tr>
					';
			}

			$total_share += $row['total_amount'];
			$i++;
		}
		$i++;
	} else {
		$i = 1;
	}

	$html .= '
			<tr class="footer-tr">
				<td class="fw-bold text-start" colspan="2">Tổng</td>
				<td class="fw-bold text-end"><span id="ttl_share_profit">' . format_number($total_share) . '</span></td>
				<td></td>
			</tr>
		';

	echo json_encode(array('profit' => format_number($bk_profit), 'html' => $html, 'line_cnt' => $i));
}


// BLOCK - UNBLOCK - WHITELIST IP ON WEBSITE
if (isset($_POST['for']) && $_POST['for'] == 'block_ip') {
	$ip 		= isset($_POST['ip']) ? $_POST['ip'] : '';
	$duration 	= isset($_POST['duration']) ? $_POST['duration'] : 0;
	$domain 	= isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->blockIP($domain, $ip, $duration);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'unblock_ip') {
	$ip 	= isset($_POST['ip']) ? $_POST['ip'] : '';
	$domain = isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->unblockIP($domain, $ip);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'whitelist_ip') {
	$ip 		= isset($_POST['ip']) ? $_POST['ip'] : '';
	$duration 	= isset($_POST['duration']) ? $_POST['duration'] : 0;
	$domain 	= isset($_POST['domain']) ? $_POST['domain'] : '';

	require_once('modules/EC_TongHop/views/view.iplist.php');
	$IPList = new Viewiplist();
	echo $IPList->whitelistIP($domain, $ip, $duration);
	exit();
}
if (isset($_POST['for']) && $_POST['for'] == 'get_blocking_history') {
	$ip 		= isset($_POST['ip']) ? $_POST['ip'] : '';
	$from_date 	= isset($_POST['from_date']) ? $_POST['from_date'] : '';
	$to_date 	= isset($_POST['to_date']) ? $_POST['to_date'] : '';
	$domain 	= isset($_POST['domain']) ? $_POST['domain'] : '';

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
					<th width="5%">STT</th>
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
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			WHERE bk.deleted = 0
			AND (
				TIMESTAMPDIFF(
					MINUTE
					, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)
					, i.departure_date
				) <= 1440
				OR TIMESTAMPDIFF(
					MINUTE
					, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)
					, i.arrival_date
				) <= 1440
			) 
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk.id
			ORDER BY bk.date_entered
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total, 1);
	$completed_txt 	= getComparePercentTxt($completed, $total, 1);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total, 1);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

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
}

// lấy ds bk 2 vé
if (isset($_POST['for']) && $_POST['for'] == 'get2TicketBooking') {
	$html2 = '
				<table id="twoticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get2TicketBooking2" cellspacing="0" cellpadding="0">
					<thead>
						<th width="5%">STT</th>
						<th width="10%">Booking</th>
						<th width="10%">Trạng thái</th>
						<th width="10%">Vé</th>
						<th width="15%">Doanh số</th>
						<th width="15%">Ngày đặt</th>
					</thead>
					<tbody>
			';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			WHERE bk.deleted = 0 AND bk.total_qty = 2
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk.id
			ORDER BY bk.date_entered
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
					<td class="text-center">
						' . date('d-m-Y H:i:s', strtotime($row['bk_date_entered'])) . '
					</td>
				</tr>';
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 	= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	$html1 = '<table class="detail_bk_tbl table-details__booking table-get2TicketBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Đã gọi</th>
					<th width="10%">Booking huỷ</th>
					<th>Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center">' . format_number($called) . $called_txt . '</td>
					<td class="text-center"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td>' . $note . '</td>
				</tbody>
			</table>';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
}

// lấy ds bk 3 vé trở xuống
if (isset($_POST['for']) && $_POST['for'] == 'get3TicketBooking') {
	$html2 = '
			<table id="threeticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get3TicketBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">STT</th>
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
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
				, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0)) AS average_fee 
				, (SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.deleted = 0 AND i.booking_id = bk.id) AS min_dep_time
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			LEFT JOIN ec_booking_itineraries i 
			ON i.booking_id = bk.id
			LEFT JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 AND i.deleted = 0 AND bk.total_qty <= 3
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk.id
			HAVING TIMESTAMPDIFF(MINUTE, bk_date_entered, min_dep_time) > 1440
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), average_fee DESC
		';

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;

	while ($row = $db->fetchByAssoc($res)) {
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));

		$service_fee = implode("&nbsp;/&nbsp;", array_map(function ($val) {
			return format_number($val);
		}, explode(',', $row['service_fee'])));

		// Ngày xuất vé
		if ($row['bk_date_ticket_issue'] == '') {
			$date_ticket_issue = '';
		} else {
			$date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
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
					<td class="text-center service_fee hide-mobile">' . $service_fee . '</td>
					<td class="text-center total_ticket">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center bk_sales">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center average_fee">' . format_number($row['average_fee']) . '</td>
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 	= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

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
}

// lấy ds bk 4-8 vé
if (isset($_POST['for']) && $_POST['for'] == 'get4To8TicketBooking') {
	$html2 = '
			<table id="feticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get4To8TicketBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">STT</th>
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
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
				, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0)) AS average_fee 
				, (SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.deleted = 0 AND i.booking_id = bk.id) AS min_dep_time
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			INNER JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 AND bk.total_qty >= 4 AND bk.total_qty <= 8
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk.id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), average_fee DESC
		';
	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));
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
					<td class="text-center">' . format_number($row['average_fee']) . '</td>
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 	= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

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
}

// lấy ds bk trên 9 vé
if (isset($_POST['for']) && $_POST['for'] == 'get9TicketBooking') {
	$html2 = '
			<table id="nineticket_bk_tbl" class="detail_bk_tbl table-details__booking table-get9TicketBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">STT</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="10%">Phí DV</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Giao cho</th>
					<th>Ngày đặt</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
				, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0)) AS average_fee 
				, (SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.deleted = 0 AND i.booking_id = bk.id) AS min_dep_time
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			INNER JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 AND bk.total_qty >= 9
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk_id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), average_fee DESC
		';
	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));
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
					<td class="text-center">' . $service_fee . '</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center">' . format_number($row['average_fee']) . '</td>
					<td class="text-center hide-mobile">' . $row['user_name'] . '</td>
					<td class="text-center">
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 	= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

	// ghi chú nếu trạng thái bk khác hoàn tất / đã gọi / huỷ
	$note = '';
	$note .= genNoteBKStt($exported, $exported_txt, 'Xuất vé');
	$note .= genNoteBKStt($confirmed, $confirmed_txt, 'Xác nhận');
	$note .= genNoteBKStt($paidwait, $paidwait_txt, 'Chờ TT');
	$note .= genNoteBKStt($created, $created_txt, 'Mới tạo');

	$html1 = '
			<table class="detail_bk_tbl table-details__booking table-get9TicketBooking1 mb-3" cellspacing="0" cellpadding="0">
				<thead>
					<th width="10%">Tổng số Booking</th>
					<th width="10%">Hoàn tất</th>
					<th width="10%">Số vé xuất</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="10%">Đã gọi</th>
					<th width="10%">Booking huỷ</th>
					<th>Ghi chú</th>
				</thead>
				<tbody>
					<td class="text-center fw-semibold">' . format_number($total) . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($completed) . '</font>' . $completed_txt . '</td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($ticket_completed) . '</span></td>
					<td class="text-center"><span class="text-conpleted-status fw-semibold">' . format_number($total_sale) . '</span></td>
					<td class="text-center">' . format_number($total_sale / $ticket_completed) . '</td>
					<td class="text-center">' . format_number($called) . $called_txt . '</td>
					<td class="text-center"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td>' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
}

// Lấy ds bk vé quốc tế
if (isset($_POST['for']) && $_POST['for'] == 'getInterBooking') {
	$html2 = '
			<table id="inter_bk_tbl" class="detail_bk_tbl table-details__booking table-getinter_booking" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">STT</th>
					<th width="10%">Booking</th>
					<th width="10%">Trạng thái</th>
					<th width="10%" class="hide-mobile">Phí DV</th>
					<th width="5%">Vé</th>
					<th width="10%">Doanh số</th>
					<th width="10%">Phí bình quân</th>
					<th width="8%" class="hide-mobile">Giao cho</th>
					<th width="15%" class="hide-mobile">Ngày đặt</th>
					<th class="hide-mobile">Ngày xuất vé</th>
				</thead>
				<tbody>
		';
	$sql = '
			SELECT 
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales 
				, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0)) AS average_fee 
				, (SELECT MIN(i.departure_date) FROM ec_booking_itineraries i WHERE i.deleted = 0 AND i.booking_id = bk.id) AS min_dep_time
				, (SELECT GROUP_CONCAT(DISTINCT service_fee) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id) AS service_fee
				, u.user_name
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			INNER JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 AND bk.ticket_type = 2
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			GROUP BY bk_id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), average_fee DESC
		';

	// pr($sql);

	$res = $db->query($sql);
	$i = $total = $canceled = $completed = $exported = $confirmed = $called = $paidwait = 0;
	$created = $ticket_completed = $total_sale = 0;
	while ($row = $db->fetchByAssoc($res)) {
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));
		$service_fee = implode("&nbsp;/&nbsp;", array_map(function ($val) {
			return format_number($val);
		}, explode(',', $row['service_fee'])));

		// Ngày xuất vé
		if ($row['bk_date_ticket_issue'] == '') {
			$date_ticket_issue = '';
		} else {
			$date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
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
					<td class="text-center hide-mobile">' . $service_fee . '</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center">' . format_number($row['average_fee']) . '</td>
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 	= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total, 1);

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
					<td class="text-center fee-average">' .  $fee_average . '</td>
					<td class="text-center hide-mobile">' . format_number($called) . $called_txt . '</td>
					<td class="text-center hide-mobile"><span class="color-red fw-semibold">' . format_number($canceled) . $canceled_txt . '</span></td>
					<td class="hide-mobile">' . $note . '</td>
				</tbody>
			</table>
		';

	$html = '<div class="box-section detail_bk--wrap">' . $html1 . $html2 . '</div>';

	echo $html;
}

function genNoteBKStt($num, $num_per, $stt_name, $init_note = '')
{
	$note = '';
	if ($num > 0) {
		if (!empty($init_note)) $note .= '<br>';
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
	} else $percent_txt = '';
	return $percent_txt;
}

// lấy ds booking do booker đặt
if (isset($_POST['for']) && $_POST['for'] == 'getBookerBooking') {
	$html2 = '
			<table id="booker_bk_tbl" class="detail_bk_tbl table-details__booking table-getBookerBooking2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="5%">STT</th>
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
				-- , (bk.total_amount - bk.total_bought_amount) AS bk_sales 
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id)), 0) AS bk_sales
				, bk.contact_name, bk.phone
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
			FROM ec_flight_bookings bk
			LEFT JOIN ec_flight_bookings_audit a
			ON bk.id = a.parent_id
			WHERE bk.deleted = 0
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . '"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			AND bk.created_by = "' . $_POST['user'] . '"
			AND (
				(a.field_name = "contact_name" AND a.before_value_string IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi"))
				OR bk.contact_name IN ("Panda Po", "Bao Gia Khach", "Khach Hang Hoi")
			)
			GROUP BY bk.id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), bk.date_entered DESC
		';

	// pr($sql);

	$res 	= $db->query($sql);
	$i 		= $total = $canceled = $completed = $confirmed = $called = $paidwait = 0;
	$created 	= $ticket_completed = 0;

	while ($row = $db->fetchByAssoc($res)) {

		if (empty($row['bk_date_ticket_issue'])) {
			$date_ticket_issue = '';
		} else {
			$date_ticket_issue = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
		}

		$html2 .= '
				<tr>
					<td class="text-center fw-semibold">' . ($i + 1) . '</td>
					<td class="text-center">
						<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['bk_id'] . '" target="_blank">' . $row['bk_name'] . '</a>
					</td>
					<td class="text-center fw-semibold">
						<font color="' . $app_list_strings['booking_status_color_list'][(int)$row['booking_status']] . '">' . $app_list_strings['booking_status_list'][(int)$row['booking_status']] . '</font>
					</td>
					<td class="text-center">' . format_number($row['total_ticket']) . '</td>
					<td class="text-center">' . format_number($row['bk_sales']) . '</td>
					<td class="text-center hide-mobile">' . $row['contact_name'] . '</td>
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total);
	$completed_txt 		= getComparePercentTxt($completed, $total);
	$exported_txt 		= getComparePercentTxt($exported, $total);
	$confirmed_txt 		= getComparePercentTxt($confirmed, $total);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total);
	$called_txt 		= getComparePercentTxt($called, $total);
	$created_txt 		= getComparePercentTxt($created, $total);

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
}

// thống kê chi tiết booking trong ngày
if (isset($_POST['for']) && $_POST['for'] == 'getToTalBKInOneDay') {
	$html2 = '
			<table id="getToTalBKInOneDay_lbl" class="detail_bk_tbl table-details__booking table-getToTalBKInOneDay2" cellspacing="0" cellpadding="0">
				<thead>
					<th width="3%">STT</th>
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
				bk.id AS bk_id, bk.name AS bk_name, bk.booking_status, bk.ticket_type
				, DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) AS bk_date_entered
				, bk.date_ticket_issue AS bk_date_ticket_issue
				, GROUP_CONCAT(IF(i.direction = 0, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS departure_date
				, GROUP_CONCAT(IF(i.direction = 1, IF(i.departure_date IS NULL, NULL, DATE_FORMAT(i.departure_date, "%d-%m-%Y %H:%i:%s")), NULL) SEPARATOR "|") AS arrival_date
				, IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id =  bk.id), 0) AS total_ticket
				, IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id AND (add_type IS NULL OR add_type = ""))), 0) AS bk_sales
				, (IF(bk.booking_status IN (3, 7, 8), (bk.total_amount - bk.total_bought_amount - (SELECT SUM(IFNULL(luggage_purchase, 0)) + SUM(IFNULL(luggage_purchase_inbound, 0)) FROM ec_booking_passengers WHERE deleted = 0 AND booking_id = bk.id AND (add_type IS NULL OR add_type = ""))), 0) / IFNULL((SELECT SUM(quantity) FROM ec_booking_details WHERE deleted = 0 AND booking_id = bk.id), 0)) AS average_fee
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
				, MIN(i.departure_date) AS min_dep_date 
				, u.user_name
			FROM ec_flight_bookings bk
			INNER JOIN ec_booking_itineraries i 
			ON i.deleted = 0 AND i.booking_id = bk.id
			INNER JOIN users u ON u.id = bk.assigned_user_id
			WHERE bk.deleted = 0 
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) >= 
				"' . date('Y-m-d', strtotime($_POST['fdate'])) . ' 00:00:00"
			AND DATE_ADD(bk.date_entered, INTERVAL 7 HOUR) <= 
				"' . date('Y-m-d', strtotime($_POST['tdate'])) . ' 23:59:59"
			GROUP BY bk_id
			ORDER BY FIELD(booking_status, 8, 7, 3, 2, 6, 1, 4), average_fee DESC
		';
	// if($GLOBALS['current_user']->id == 1) {
	// 	echo $sql;
	// 	exit;
	// }
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
		if (((strtotime($row['min_dep_date']) - strtotime($row['bk_date_entered'])) / 60) <= 1440) {
			if (!empty($type)) $type .= '&nbsp;-&nbsp;';
			$type .= 'Vé Cận';
		}
		$departure_date = implode("<br>", explode("|", $row['departure_date']));
		$arrival_date = implode("<br>", explode("|", $row['arrival_date']));
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
			$bk_date_ticket_issue  = '';
		} else {
			$bk_date_ticket_issue  = date('d-m-Y', strtotime($row['bk_date_ticket_issue']));
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
	$canceled_txt 		= getComparePercentTxt($canceled, $total, 1);
	$completed_txt 	= getComparePercentTxt($completed, $total, 1);
	$exported_txt 		= getComparePercentTxt($exported, $total, 1);
	$confirmed_txt 	= getComparePercentTxt($confirmed, $total, 1);
	$paidwait_txt 		= getComparePercentTxt($paidwait, $total, 1);
	$called_txt 		= getComparePercentTxt($called, $total, 1);
	$created_txt 		= getComparePercentTxt($created, $total, 1);
	$total0_txt 		= getComparePercentTxt($total0, $total, 1);

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

	$res_inter 		= $db->query($sql_inter);
	$i 				= 0;
	$total_ticket 		= 0;
	$total_bk_sales 	= 0;
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

	$html = '<div class="box-section infor-booking__domestic--wrap">
				<table id="tbl-infor-booking__domestic" class="table-infor-booking__domestic table-details__booking" border="0" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th width="6%" align="center">Booking</th>
							<th width="7%" align="center">Tình trạng</th>
							<th width="11%" align="center" class="hide-mobile">Liên hệ</th>
							<th width="7%" align="center" class="hide-mobile">Điện thoại</th>
							<th width="7%" align="center" class="hide-mobile">Phí dịch vụ</th>
							<th width="3%" align="center">Vé</th>
							<th width="8%" align="center">Doanh số</th>
							<th width="8%" align="center">Phí bình quân</th>
							<th width="8%" align="center" class="hide-mobile">Ngày tạo</th>
							<th class="hide-mobile" width="8%" align="center">Ngày xuất vé</th>
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

	$sql_domestic = 'SELECT 
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
						AND bk.ticket_type <> 2
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
					WHERE bk.ticket_type <> 2
					AND p.loai_thu IN ("4", "5", "10", "11", "12", "13", "14", "16") 
					AND DATE(p.ngayhachtoan)>="' . date("Y-m-d", strtotime($post_from_date)) . '"
					AND DATE(p.ngayhachtoan)<="' . date("Y-m-d", strtotime($post_to_date)) . '"
					AND p.deleted=0
					AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
					GROUP BY p.id
					
					-- hoan ve
					UNION
					SELECT 
						hv_t.parent_id, hv_t.parent_name, hv_t.parent_type
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
						AND bk.ticket_type <> 2
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
						AND bk.ticket_type <> 2
						AND p.tinhtrang="1" 
						AND p.ngayhachtoan>="' . date('Y-m-d', strtotime($post_from_date)) . '"
						AND p.ngayhachtoan<="' . date('Y-m-d', strtotime($post_to_date)) . '"
						GROUP BY p.id
						HAVING SUM(IFNULL(p.tongtienhang,0)) - SUM(IFNULL(p.tongtienkhach,0)) > 0
					) AS hv_t
		GROUP BY hv_t.parent_id
		ORDER BY total_quantity DESC';

	// if($current_user->user_name == 'hungnh'){
	// 	pr($sql_domestic);
	// }

	$res_domestic 		= $db->query($sql_domestic);
	$i 				= 0;
	$total_ticket 		= 0;
	$total_bk_sales 	= 0;
	$total_amount_domestic = 0;
	while ($row = $db->fetchByAssoc($res_domestic)) {

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
					<td align="right" class="fw-bold hide-mobile">' . format_number(($row['service_fee'])) . '</td>
					<td align="center">' . $row['total_quantity'] . '</td>
					<td align="right" class="fw-bold">' . format_number(($row['subtotal_amount'] - $row['total_bought_price'])) . '</td>
					<td align="right" class="fw-bold">' . format_number(($row['average_fee'])) . '</td>
					<td align="center" class="hide-mobile">' . date('d-m-Y', strtotime($row['bk_date_entered'])) . '</td>
					<td align="center" class="hide-mobile">' . $date_ticket_issue . '</td>
				</tr>';
		$i++;
		$total_ticket += $row['total_quantity'];
		$total_bk_sales += ($row['subtotal_amount'] - $row['total_bought_price']);
		$total_amount_domestic += $row['subtotal_amount'];
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

/*
	Danh sách booking của liên hệ - QUY ĐỊNH 1 SDT LÀ 1 LIÊN HỆ
*/
if (isset($_POST['for']) && $_POST['for'] == 'showHistoryBookingContact') {
	$contact_id  = isset($_POST['contact_id']) ? $_POST['contact_id'] : '';
	$where  	 = (isset($_POST['purpose']) && $_POST['purpose'] == 'get_completed_status') ? ' AND booking_status = 8' : '';
	$booking_id  = $_POST['booking_id'] ?? '';

	$html = $html_summary = '';
	$type_contact = '';
	$name_contact = '';
	$phone_contact = '';
	$email_contact = '';

	$total_ = 0;
	$count_booking = 0;
	$count_booking_completed 	= 0;
	$count_booking_cancel 		= 0;
	$count_booking_other 		= 0;

	$total_revenue 		= 0;
	$total_profit 		= 0;
	
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
								<th>Liên hệ</th>
								<th>Số vé</th>
								<th>Doanh thu</th>
								<th>Doanh số</th>
							</tr>
						</thead>';

		$sql = "SELECT id, name, contact_name, phone, email, journey, booking_status, total_amount, total_qty, date_entered
				FROM ec_flight_bookings
				WHERE  contact_id = '" . $contact_id . "' AND deleted = 0
				".$where."
				ORDER BY date_entered DESC";

		$res = $db->query($sql);
		$count_booking 	= $db->countRows($res);

		$con = new Contact();
		$con->retrieve($contact_id);
		$name_contact 	= $con->last_name;
		$phone_contact 	= $con->phone_mobile;
		$email_contact 	= $con->email1;

		if ($count_booking > 0) {
			$i = 1;
			while ($row = $db->fetchByAssoc($res)) {

				// Infor contact
				$current_date 			= date('Y-m-d');
				if ($row['booking_status'] == 2) { //CHỜ THANH TOÁN
					$class_color = 'text-warning';
				} elseif ($row['booking_status'] == 3 || $row['booking_status'] == 7) { //XÁC NHẬN
					$class_color = 'text-success';
				} elseif ($row['booking_status'] == 4) { //HỦY
					$class_color = 'text-danger';
				} elseif ($row['booking_status'] == 6) { //ĐÃ GỌI
					$class_color = 'text-info';
				} elseif ($row['booking_status'] == 8) { //HOÀN TẤT
					$class_color = 'text-primary';
				} else {
					$class_color = 'text-dark';
				}

				// Journey
				if ($row['journey']) {
					$journey = $row['journey'];
				} else {
					$journey_array 	= journeyOfBooking($row['id']);
					$journey 		= $journey_array["departure"] . '-' . $journey_array["arrival"];
				}

				// if ($row['booking_status'] == 8 || $row['booking_status'] == 7 || $row['booking_status'] == 3) {
				if ($row['booking_status'] == 8) {
					$count_booking_completed++;

					$total_revenue  += $row['total_amount'];
					$total_profit 	+= calculateBKTotalAmt($row['id']);
				} else if ($row['booking_status'] == 4) {
					$count_booking_cancel++;
				} else {
					$count_booking_other++;
				}

				// pr($row);
				$current_booking = ($row['id'] == $booking_id) ? 'current_booking' : '';

				$html .= '<tr>
							<td class="' . $current_booking . ' hide-mobile fw-bold text-center">' . $i . '</td>
							<td class="' . $current_booking . '"><a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['id'] . '" target="_blank">' . $row['name'] . '</a></td>
							<td class="' . $current_booking . ' hide-mobile text-center">' . $journey . '</td>
							<td class="' . $current_booking . ' hide-mobile text-center fw-bold ' . $class_color . '">' . $app_list_strings['booking_status_list'][(int)$row['booking_status']] . '</td>
							<td class="' . $current_booking . ' text-center">' . date('H:i d-m-Y', strtotime('+7 hours', strtotime($row['date_entered']))) . '</td>
							<td class="' . $current_booking . '">' . $row['contact_name'] . '</td>
							<td class="' . $current_booking . ' text-center fw-bold">' . $row['total_qty'] . '</td>
							<td class="' . $current_booking . ' text-end fw-bold">' . format_number($row['total_amount']) . '</td>
							<td class="' . $current_booking . ' text-end fw-bold">' . format_number(calculateBKTotalAmt($row['id'])) . '</td>
						</tr>';
				$i++;
			}
		}

		// $type_contact = classifyContact($contact_id);
		$type_contact = classifyContactv2($contact_id);

		$html .= '</table></div>';

		$denominator_booking_completed = ($count_booking_completed == 0) ? 1 : $count_booking_completed;
		$html_summary .= '
						<div class="d-flex gap-2 mb-3 flex-nowrap">
							<div class="flex-fill lh-base">
								<p>👤 *Họ tên: ' . $name_contact . '</p>  
								<p>📞 *SĐT: ' . $phone_contact . '</p>
								<p>📧 *Email: ' . $email_contact . '</p>
								<p>🏷️ *Mô tả: ' . $type_contact['desc'] . '</p>  
							</div>
							<div class="flex-fill lh-base">
								<p>🏷️ *Loại khách hàng: <span class="fw-bold">' . $type_contact['label'] . '</span></p>  
								<p>📊 *Tổng booking: ' . $count_booking . ' (<span class="text-primary fw-bold">' . $count_booking_completed . ' hoàn tất</span>, <span class="text-danger fw-bold">' . $count_booking_cancel . ' hủy</span>, <span class="text-dark fw-bold">' . $count_booking_other . ' Khác</span>)</p>
								<p>✅ *Tỷ lệ hoàn tất: <span class="fw-bold">' . round(($count_booking_completed / $count_booking * 100), 2) . '%</span></p>
								<p>❌ *Tỷ lệ hủy: <span class="fw-bold">' . round(($count_booking_cancel / $count_booking * 100), 2) . '%</span></p>
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
