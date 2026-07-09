<?php
if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

/**
 * Custom detail panels.
 */
trait DetailPanelsTrait
{
	/**
	 * Hành trình (itinerary table): dùng các helper trong ItineraryTrait.
	 */
	public function populateLineItineraries()
	{
		// Iti header
		$html = '<table id="itinerary_tbl" border="0" cellpadding="0" cellspacing="0" class="table-config table-itinerary table-details__booking"> 
					<thead>
						<tr>
							<th scope="col" width="3%"></th> 
							<th scope="col" width="3%">STT</th>
							<th scope="col" width="8%">Chiều</th>
							<th scope="col" width="8%">Mã hãng</th>
							<th scope="col" width="7%">Số hiệu</th>
							<th scope="col" width="7%">Hạng vé</th>
							<th scope="col" width="7%">Nơi đi</th>
							<th scope="col" width="7%">Nơi đến</th>
							<th scope="col" width="10%">Ngày giờ đi</th>
							<th scope="col" width="10%">Ngày giờ đến</th>
							<th scope="col" width="10%">Hạn giữ chỗ</th>
							<th scope="col" width="8%">Giá cơ bản</th>
							<th scope="col">&nbsp;</th>
						</tr>
					</thead>';

		$itineraryRows = $this->getItineraryRowsForDetail();

		$html .= $this->renderOriginalItineraryRows($itineraryRows['original']);
		$html .= $this->renderEditedLineItineraries($itineraryRows['edited']);
		$html .= '</table>';

		/* POPUP LÝ DO THẮNG THUA */
		$html .= $this->populateWinLoseTemplate();

		/* POPUP WORKING PROCESS NOTE */
		$html .= $this->populateWorkingProcessNote();

		/* POPUP REMIND */
		$html .= $this->populateRemindTemplate();

		/* POPUP CHECKIN NOTE */
		$html .= $this->populateCheckinNoteModal();

		return $html;
	}

