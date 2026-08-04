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
			</thead>
			<tbody>';
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
				,p.passport_type
				,p.passport_number
				,p.passport_nationality
				,p.passport_issue_country
				,p.passport_issue_date
				,p.passport_expired_date
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

			$passportTrigger = $this->renderPassportInfoTrigger($row);

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
							' . $passportTrigger . '
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
			$row['createdBy']           = $this->bean->created_by ?? '';
			$row['airlineCodeOutbound'] = $this->_outbound_airline ?? '';
			$row['ticketClassOutbound'] = $this->_outbound_ticket_class ?? '';
			$row['airlineCodeInbound']  = $this->_inbound_airline ?? '';
			$row['ticketClassInbound']  = $this->_inbound_ticket_class ?? '';
			$html .= $this->bean->generatePassengerBaggageInfo($row, $i);


		return $html;
	}

	/**
	 * Renders the CCCD/Passport cell as a single trigger "card" button. Clicking it opens
	 * the passport info popup (see TemplatesTrait::populatePassportInfoModal) instead
	 * of showing the passport number value directly.
	 */
	private function renderPassportInfoTrigger(array $row) {
		$displayValue = $row['passport_number'] ?? '';
		$hasValue = $displayValue !== '';
		$displayLabel = $hasValue ? $displayValue : 'Bổ sung';

		$typeLabels = ['P' => 'Passport', 'I' => 'CCCD/ID'];
		$passportType = $row['passport_type'] ?? '';
		$tooltip = ($hasValue && isset($typeLabels[$passportType])) ? $typeLabels[$passportType] : 'Nhập/Sửa thông tin giấy tờ';

		$dataAttrs = '';
		foreach (['passport_type', 'passport_number', 'passport_nationality', 'passport_issue_country', 'passport_issue_date', 'passport_expired_date'] as $field) {
			$value = $row[$field] ?? '';
			if ($value === '0000-00-00') $value = '';
			$dataAttrs .= ' data-' . $field . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
		}

		$chipClass = 'passport-chip' . ($hasValue ? '' : ' passport-chip--empty') . ($passportType === 'P' ? ' passport-chip--type-p' : '');

		return '<button type="button" class="btn-passport-info ' . $chipClass . '" data-id="' . $row['id'] . '"' . $dataAttrs . ' title="' . htmlspecialchars($tooltip, ENT_QUOTES) . '">
			<span class="passport-chip__icon">
				<svg class="passport-chip__icon-id" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
					<path d="M14 3H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm0 9H2V4h12v8z"></path>
					<circle cx="5" cy="7.4" r="1.4"></circle>
					<path d="M2.6 10.8c.3-1.1 1.4-1.9 2.4-1.9s2.1.8 2.4 1.9H2.6z"></path>
					<path d="M9 6h4v1H9zM9 8.2h4v1H9z"></path>
				</svg>
				<svg class="passport-chip__icon-passport" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M5 5C5 4.59334 4.75727 4.24338 4.40879 4.0871C4.55271 3.97374 4.72712 3.89615 4.91959 3.86865L15.7172 2.32614C16.922 2.15402 18 3.08894 18 4.30604V5.12602C17.6804 5.04375 17.3453 5 17 5H5Z" fill="#1C274D"></path>
					<path d="M9.75 13C9.75 11.7574 10.7574 10.75 12 10.75C13.2426 10.75 14.25 11.7574 14.25 13C14.25 14.2426 13.2426 15.25 12 15.25C10.7574 15.25 9.75 14.2426 9.75 13Z" fill="#1C274D"></path>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M18 6.17071C19.1652 6.58254 20 7.69378 20 9V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V5C4 5.18214 4.0487 5.35291 4.13378 5.5C4.30669 5.7989 4.62986 6 5 6H17C17.3506 6 17.6872 6.06015 18 6.17071ZM12 9.25C9.92893 9.25 8.25 10.9289 8.25 13C8.25 15.0711 9.92893 16.75 12 16.75C14.0711 16.75 15.75 15.0711 15.75 13C15.75 10.9289 14.0711 9.25 12 9.25ZM10 18.25C9.58579 18.25 9.25 18.5858 9.25 19C9.25 19.4142 9.58579 19.75 10 19.75H14C14.4142 19.75 14.75 19.4142 14.75 19C14.75 18.5858 14.4142 18.25 14 18.25H10Z" fill="#1C274D"></path>
				</svg>
			</span>
			<span class="passport-chip__body">
				<span class="passport-chip__value">' . htmlspecialchars($displayLabel, ENT_QUOTES) . '</span>
			</span>
		</button>';
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
	 * Extract the first two integers from a baggage text string.
	 * Returns [pack_count, weight_kg].
	 */

	public function parseBaggageNumbers($text)
	{
		preg_match_all('/\d+/', $text, $matches);
		return [
			(int)($matches[0][0] ?? 0),
			(int)($matches[0][1] ?? 0),
		];
	}

	/**
	 * Render table default receipt voucher
	 * @return array
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
				[$pack, $weight] = $this->parseBaggageNumbers(Baggage::renderAvailableBaggage($row['luggage_index_outbound']));
				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_index_inbound'])) {
				[$pack, $weight] = $this->parseBaggageNumbers(Baggage::renderAvailableBaggage($row['luggage_index_inbound']));
				$totalPackRet += $pack;
				$totalWeightRet += $weight;
			}

			// Hành lý mua thêm
			if (!empty($row['luggage_purchase_text'])) {
				[$pack, $weight] = $this->parseBaggageNumbers($row['luggage_purchase_text']);
				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_purchase_text_inbound'])) {
				[$pack, $weight] = $this->parseBaggageNumbers($row['luggage_purchase_text_inbound']);
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

}
