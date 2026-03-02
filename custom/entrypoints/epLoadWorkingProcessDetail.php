<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if (!empty($_SESSION['authenticated_user_id'])) {

	$kpi_type = trim($_POST['kpi_type']);
	$user_id = trim($_POST['user_id']);
	$load_type = trim($_POST['load_type']);
	$from_date = trim($_POST['from_date']);
	$to_date = trim($_POST['to_date']);
	$html = '';

	$sql_search = "";
	// từ ngày
	if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
		$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '" . date('Y-m-d', strtotime($from_date)) . "' ";
	} else {
		$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '" . date('Y-m-d') . "' ";
	}
	// đến ngày
	if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($to_date)) . "' ";
	} else {
		$sql_search .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '" . date('Y-m-d') . "' ";
	}

	// Filter by employee kpi type
	if (isset($_POST['kpi_type']) && !empty($_POST['kpi_type']) && in_array($_POST['kpi_type'], array_keys($app_list_strings['employee_kpi_type_list']))) {
		$sql_search .= " AND w." . $_POST['kpi_type'] . " IS NOT NULL AND w." . $_POST['kpi_type'] . " > 0 ";
	}

	// phân quyền dữ liệu
	if (
		is_admin($current_user) || ($current_user->title == 'QuanLy') // ngocthu, trangbtq, soinau
	) {
		$sql_search .= " AND w.assigned_user_id='" . $user_id . "' ";
	} else {
		$sql_search .= " AND w.assigned_user_id='" . $current_user->id . "' ";
	}

	// load danh sách booking đã xuất vé
	if ($load_type && $load_type == 'total_ticket') {
	}

	// load danh sách điểm thưởng
	if ($load_type && $load_type == 'total_bonus') {

		$html .= '<table class="table-details__booking detail-data-list table-total_bonus__detail" cellpadding="0" cellspacing="0" border="0">
		<thead>
		<tr>
			<th width="8%" align="center">Booking</th>
			<th width="10%" align="center">Liên hệ</th>
			<th width="14%" align="center">Ghi chú</th>
			<th width="8%" align="center"><span title="Giá bán">GB</span></th>
			<th width="8%" align="center"><span title="Giá mua">GM</span></th>
			<th width="8%" align="center"><span title="Lợi nhuận">LN</span></th>
			<th width="10%" align="center">Nhân viên</th>
			<th width="5%" align="center">Bonus</th>
			<th width="5%" align="center">Duyệt</th>
			<th width="10%" align="center">Người duyệt</th>
			<th width="14%" align="center">Nhận xét</th>
		</tr></thead>';

		$sql = "SELECT w.name AS booking
					  ,w.parent_id AS booking_id
					  ,w.parent_type AS module
					  ,b.contact_name
					  ,w.description
					  ,IFNULL(b.total_amount,0) AS total_amount
					  ,((
							SELECT SUM(IFNULL(d.total_bought_price,0))
							FROM ec_booking_details d
							WHERE d.deleted = 0
							AND d.booking_id = b.id
						) + (
							SELECT IF(b.flight_type = '0'
									 ,SUM(
										IF(p.luggage_price > 0, IFNULL(p.luggage_purchase,0), 0) 
										+ IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound,0), 0)
									  )
									 ,SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase,0), 0))
									)
							FROM ec_booking_passengers p
							WHERE p.deleted = 0
							AND p.booking_id = b.id
					   )) AS total_purchase
					  ,(IFNULL(b.total_amount,0) - (SELECT total_purchase)) AS total_profit
					  ,DATE_ADD(w.date_entered, INTERVAL 7 HOUR) AS date_entered
					  ,CONCAT(IFNULL(u.last_name,''),IF(u.first_name IS NOT NULL,' ',''),IFNULL(u.first_name,'')) AS assigned_user
					  ,IFNULL(w.bonus,0) AS bonus
					  ,w.is_approved
					  ,CONCAT(IFNULL(u1.last_name,''),IF(u1.first_name IS NOT NULL,' ',''),IFNULL(u1.first_name,'')) AS approved_by
					  ,w.approved_note
				FROM ec_working_process w
				LEFT JOIN ec_flight_bookings b ON w.parent_id = b.id AND b.deleted = 0
				LEFT JOIN users u ON w.assigned_user_id = u.id AND u.deleted = 0
				LEFT JOIN users u1 ON w.approved_by_id = u1.id AND u1.deleted = 0
				WHERE w.deleted = 0
				AND w.parent_type = 'EC_Flight_Bookings'
				AND w.bonus IS NOT NULL " . $sql_search;

		$res = $db->query($sql);
		$total_bonus = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$html .= '<tr>
						<td align="center"><a target="_blank" href="index.php?module=' . $row['module'] . '&action=DetailView&record=' . $row['booking_id'] . '" title="Xem chi tiết booking ' . $row['booking'] . '">' . $row['booking'] . '</a></td>
						<td align="center">' . $row['contact_name'] . '</td>
						<td align="left">' . $row['description'] . '</td>
						<td align="right">' . format_number($row['total_amount']) . '</td>
						<td align="right">' . format_number($row['total_purchase']) . '</td>
						<td align="right">' . format_number($row['total_profit']) . '</td>
						<td align="left">' . $row['assigned_user'] . '</td>
						<td align="right">' . format_number($row['bonus']) . '</td>
						<td align="center"><input disabled="disabled" type="checkbox" ' . ($row['is_approved'] ? 'checked="checked"' : '') . ' /></td>
						<td align="left">' . $row['approved_by'] . '</td>
						<td align="left">' . $row['approved_note'] . '</td>
					</tr>';
			$total_bonus += $row['bonus'];
		}

		$html .= '<tr class="footer-tr">
			<td colspan="7" align="center">Tổng</td>
			<td align="right">' . $total_bonus . '</td>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html .= '</table>';
	}

	// load danh sách điểm KPI đã đạt
	if ($load_type && $load_type == 'total_kpi') {

		$html .= '<table class="table-details__booking table-total__kpi detail-data-list" cellpadding="0" cellspacing="0" border="0">
		<thead><tr>
			<th width="10%" align="center">Chứng từ</th>
			<th width="20%" align="center">Ghi chú</th>
			<th width="8%" align="center">Ngày</th>
			<th width="4%" align="center"><span title="Called">CAL</span></th>
			<th width="4%" align="center"><span title="Comepleted">COM</span></th>
			<th width="4%" align="center"><span title="Đã thanh toán / Đã thu">DTT</span></th>
			<th width="4%" align="center"><span title="Recheck">RCE</span></th>
			<th width="4%" align="center"><span title="Recall">RCA</span></th>
			<th width="4%" align="center"><span title="Xuất hóa đơn đầu vào">HDV</span></th>
			<th width="4%" align="center"><span title="Xuất hóa đơn đầu ra">HDR</span></th>
			<th width="4%" align="center"><span title="Giao vé">GVE</span></th>
			<th width="4%" align="center"><span title="Checkin">CKI</span></th>
			<th width="4%" align="center"><span title="Đối chiếu công nợ">DCN</span></th>
			<th width="4%" align="center"><span title="Tạo hoàn vé">THV</span></th>
			<th width="4%" align="center"><span title="Lập phiếu chi">LPC</span></th>
			<th width="4%" align="center"><span title="Lập phiếu thu">LPT</span></th>
			<th width="4%" align="center"><span title="Lập điều chuyển tiền">DCT</span></th>
			<th width="4%" align="center"><span title="Hỗ trợ KH">SDL</span></th>

			<!-- <th width="4%" align="center"><span title="Chuyên môn">CMN</span></th>
			<th width="4%" align="center"><span title="Hiệu quả">HQA</span></th>
			<th width="4%" align="center"><span title="Tác phong">TPG</span></th>
			<th width="4%" align="center"><span title="Bị trừ">TRU</span></th> -->

			<th width="4%" align="center"><span title="Tổng cộng">TCG</span></th>
		</tr></thead>';

		$sql = "SELECT w.name AS voucher
					  ,w.parent_id
					  ,w.parent_type
					  ,w.description
					  ,w.called
					  ,w.completed
					  ,w.paid
					  ,w.recheck
					  ,w.support
					  ,(IFNULL(w.invoice_issued,0) * 3) AS invoice_issued
					  ,w.ticket_delivery
					  ,w.checkin_journey
					  ,w.recall
					  ,w.remind
					  ,w.check_debt
					  ,w.create_repaid
					  ,w.process_repaid
					  ,w.create_payment
					  ,w.create_receipt
					  ,w.create_transfer
					  ,w.invoice_input_issued
					  ,w.manner
					  ,w.effected
					  ,w.awareness
					  ,w.minus
					  ,DATE_ADD(w.date_entered, INTERVAL 7 HOUR) AS date_entered
				FROM ec_working_process w
				WHERE w.deleted = 0 " . $sql_search . "
				ORDER BY date_entered DESC
				";

		$ttl_called = 0;
		$ttl_confirmed = 0;
		$ttl_completed = 0;
		$ttl_paid = 0;
		$ttl_recheck = 0;
		$ttl_support = 0;
		$ttl_inv_in_issued = 0;
		$ttl_inv_issued = 0;
		$ttl_delivery = 0;
		$ttl_checkin = 0;
		$ttl_recall = 0;
		$ttl_bonus = 0;
		$ttl_comdebt = 0;
		$ttl_new_repaid = 0;
		$ttl_do_repaid = 0;
		$ttl_payment = 0;
		$ttl_receipt = 0;
		$ttl_transfer = 0;
		$ttl_final = 0;

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {

			$ttl_row = $row['called'] + $row['completed'] + $row['paid'] + $row['recheck'] + $row['support']
				+ $row['invoice_input_issued'] + $row['invoice_issued'] + $row['ticket_delivery'] + $row['checkin_journey'] + $row['recall'] + $row['remind']
				+ $row['check_debt'] + $row['create_repaid'] + $row['create_payment'] +  $row['create_receipt'] + $row['create_transfer']
				+ $row['manner'] + $row['effected'] + $row['awareness']
				- $row['minus'];

			if (!empty($row['manner'])) {
				$row['voucher'] = 'Chuyên môn';
			} else if (!empty($row['effected'])) {
				$row['voucher'] = 'Hiệu quả';
			} else if (!empty($row['awareness'])) {
				$row['voucher'] = 'Ý thức';
			} else if (!empty($row['minus'])) {
				$row['voucher'] = 'Bị trừ';
			}

			$recall = ($row['recall'] != 0 || $row['remind'] != 0) ? (int)($row['recall'] + $row['remind']) : '';
			$html .= '<tr>
						<td align="center"><a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '" target="_blank" title="Xem chi tiết">' . $row['voucher'] . '</a></td>
						<td align="left">' . $row['description'] . '</td>
						<td align="center"><span title="Ngày KPI được ghi nhận">' . (date('Y-m-d H:i:s', strtotime($row['date_entered']))) . '</span></td>
						<td align="center"><span title="Called">' . ($row['called'] != 0 ? $row['called'] : '') . '</span></td>
						<td align="center"><span title="Completed">' . ($row['completed'] != 0 ? $row['completed'] : '') . '</span></td>
						<td align="center"><span title="Đã thanh toán / Đã thu">' . ($row['paid'] != 0 ? $row['paid'] : '') . '</span></td>
						<td align="center"><span title="Recheck">' . ($row['recheck'] != 0 ? $row['recheck'] : '') . '</span></td>
						<td align="center"><span title="Recall">' . $recall . '</span></td>
						<td align="center"><span title="Xuất hóa đơn đầu vào">' . ($row['invoice_input_issued'] != 0 ? $row['invoice_input_issued'] : '') . '</span></td>
						<td align="center"><span title="Xuất hóa đơn đầu ra">' . ($row['invoice_issued'] != 0 ? $row['invoice_issued'] : '') . '</span></td>
						<td align="center"><span title="Giao vé">' . ($row['ticket_delivery'] != 0 ? $row['ticket_delivery'] : '') . '</span></td>
						<td align="center"><span title="Checkin">' . ($row['checkin_journey'] != 0 ? $row['checkin_journey'] : '') . '</span></td>
						<td align="center"><span title="Đối chiếu công nợ">' . ($row['check_debt'] != 0 ? $row['check_debt'] : '') . '</span></td>
						<td align="center"><span title="Tạo hoàn vé">' . ($row['create_repaid'] != 0 ? $row['create_repaid'] : '') . '</span></td>
						<td align="center"><span title="Lập phiếu chi">' . ($row['create_payment'] != 0 ? $row['create_payment'] : '') . '</span></td>
						<td align="center"><span title="Lập phiếu thu">' . ($row['create_receipt'] != 0 ? $row['create_receipt'] : '') . '</span></td>
						<td align="center"><span title="Lập điều chuyển tiền">' . ($row['create_transfer'] != 0 ? $row['create_transfer'] : '') . '</span></td>
						<td align="center"><span title="Hỗ trợ KH">' . ($row['support'] != 0 ? $row['support'] : '') . '</span></td>
						
						<!-- <td align="center"><span title="Chuyên môn">' . ($row['manner'] != 0 ? $row['manner'] : '') . '</span></td>
						<td align="center"><span title="Hiệu quả">' . ($row['effected'] != 0 ? $row['effected'] : '') . '</span></td>
						<td align="center"><span title="Ý thức">' . ($row['awareness'] != 0 ? $row['awareness'] : '') . '</span></td>
						<td align="center"><span title="Bị trừ">' . ($row['minus'] != 0 ? $row['minus'] : '') . '</span></td> -->
						
						<td align="center"><span title="Tổng cộng">' . $ttl_row . '</span></td>
					</tr>';

			$ttl_called += (int)$row['called'];
			$ttl_confirmed += (int)$row['confirmed'];
			$ttl_completed += (int)$row['completed'];
			$ttl_paid += (int)$row['paid'];
			$ttl_recheck += (int)$row['recheck'];
			$ttl_support += (int)$row['support'];

			$ttl_inv_in_issued += (int)$row['invoice_input_issued'];
			$ttl_inv_issued += (int)$row['invoice_issued'];
			$ttl_delivery += (int)$row['ticket_delivery'];
			$ttl_checkin += (int)$row['checkin_journey'];
			$ttl_recall += (int)$recall;
			$ttl_bonus += (int)$row['bonus'];

			$ttl_comdebt += (int)$row['check_debt'];
			$ttl_new_repaid += (int)$row['create_repaid'];
			$ttl_do_repaid += (int)$row['process_repaid'];
			$ttl_payment += (int)$row['create_payment'];
			$ttl_receipt += (int)$row['create_receipt'];
			$ttl_transfer += (int)$row['create_transfer'];

			$ttl_manner += (int)$row['manner'];
			$ttl_effect += (int)$row['effected'];
			$ttl_aware += (int)$row['awareness'];
			$ttl_minus += (int)$row['minus'];

			$ttl_final += $ttl_row;
		}

		$html .= '<tr class="footer-tr">
			<td colspan="3" align="center">Tổng cộng</label></td>
			<td align="center"><span title="Called">' . $ttl_called . '</span></td>
			<td align="center"><span title="Completed">' . $ttl_completed . '</span></td>
			<td align="center"><span title="Đã thanh toán / Đã thu">' . $ttl_paid . '</span></td>
			<td align="center"><span title="Recheck">' . $ttl_recheck . '</span></td>
			<td align="center"><span title="Recall">' . $ttl_recall . '</span></td>
			<td align="center"><span title="Xuất hóa đơn đầu vào">' . $ttl_inv_in_issued . '</span></td>
			<td align="center"><span title="Xuất hóa đơn đầu ra">' . $ttl_inv_issued . '</span></td>
			<td align="center"><span title="Giao vé">' . $ttl_delivery . '</span></td>
			<td align="center"><span title="Checkin">' . $ttl_checkin . '</span></td>
			<td align="center"><span title="Đối chiếu công nợ">' . $ttl_comdebt . '</span></td>
			<td align="center"><span title="Tạo hoàn vé">' . $ttl_new_repaid . '</span></td>
			<td align="center"><span title="Lập phiếu chi">' . $ttl_payment . '</span></td>
			<td align="center"><span title="Lập phiếu thu">' . $ttl_receipt . '</span></td>
			<td align="center"><span title="Lập điều chuyển tiền">' . $ttl_transfer . '</span></td>
			<td align="center"><span title="Hỗ trợ KH">' . $ttl_support . '</span></td>

			<!-- <td align="center"><span title="Chuyên môn">' . $ttl_manner . '</span></td>
			<td align="center"><span title="Hiệu quả">' . $ttl_effect . '</span></td>
			<td align="center"><span title="Ý thức">' . $ttl_aware . '</span></td>
			<td align="center"><span title="Bị trừ">' . $ttl_minus . '</span></td> -->

			<td align="center"><span title="Tổng cộng">' . $ttl_final . '</span></td>
		</tr>';

		$html .= '</table>';
	}

	echo $html;
	exit();
}