	/**
	 * Hành khách (passenger table): dùng các helper trong PassengerTrait.
	 */
	public function populateLinePassengers()
	{
		global $app_list_strings, $timedate;

		// Date format
		$date_format = $timedate->get_date_format();

		// Passenger rows
		$passengerRows = $this->getPassengerRowsForDetail();

		// Passenger header
		$html = $this->renderPassengerTableHeader();

		// Original passengers
		$html .= $this->renderOriginalPassengerRows($passengerRows['original'], $date_format, $app_list_strings);

		// Edited passengers
		$html .= $this->renderEditedPassengerRows($passengerRows['edited']);
		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Chi tiết vé (ticket detail table): dòng giá vé theo hành khách + chiều bay.
	 */
	public function populateLineDetails()
	{
		global $locale, $app_list_strings;
		$sep = my_get_number_separators();

		$html = '<table id="line_details_tbl" border="0" cellpadding="0" cellspacing="0" class="table-config table-line-details table-details__booking">
					<thead>
						<tr>
							<th scope="col" width="3%"></th>
							<th scope="col" width="3%">STT</th>
							<th scope="col" width="7%">Chiều</th>
							<th scope="col" width="7%">Loại HK</th>
							<th scope="col" width="4%">SL</th>
							<th scope="col" width="8%">Giá cơ bản</th>
							<th scope="col" width="6%">VAT</th>
							<th scope="col" width="8%">Phí sân bay</th>
							<th scope="col" width="8%">Phí admin</th>
							<th scope="col" width="7%" title="Phí dịch vụ">Phí DV</th>
							<th scope="col" width="8%">Thành tiền</th>
							<th scope="col" width="8%">Giá mua</th>
							<th scope="col" width="8%">Chiết khấu</th>
							<th scope="col" width="8%">Phí xuất vé</th>
							<th scope="col">NCC</th>
						</tr>
					</thead>';

		$i = 0;
		$totals = [
			'qty' => 0,
			'supplier_discount' => 0,
			'basic_amount' => 0,
			'vat_amount' => 0,
			'airport_amount' => 0,
			'admin_amount' => 0,
			'service_amount' => 0,
			'issue_fee' => 0,
		];

		$sql = "SELECT d.id,
					d.passenger_type,
					d.quantity,
					d.unit_price,
					d.tax_and_fee,
					d.total_price,
					d.description,
					d.direction,
					d.service_fee,
					d.admin_fee,
					d.vat_admin,
					d.admin_fee_no_vat,
					d.airport_fee,
					d.total_bought_price,
					d.discount_amount,
					d.fee_bought,
					d.supplier_id,
					IF(d.supplier_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.deleted=0 AND a.id=d.supplier_id LIMIT 1), '') AS supplier,
					d.supplier_discount
				FROM ec_booking_details d
				WHERE d.booking_id = '{$this->bean->id}' AND d.deleted = 0
				ORDER BY d.direction, d.passenger_type, d.date_entered ";

		$res = $this->bean->db->query($sql);

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$admin_fee_inf = '';
			if ($row['admin_fee_no_vat'] > 0) {
				$admin_fee_inf = '<div class="admin_fee_no_vat--wrap d-flex align-items-center justify-content-between">
								<span class="text-start">TVAT:</span>
								<span class="text-end">' . format_number($row['admin_fee_no_vat']) . '</span>
							</div>
							<div class="vat_admin--wrap d-flex align-items-center justify-content-between">
								<span class="text-start">VAT:</span>
								<span class="text-end">' . format_number($row['vat_admin']) . '</span>
							</div>';
			}

			$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';
			$html .= '<tr class="' . $even_or_odd . '">
						<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-detail" class="check-journey" data-id="' . $row['id'] . '" title="Autobook" /></td>
						<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
						<td data-label="Chiều" class="text-center">' . $app_list_strings['bk_direction_list'][(int) $row['direction']] . '</td>
						<td data-label="Loại HK" class="text-center">' . $app_list_strings['passenger_type_list'][(int) $row['passenger_type']] . '</td>
						<td data-label="SL" class="text-center">' . format_number($row['quantity']) . '</td>
						<td data-label="Giá cơ bản" class="text-end">' . format_number($row['unit_price']) . '</td>
						<td data-label="VAT" class="text-end">' . format_number($row['tax_and_fee']) . '</td>
						<td data-label="Phí sân bay" class="text-end">' . format_number($row['airport_fee']) . '</td>
						<td data-label="Phí admin" class="text-end"><div class="admin_fee">' . format_number($row['admin_fee']) . '</div>' . $admin_fee_inf . '</td>
						<td data-label="Phí dịch vụ" class="text-end">' . format_number($row['service_fee']) . '</td>
						<td data-label="Thành tiền" class="text-end" title="Đã gồm số lượng">' . format_number($row['total_price']) . '</td>
						<td data-label="Giá mua" class="text-end" title="Đã gồm số lượng">
							<input type="hidden" name="check_total_bought_price[]" id="check_total_bought_price' . $i . '" value="' . format_number($row['total_bought_price']) . '" />
							' . (ACLController::checkAccess('Bugs', 'list', true) ? format_number($row['total_bought_price']) : '&nbsp;') . '
						</td>
						<td data-label="Chiết khấu" class="text-end">' . format_number($row['supplier_discount']) . '</td>
						<td data-label="Phí xuất vé" class="text-end">' . format_number($row['fee_bought']) . '</td>
						<td data-label="NCC" class="text-start">
							<input type="hidden" name="check_supplier_id[]" id="check_supplier_id' . $i . '" value="' . $row['supplier_id'] . '" />
							<a href="index.php?module=Accounts&action=DetailView&record=' . $row['supplier_id'] . '" target="_blank">' . $row['supplier'] . '</a>
						</td>
					</tr>';

			$totals['qty'] += $row['quantity'];
			$totals['supplier_discount'] += $row['supplier_discount'];
			$totals['basic_amount'] += $row['unit_price'];
			$totals['vat_amount'] += $row['tax_and_fee'];
			$totals['airport_amount'] += $row['airport_fee'];
			$totals['admin_amount'] += $row['admin_fee'];
			$totals['service_amount'] += $row['service_fee'];
			$totals['issue_fee'] += $row['fee_bought'];

			$i++;
		}

