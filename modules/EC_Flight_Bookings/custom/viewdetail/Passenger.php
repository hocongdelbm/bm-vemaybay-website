<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Passenger, baggage, and passenger-change rendering.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait PassengerTrait
{
	private function getPassengerRowsForDetail()
	{
		$rows = $this->fetchPassengerRowsForDetail();

		return [
			'original' => $this->sortOriginalPassengerRows($this->filterOriginalPassengerRows($rows)),
			'edited' => $this->sortEditedPassengerRows($this->filterEditedPassengerRows($rows)),
		];
	}

	private function fetchPassengerRowsForDetail()
	{
		$res = $this->queryPassengerRowsForDetail();
		$rows = [];

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$rows[] = $row;
		}

		return $rows;
	}

	private function filterOriginalPassengerRows($rows)
	{
		return array_values(array_filter($rows, function ($row) {
			return !$this->isEditedPassengerRow($row);
		}));
	}

	private function filterEditedPassengerRows($rows)
	{
		return array_values(array_filter($rows, function ($row) {
			return $this->isEditedPassengerRow($row);
		}));
	}

	private function isEditedPassengerRow($row)
	{
		return (int)($row['add_type'] ?? 0) === 2;
	}

	private function sortOriginalPassengerRows($rows)
	{
		usort($rows, function ($a, $b) {
			$typeCompare = (int)$a['type'] <=> (int)$b['type'];
			if ($typeCompare !== 0) {
				return $typeCompare;
			}

			return strcmp((string)$a['date_entered'], (string)$b['date_entered']);
		});

		return $rows;
	}

	private function sortEditedPassengerRows($rows)
	{
		usort($rows, function ($a, $b) {
			$groupCompare = (int)$a['go_with'] <=> (int)$b['go_with'];
			if ($groupCompare !== 0) {
				return $groupCompare;
			}

			return (int)$a['type'] <=> (int)$b['type'];
		});

		return $rows;
	}

	private function renderPassengerTableHeader()
	{
		return '<table id="tbl_pax" border="0" cellpadding="0" cellspacing="0" class="table-config table-pax table-details__booking">
				<thead>
					<tr>
						<th scope="col" width="2%"><input type="checkbox" id="select-all-passengers"/></th>
						<th scope="col" width="3%">STT</th>
						<th scope="col" width="7%">Loại</th>
						<th scope="col" width="7%">Giới tính</th>
						<th scope="col" width="16%">Họ tên</th>
						<th scope="col" width="8%">Ngày sinh</th>
						<th scope="col" width="12%">CCCD/Passport</th>
						<th scope="col" width="10%">Số vé đi</th>
						<th scope="col" width="10%">Số vé về</th>
						<th scope="col" width="7%">PNR đi</th>
						<th scope="col" width="7%">PNR về</th>
					</tr>
				</thead><tbody>';
	}

	private function queryPassengerRowsForDetail()
	{
		$sql = "SELECT p.id
				,p.name
				,p.salutation
				,p.birthday
				,p.type
				,p.eticket_outbound
				,p.eticket_inbound
				,p.eluggage_outbound
				,p.eluggage_inbound
				,p.pnr_outbound
				,p.pnr_inbound
				,p.direction
				,p.luggage_index_outbound
				,p.luggage_index_inbound
				,p.luggage_price
				,p.luggage_price_inbound
				,p.luggage_purchase_no_vat
				,p.vat_luggage_purchase
				,p.luggage_purchase
				,p.luggage_purchase_text
				,p.luggage_purchase_inbound_no_vat
				,p.vat_luggage_purchase_inbound
				,p.luggage_purchase_inbound
				,p.luggage_purchase_text_inbound
				,p.hand_baggage_outbound
				,p.hand_baggage_inbound
				,p.supplier_id
				,IF(p.supplier_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.id=p.supplier_id AND a.deleted=0 LIMIT 1), '') AS supplier
				,p.supplier_inbound_id
				,IF(p.supplier_inbound_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.id=p.supplier_inbound_id AND a.deleted=0 LIMIT 1), '') AS supplier_inbound
				,p.add_type
				,p.parent_detail_id
				,p.date_entered
				,p.go_with
				,p.cic
				,p.passport_number
				,p.description
				,(
					SELECT ticket_class FROM ec_booking_itineraries
					WHERE deleted = 0 AND direction = 0
						AND booking_id = p.booking_id
						AND (assigned_user_id = p.id OR assigned_user_id IS NULL OR assigned_user_id = '')
					ORDER BY sabre_logs DESC
					LIMIT 1
				) AS ticketClassOutbound
				,(
					SELECT ticket_class FROM ec_booking_itineraries
					WHERE deleted = 0 AND direction = 1
						AND booking_id = p.booking_id
						AND (assigned_user_id = p.id OR assigned_user_id IS NULL OR assigned_user_id = '')
					ORDER BY sabre_logs DESC
					LIMIT 1
				) AS ticketClassInbound
				,(
					SELECT name
					FROM ec_booking_passengers
					WHERE id = p.parent_detail_id
				) AS old_name
			FROM ec_booking_passengers p
			WHERE p.booking_id = '{$this->bean->id}'
				AND p.deleted = 0
				AND (p.add_type IS NULL OR p.add_type != 1)";

		$res = $this->bean->db->query($sql);

		return $res;
	}

	private function renderOriginalPassengerRows($rows, $date_format, $app_list_strings)
	{
		$html = '';
		foreach ($rows as $i => $row) {
			// Render từng hành khách gốc kèm dòng hành lý tương ứng.
			$html .= $this->renderOriginalPassengerRow($row, $i, $date_format, $app_list_strings);
		}

		return $html;
	}

	private function renderOriginalPassengerRow($row, $i, $date_format, $app_list_strings)
	{
			$html = '';
			$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';

			$hide_cic = ($this->bean->ticket_type == 2 || $row['type'] == 2 || empty($row['cic'])) ? ' style="display:none" ' : '';
			$hide_passport = empty($row['passport_number']) ? ' style="display:none" ' : '';
			if (!empty($row['passport_number']) && empty($row['cic']) && $this->bean->ticket_type == 1 && $row['type'] != 2)
				$hide_cic = ' style="display:none" ';

			// Line 1
			$html .= '<tr class="psg-line ' . $even_or_odd . '" data-id="' . $row['id'] . '">
						<td data-label="Autobook" class="text-center">
							<input type="checkbox" name="check-passenger" class="check-passenger" data-id="' . $row['id'] . '" data-type="' . $row['type'] . '" title="Autobook" />
						</td>
						<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
						<td data-label="Loại HK" class="passenger_type text-center" data="' . $row['type'] . '" class="text-center">' . $app_list_strings['passenger_type_list'][(int) $row['type']] . '</td>
						<td data-label="Danh xưng" class="passenger_salutation text-center" data="' . $row['salutation'] . '" class="text-center">' . $app_list_strings['passenger_salutation_list'][(int) $row['salutation']] . '</td>
						<td data-label="Họ tên" class="passenger_name text-start">
							<p class="fullname">' . $row['name'] . '</p>
						</td>
						<td data-label="Ngày sinh" class="passenger_birthdate text-center">
							<p class="birthdate">' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date($date_format, strtotime($row['birthday'])) : '') . '</p>
						</td>
						<td data-label="Giấy tờ" class="passenger_id text-center">
							<p class="cic text-nowrap" data="' . $row['cic'] . '" ' . $hide_cic . ' title="CCCD">
								<span style="letter-spacing:0.65px">' . $row['cic'] . '</span>
							</p>
							<p class="passport text-nowrap" data="' . $row['passport_number'] . '" ' . $hide_passport . ' title="Passport">
								<span style="letter-spacing:0.65px">' . $row['passport_number'] . '</span>
							</p>
						</td>
						<td data-label="Số vé đi" class="text-center" class="eticket_outbound" content="' . strtoupper($row['eticket_outbound']) . '" row_no="' . $row['id'] . '">
							' . strtoupper($row['eticket_outbound']) . '
							<input type="hidden" name="eticket_outbound[]" id="eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '"  />
						</td>
						<td data-label="Số vé về" class="text-center" class="eticket_inbound" content="' . strtoupper($row['eticket_inbound']) . '" row_no="' . $row['id'] . '">
							' . strtoupper($row['eticket_inbound']) . '
							<input type="hidden" name="eticket_inbound[]" id="eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '"  />
						</td>
						<td data-label="PNR đi" class="text-center">
							' . strtoupper($row['pnr_outbound']) . '
							<input type="hidden" name="pnr_outbound[]" id="pnr_outbound' . $i . '" value="' . strtoupper($row['pnr_outbound']) . '"  />
						</td>
						<td data-label="PNR về" class="text-center">
							' . strtoupper($row['pnr_inbound']) . '
							<input type="hidden" name="pnr_inbound[]" id="pnr_inbound' . $i . '" value="' . strtoupper($row['pnr_inbound']) . '"  />
						</td>
					</tr>';

			// Line 2 (Baggage)
			$row['bookingName']         = $this->bean->name ?? '';
			$row['createdBy']             = $this->bean->created_by ?? '';
			$row['airlineCodeOutbound'] = $this->_outbound_airline ?? '';
			$row['ticketClassOutbound'] = $this->_outbound_ticket_class ?? '';
			$row['airlineCodeInbound']  = $this->_inbound_airline ?? '';
			$row['ticketClassInbound']  = $this->_inbound_ticket_class ?? '';
			$html .= $this->bean->generatePassengerBaggageInfo($row, $i);


		return $html;
	}

	private function renderEditedPassengerRows($rows)
	{
		// Các dòng hành khách phát sinh do đổi tên/code vé/hành lý, được gom nhóm theo lần thay đổi go_with.
		$row_count = count($rows);
		$current_group_html = $rows_html = $result_html = '';
		$i = $k = 0;
		$pass_order = 0;
		$changed_names = [];

		foreach ($rows as $row) {
			// Khi gặp lần thay đổi mới, đóng group cũ trước khi mở group mới.
			if ($this->isNewEditedPassengerGroup($pass_order, $row) && !empty($current_group_html)) {
				$result_html .= $this->renderEditedPassengerGroup($current_group_html, $rows_html, $changed_names);
			}

			if ($this->isNewEditedPassengerGroup($pass_order, $row)) {
				// Header group hiển thị "Lần thay đổi thứ..." và nút tạo phiếu thu phát sinh nếu cần.
				$pass_order = $row['go_with'];
				$changed_names = [];
				$current_group_html = '';
				$rows_html = '';
				$i = 0;

				$current_group_html = $this->renderEditedPassengerGroupHeader($row);
			}

			$changed_names = $this->appendChangedPassengerName($changed_names, $row);
			$rows_html .= $this->renderEditedPassengerRowWithBaggage($row, $i);

			$i++;
			$k++;

			if ($k == $row_count && !empty($current_group_html)) {
				// Đóng group cuối cùng sau khi duyệt hết dữ liệu.
				$result_html .= $this->renderEditedPassengerGroup($current_group_html, $rows_html, $changed_names);
			}
		}

		return $result_html;
	}

	private function isNewEditedPassengerGroup($currentGroup, $row)
	{
		return $currentGroup != $row['go_with'];
	}

	private function renderEditedPassengerGroup($groupHeaderHtml, $rowsHtml, $changedNames)
	{
		if (!empty($changedNames)) {
			$groupHeaderHtml .= '<span>Có ' . count($changedNames) . ' hành khách đổi tên, chi tiết: ' . implode(', ', $changedNames) . '</span>';
		}

		return $groupHeaderHtml . '</td></tr>' . $rowsHtml;
	}

	private function appendChangedPassengerName($changedNames, $row)
	{
		if (trim((string) $row['old_name']) != trim((string) $row['name'])) {
			// Ghi nhận danh sách hành khách đổi tên để show summary trong header group.
			$changedNames[] = '<font color="blue">' . $row['old_name'] . '</font> <span style="font-size: 16px;">&rarr;</span> ' . $row['name'];
		}

		return $changedNames;
	}

	private function renderEditedPassengerRowWithBaggage($row, $i)
	{
		$html = $this->renderEditedPassengerRow($row, $i);

		$row['bookingName'] = $this->bean->name ?? '';
		$row['createdBy'] = $this->bean->created_by ?? '';
		$row['airlineCodeOutbound'] = $this->bean->airline ?? '';
		$row['airlineCodeInbound'] = $this->bean->airline_inbound ?? '';

		return $html . $this->bean->generatePassengerBaggageInfo($row, $i);
	}

	private function renderEditedPassengerGroupHeader($row)
	{
		$loaithu = ($row['luggage_purchase'] > 0 || $row['luggage_purchase_inbound'] > 0 || $row['luggage_price'] > 0 || $row['luggage_price_inbound'] > 0) ? 5 : 4;
		$noidungthu = $loaithu == 5 ? "Thu tiền hành lý booking {$this->bean->name}" : "Thu tiền đổi thông tin chuyến bay booking {$this->bean->name}";
		$form = '<form name="formCreateReceiptVoucher" action="index.php" method="post" target="_blank" style="float:right;">';
		$form .= '<input type="hidden" name="module" value="EC_Receipt_Voucher" />';
		$form .= '<input type="hidden" name="action" value="EditView" />';
		$form .= '<input type="hidden" name="booking_name" value="' . $this->bean->name . '" />';
		$form .= '<input type="hidden" name="booking_id" value="' . $this->bean->id . '" />';
		$form .= '<input type="hidden" name="guest_phone" value="' . $this->bean->phone . '" />';
		$form .= '<input type="hidden" name="guest_name" value="' . $this->bean->contact_name . '" />';
		$form .= '<input type="hidden" name="go_with" value="' . $row['go_with'] . '" />';
		$form .= '<input type="hidden" name="loai_thu" value="' . $loaithu . '" />';
		$form .= '<input type="hidden" name="description" value="' . $noidungthu . '" />';
		$form .= '<input type="hidden" name="receipt_type" value="credit_transfer" />';
		$form .= '<input type="submit" name="btnCreateReceiptVoucher" id="btnCreateReceiptVoucher" value="Tạo phiếu thu" title="Tạo phiếu thu" class="btn btn-primary" style="cursor:pointer; font-size:13px!important;" />';
		$form .= '</form>';

		return '<tr class="edited_pass_group"><td colspan="13" class="bg-yellow"><b>Lần thay đổi thứ ' . $row['go_with'] . ': </b>' . $form;
	}

	private function renderEditedPassengerRow($row, $i)
	{
		global $app_list_strings;

		$birthday = isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date('d-m-Y', strtotime($row['birthday'])) : '';
		return '<tr class="psg-line edited_pass_line" data-id="' . $row['id'] . '" data-times-change="' . $row['go_with'] . '">
				<td class="hide-mobile text-center" style="vertical-align: middle;">
					<input type="checkbox" name="check-passenger[]" class="check-passenger" data-id="' . $row['id'] . '" value="' . $row['id'] . '" title="Select Itinerary" style="cursor: pointer;">
				</td>
				<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
				<td data-label="Loại HK" class="text-center passenger_type">' . $app_list_strings['passenger_type_list'][$row['type']] . '</td>
				<td data-label="Danh xưng" class="text-center passenger_salutation">' . $app_list_strings['passenger_salutation_list'][$row['salutation']] . '</td>
				<td data-label="Họ tên" class="text-start passenger_name">
					<div class="d-flex">
						<p class="fullname" style="margin-right: auto">' . $row['name'] . '</p>
						<svg xmlns="http://www.w3.org/2000/svg" class="edit_pass_row cursor-pointer" data-id="' . $row['id'] . '" width="20" height="20" viewBox="0 0 24 24" style="#202020;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
					</div>
				</td>
				<td data-label="Ngày sinh" class="text-center passenger_birthday">' . $birthday . '</td>
				<td data-label="Giấy tờ" class="passenger_id text-start"></td>
				<td data-label="Số vé đi" class="text-center eticket_outbound" content="' . strtoupper($row['eticket_outbound']) . '" row_no="' . $row['id'] . '">' . strtoupper($row['eticket_outbound']) . '<input type="hidden" name="eticket_outbound[]" id="eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '" /></td>
				<td data-label="Số vé về" class="text-center eticket_inbound" content="' . strtoupper($row['eticket_inbound']) . '" row_no="' . $row['id'] . '">' . strtoupper($row['eticket_inbound']) . '<input type="hidden" name="eticket_inbound[]" id="eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '" /></td>
				<td data-label="PNR đi" class="text-center">' . strtoupper($row['pnr_outbound']) . '<input type="hidden" name="pnr_outbound[]" id="pnr_outbound' . $i . '" value="' . strtoupper($row['pnr_outbound']) . '" /></td>
				<td data-label="PNR về" class="text-center">' . strtoupper($row['pnr_inbound']) . '<input type="hidden" name="pnr_inbound[]" id="pnr_inbound' . $i . '" value="' . strtoupper($row['pnr_inbound']) . '" /></td>
			</tr>';
	}

	/**
	 * Render table default receipt voucher
	 * @return string HTML
	 */

	public function getPassengerAndBaggage($booking_id)
	{
		if (is_null($booking_id) || empty($booking_id)) return ['passenger' => '', 'baggage' => ''];

		$adt = $chd = $inf = 0;
		$totalPackDep = $totalWeightDep = 0;
		$totalPackRet = $totalWeightRet = 0;
		$sql = "SELECT type
					,IFNULL(luggage_index_outbound, '') AS luggage_index_outbound
					,IFNULL(luggage_index_inbound, '') AS luggage_index_inbound
					,IFNULL(luggage_purchase_text, '') AS luggage_purchase_text
					,IFNULL(luggage_purchase_text_inbound, '') AS luggage_purchase_text_inbound
				FROM ec_booking_passengers
				WHERE booking_id = '{$booking_id}'
					AND deleted = 0";

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ($row['type'] == '0') $adt++;
			elseif ($row['type'] == '1') $chd++;
			elseif ($row['type'] == '2') $inf++;

			// Hành lý có sẵn
			if (!empty($row['luggage_index_outbound'])) {
				[$pack, $weight] = ECFlightBookingViewDetailSupportHelpers::parseBaggageNumbers(Baggage::renderAvailableBaggage($row['luggage_index_outbound']));
				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_index_inbound'])) {
				[$pack, $weight] = ECFlightBookingViewDetailSupportHelpers::parseBaggageNumbers(Baggage::renderAvailableBaggage($row['luggage_index_inbound']));
				$totalPackRet += $pack;
				$totalWeightRet += $weight;
			}

			// Hành lý mua thêm
			if (!empty($row['luggage_purchase_text'])) {
				[$pack, $weight] = ECFlightBookingViewDetailSupportHelpers::parseBaggageNumbers($row['luggage_purchase_text']);
				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_purchase_text_inbound'])) {
				[$pack, $weight] = ECFlightBookingViewDetailSupportHelpers::parseBaggageNumbers($row['luggage_purchase_text_inbound']);
				$totalPackRet += $pack;
				$totalWeightRet += $weight;
			}
		}

		$pass = "$adt người lớn";
		if ($chd > 0) $pass .= ", $chd trẻ em";
		if ($inf > 0) $pass .= ", $inf em bé";

		$bag = '';
		if ($totalPackDep * $totalWeightDep != 0) $bag .= "{$totalPackDep} kiện đi (tổng {$totalWeightDep}kg)";
		elseif ($totalPackDep > 0) $bag .= "{$totalPackDep} kiện đi";
		elseif ($totalWeightDep > 0) $bag .= "{$totalWeightDep}kg lượt đi";

		if (!empty($bag)) $bag .= ', ';
		if ($totalPackRet * $totalWeightRet != 0) $bag .= "{$totalPackRet} kiện về (tổng {$totalWeightRet}kg)";
		elseif ($totalPackRet > 0) $bag .= "{$totalPackRet} kiện về";
		elseif ($totalWeightRet > 0) $bag .= "{$totalWeightRet}kg lượt về";

		if (empty($bag)) $bag = "Không";
		return ['passenger' => $pass, 'baggage' => trim($bag)];
	}

	// Get zalo id

	private function resolvePassengerIdChain($passengerId)
	{
		$ids       = [];
		$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $passengerId);
		$bookingId = $this->bean->db->quote($this->bean->id);
		$maxDepth  = 10;

		for ($i = 0; $i < $maxDepth; $i++) {
			if (empty($currentId) || in_array($currentId, $ids)) break;
			$ids[] = $currentId;

			$sql = "SELECT parent_detail_id
                FROM ec_booking_passengers
                WHERE id = '$currentId'
                  AND booking_id = '$bookingId'
                  AND deleted = 0
                LIMIT 1";
			$res = $this->bean->db->query($sql);
			$row = $this->bean->db->fetchByAssoc($res);

			if (!$row || empty($row['parent_detail_id'])) break;
			$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $row['parent_detail_id']);
		}

		return $ids; // [ID_RENAME_3, ID_RENAME_1, ID_GỐC]
	}
}