		$html .= '<tr class="footer-tr">
					<td class="hide-mobile show-landscape" colspan="4">Tổng:
						<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
						<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
						<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />
						<input type="hidden" id="supplier_option_val" value="' . str_replace('"', "'", myGetSelectOptionsWithDbExt("Accounts", "ticker_symbol", "", "id", "AND account_type='Supplier' AND is_stop_tracking=0")) . '">
					</td>
					<td data-label="Tổng số vé" class="text-center">' . format_number($totals['qty']) . '</td>
					<td data-label="Giá cơ bản" class="hide-mobile text-end show-landscape">' . format_number($totals['basic_amount']) . '</td>
					<td data-label="VAT" class="hide-mobile text-end show-landscape">' . format_number($totals['vat_amount']) . '</td>
					<td data-label="Phí sân bay" class="hide-mobile text-end show-landscape">' . format_number($totals['airport_amount']) . '</td>
					<td data-label="Phí admin" class="hide-mobile text-end show-landscape">' . format_number($totals['admin_amount']) . '</td>
					<td data-label="Phí DV" class="hide-mobile text-end show-landscape">' . format_number($totals['service_amount']) . '</td>
					<td data-label="Tổng thành tiền" class="text-end into_money">' . format_number($this->bean->subtotal_amount) . '</td>
					<td data-label="Tổng giá mua" class="text-end purchase_price">' . format_number($this->bean->total_bought_amount) . '</td>
					<td data-label="Tổng chiết khấu" class="text-end supplier_discount">' . format_number($totals['supplier_discount']) . '</td>
					<td data-label="Phí xuất vé" class="text-end hide-mobile show-landscape">' . format_number($totals['issue_fee']) . '</td>
					<td class="text-end hide-mobile show-landscape"></td>
				</tr>
			</table>';

		return $html;
	}

	/**
	 * Chứng từ liên quan: phiếu thu + phiếu hoàn của booking.
	 */
	public function populateLineRelateVoucher()
	{
		global $db;

		$booking_id = $db->quote($this->bean->id);

		$sql = "(
					SELECT
						id,
						name,
						rv_status as status,
						'receipt_voucher_status_list' AS status_list,
						'receipt_voucher_status_color_list' AS status_list_color,
						amount,
						description,
						ngaychungtu,
						'EC_Receipt_Voucher' AS parent_type
					FROM ec_receipt_voucher
					WHERE booking_id = '$booking_id'
					AND deleted = 0
				)
				UNION ALL
				(
					SELECT
						id,
						name,
						tinhtrang AS status,
						'tinhtranghoanve_list' AS status_list,
						'tinhtranghoanvecolor_list' AS status_list_color,
						tongtienkhach AS amount,
						description,
						ngaychungtu,
						'EC_HoanVe' AS parent_type
					FROM ec_hoanve
					WHERE booking_id = '$booking_id'
					AND deleted = 0
					)
					ORDER BY ngaychungtu DESC";

		$res = $db->query($sql);

		$parentTypeLabels = [
			'EC_HoanVe' => 'Phiếu hoàn',
			'EC_Receipt_Voucher' => 'Phiếu thu',
		];

		$html = '<table id="tbl_pax" border="0" cellpadding="0" cellspacing="0" class="table-config table-details__booking">
                    <thead>
                        <tr>
                            <th scope="col" width="5%">STT</th>
                            <th scope="col" width="12%">Ngày chứng từ</th>
                            <th scope="col" width="12%">Loại phiếu</th>
                            <th scope="col" width="12%">Tên phiếu</th>
                            <th scope="col" width="12%">Tình trạng</th>
                            <th scope="col" width="12%">Số tiền</th>
                            <th scope="col">Ghi chú</th>
                        </tr>
                    </thead>';

		$i = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$i++;
			$html .= '<tr>
						<td data-label="STT" class="text-center">' . $i . '</td>
						<td data-label="Ngày chứng từ" class="text-center">
                            ' . date('d-m-Y', strtotime($row['ngaychungtu'])) . '
						</td>
						<td data-label="Loại phiếu" class="text-center">
							' . ($parentTypeLabels[$row['parent_type']] ?? '') . '
						</td>
						<td data-label="Tên phiếu" class="text-center">
							<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['id'] . '" target="_blank">
								' . $row['name'] . '
							</a>
						</td>
						<td data-label="Tình trạng" class="text-center fw-semibold" style="color:' . $GLOBALS['app_list_strings'][$row['status_list_color']][$row['status']] . ';">' . $GLOBALS['app_list_strings'][$row['status_list']][$row['status']] . '</td>
						<td data-label="Số tiền" class="text-center">' . format_number($row['amount']) . '</td>
						<td data-label="Ghi chú" class="text-start text-wrap">' . $row['description'] . '</td>
					</tr>';
		}

		if ($i === 0) {
			$html .= '<tr>
						<td colspan="8" class="text-start fw-semibold">Không có chứng từ liên quan.</td>
					</tr>';
		}

		$html .= '</table>';

		return $html;
	}
}
