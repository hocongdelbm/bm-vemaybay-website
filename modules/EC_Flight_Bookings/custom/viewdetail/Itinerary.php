<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Itinerary rendering and itinerary-change popup data.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ItineraryTrait
{
	private function getItineraryRowsForDetail()
	{
		$rows = $this->fetchItineraryRowsForDetail();

		return [
			'original' => $this->sortOriginalItineraryRows($this->filterOriginalItineraryRows($rows)),
			'edited' => $this->sortEditedItineraryRows($this->groupEditedItineraryRows($this->filterEditedItineraryRows($rows))),
		];
	}

	private function fetchItineraryRowsForDetail()
	{
		$res = $this->queryItineraryRowsForDetail();
		$rows = [];

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$rows[] = $row;
		}

		return $rows;
	}

	private function filterOriginalItineraryRows($rows)
	{
		return array_values(array_filter($rows, function ($row) {
			return (int)$row['add_type'] === 0;
		}));
	}

	private function filterEditedItineraryRows($rows)
	{
		return array_values(array_filter($rows, function ($row) {
			return (int)$row['add_type'] === 3;
		}));
	}

	private function sortOriginalItineraryRows($rows)
	{
		usort($rows, function ($a, $b) {
			$directionCompare = (int)$a['direction'] <=> (int)$b['direction'];
			if ($directionCompare !== 0) {
				return $directionCompare;
			}

			$transitCompare = (int)$a['transit_order'] <=> (int)$b['transit_order'];
			if ($transitCompare !== 0) {
				return $transitCompare;
			}

			return strcmp((string)$a['date_entered'], (string)$b['date_entered']);
		});

		return $rows;
	}

	private function sortEditedItineraryRows($rows)
	{
		usort($rows, function ($a, $b) {
			$sabreCompare = (int)$a['sabre_logs'] <=> (int)$b['sabre_logs'];
			if ($sabreCompare !== 0) {
				return $sabreCompare;
			}

			$dateCompare = strcmp((string)$a['date_entered'], (string)$b['date_entered']);
			if ($dateCompare !== 0) {
				return $dateCompare;
			}

			return (int)$a['direction'] <=> (int)$b['direction'];
		});

		return $rows;
	}

	private function groupEditedItineraryRows($rows)
	{
		$grouped = [];

		foreach ($rows as $row) {
			$key = implode('|', [
				$row['direction'],
				$row['flight_number'],
				$row['departure'],
				$row['arrival'],
				$row['departure_date'],
				$row['sabre_logs'],
			]);

			if (!isset($grouped[$key])) {
				$row['iti_id'] = $row['id'];
				$row['pass_name'] = trim((string)$row['name']);
				$grouped[$key] = $row;
				continue;
			}

			$grouped[$key]['iti_id'] .= ',' . $row['id'];
			$grouped[$key]['pass_name'] .= ',' . trim((string)$row['name']);
		}

		return array_values($grouped);
	}

	private function renderLineItineraryTableHeader()
	{
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

		return $html;
	}

	private function getAppliedPassengerItinerariesByDirection()
	{
		// Lấy ds những hành khách còn áp dụng hành trình đặt ban đầu
		$departure_applied_pass = $this->getAppliedPassengerIti($this->bean->id, 0);
		$arrival_applied_pass = '';
		if ($this->bean->flight_type == '0') {
			$arrival_applied_pass = $this->getAppliedPassengerIti($this->bean->id, 1);
		}

		return [$departure_applied_pass, $arrival_applied_pass];
	}

	private function queryItineraryRowsForDetail()
	{
		$sql = "SELECT iti.id,
					iti.name,
					iti.description,
					iti.airline_code,
					iti.flight_number,
					iti.ticket_class,
					iti.departure,
					iti.arrival,
					iti.departure_date,
					iti.arrival_date,
					iti.base_price,
					iti.direction,
					iti.time_limit,
					iti.is_layover,
					iti.date_entered,
					iti.transit_order,
					iti.is_remind,
					iti.checkin_status,
					iti.add_type,
					iti.sabre_logs,
					iti.modified_user_id,
					bk.ticket_type,
					bk.phone AS bk_phone,
					bk.name AS bk_name,
					bk.booking_status AS booking_status
				FROM ec_booking_itineraries iti
					LEFT JOIN ec_flight_bookings bk ON bk.id = iti.booking_id
				WHERE iti.booking_id = '{$this->bean->id}' 
					AND iti.deleted = 0 
				ORDER BY iti.sabre_logs, iti.direction, iti.transit_order";

		$res = $this->bean->db->query($sql);

		return $res;
	}

	private function renderOriginalItineraryRows($rows, $date_format, $airport_list, $use_mail_eticket, $departure_applied_pass, $arrival_applied_pass)
	{
		$html = '';
		$check_dep = $check_ret = false;

		foreach ($rows as $i => $row) {
			// Render từng dòng hành trình gốc: logo hãng, giờ bay, SMS, Remind, Check-in, in vé từng chặng.
			$html .= $this->renderOriginalItineraryRow($row, $i, $date_format, $airport_list, $use_mail_eticket, $departure_applied_pass, $arrival_applied_pass, $check_dep, $check_ret);
		}

		return $html;
	}

	private function renderOriginalItineraryRow($row, $i, $date_format, $airport_list, $use_mail_eticket, $departure_applied_pass, $arrival_applied_pass, &$check_dep, &$check_ret)
	{
		global $app_list_strings;
		$html = '';
		$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';

		$airline_code = EC_Airlines::normalizeIataCode($row['airline_code']);

		$logoUrl = !$row['is_layover'] ? EC_Airlines::getLogoUrl($airline_code) : null;
		$img_src = $logoUrl ? '<img class="h-auto" style="width:40px;object-fit:contain;" src="' . $logoUrl . '" alt="' . $airline_code . '" border="0" />' : '';

		if ($this->bean->ticket_type == '2')
			$img_src .= '<br />(<b>' . $row['airline_code'] . '</b>)';
		$html .= '<tr class="' . $even_or_odd . '">';

		// Checkbox auto book
		$booking_cutoff_time = (strtotime($row['departure_date']) - time()) - 10800;
		if ($row['direction'] == '0' && $check_dep === false && $booking_cutoff_time > 0) {
			$html .= '<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="Autobook" /></td>';
			$check_dep = true;
		} else if ($row['direction'] == '1' && $check_ret === false && $booking_cutoff_time > 0) {
			$html .= '<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="Autobook" /></td>';
			$check_ret = true;
		} else
			$html .= '<td data-label="" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="" /></td>';

		$flight_number = $row['flight_number'] ?? '';
		$html .= '<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
				<td data-label="Chiều" class="text-center" id="detail_direction' . $i . '" data-direction="' . $row['direction'] . '">' . $app_list_strings['bk_direction_list'][$row['direction']] . '</td>
				<td data-label="Mã hãng" class="text-center dt_airline" id="detail_airline' . $i . '" data-airline="' . $row['airline_code'] . '">' . $img_src . '</td>
				<td data-label="Số hiệu" class="text-center">' . $flight_number . '</td>
				<td data-label="Hạng vé" class="text-center ticket_class' . $row['direction'] . '">' . $row['ticket_class'] . '</td>
				<td data-label="Nơi đi" class="text-center">' . $row['departure'] . '</td>
				<td data-label="Nơi đến" class="text-center">' . $row['arrival'] . '</td>
				<td data-label="Ngày giờ đi" class="text-center">' . (trim($row['departure_date']) != '' ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '') . '</td>
				<td data-label="Ngày giờ đến" class="text-center">' . (trim($row['arrival_date']) != '' ? date($date_format . ' H:i', strtotime($row['arrival_date'])) : '') . '</td>
				<td data-label="Hạn giữ chỗ" class="text-center">' . (trim($row['time_limit']) != '' ? date($date_format . ' H:i', strtotime($row['time_limit'])) : '') . '</td>
				<td data-label="Giá cơ bản" class="text-end">' . format_number($row['base_price']) . '</td>';

		if ($row['is_layover'] == 0 && $use_mail_eticket) {
			// REMIND BUTTON
			$remind_btn = '';
			if ($row['is_remind'] == 0 && ($row['booking_status'] == 7 || $row['booking_status'] == 8)) {
				$remind_btn .= '<div class="dropdown">
						<button class="btn btn-primary-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							Remind
						</button>
						<ul class="dropdown-menu dropdown-menu-end box-list">
							<li class="box-item">
								<a class="dropdown-item btn-remind btn-voiceip-calling btn-voiceip-calling-teco" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay</a>
							</li>
							<li class="box-item">
								<a class="dropdown-item btn-remind btn-voiceip-calling btn-voiceip-calling-zalo" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay (Zalo)</a>
							</li>
							<li class="box-item">
								<a class="dropdown-item confirm-remind" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" id="confirm-remind" href="javascript:void(0)">Đã nhắc nhở khách</a>
							</li>
						</ul>
					</div>';
			}

			// SMS BUTTON
			$journey_name = ucfirst(myRemoveUnicodeChars($airport_list[$row['departure']] ?? '')) . ' - ' . ucfirst(myRemoveUnicodeChars($airport_list[$row['arrival']] ?? ''));
			$sms_depdate = date('d/m/Y H:i', strtotime($row['departure_date']));
			$sms_btn = '<input type="button" name="btnSendSMS" value="SMS" title="Send SMS" class="btn btn-primary-2"
					direction="' . $row['direction'] . '" 
					flightno="' . $flight_number . '"
					journey="' . $journey_name . '"
					date="' . explode(' ', $sms_depdate)[0] . '"
					time="' . explode(' ', $sms_depdate)[1] . '"
					applied_pass="' . ($row['direction'] == 0 ? $departure_applied_pass : $arrival_applied_pass) . '"
				/>';

			// TT Checkin status
			$checkin_status = '';
			if ((int)$row['checkin_status'] !== 2 && in_array((int)$this->bean->booking_status, [3, 7, 8])) {
				$jour_name = $row['departure'] . '-' . $row['arrival'];
				$checkin_status = '<select class="select-box checkin_status_iti" iti_id="' . $row['id'] . '" iti_name="' . $jour_name . '" booking_id="' . $this->bean->id . '" record_name="' . $this->bean->name . '" data-notes="' . htmlspecialchars($row['description'] ?? '', ENT_QUOTES) . '">' . get_select_options_with_id($app_list_strings['booking_checkin_status_list'], (int)$row['checkin_status']) . '</select>';
			}

			$html .= '<td data-label="" class="text-center">
					<form action="index.php?print=true" method="post" name="frmPrintEticket" id="frmPrintEticket' . $i . '" target="_blank">
						<input type="hidden" name="module" value="EC_Flight_Bookings" />
						<input type="hidden" name="action" value="printeticket" />
						<input type="hidden" name="record" value="' . $this->bean->id . '" />
						<input type="hidden" name="return_module" value="EC_Flight_Bookings" />
						<input type="hidden" name="return_action" value="" />
						<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
						<input type="hidden" name="booking" value="' . $this->bean->name . '" />
						<input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
						<input type="hidden" name="contact_email" value="' . $this->bean->email . '" />
						<input type="hidden" name="contact_name" value="' . $this->bean->contact_name . '" />
						<input type="hidden" name="itinerary_id" value="' . $row['id'] . '" />
						<input type="hidden" name="direction" value="' . $row['direction'] . '" />
						<input type="hidden" name="airline_code" value="' . $row['airline_code'] . '" />
						<input type="hidden" name="ticket_type" value="' . $this->bean->ticket_type . '" />
						<div class="action-button-ticket d-flex gap-2 align-items-center justify-content-center">
							' . $sms_btn . '
							' . $remind_btn . '
							' . $checkin_status . '
						</div>
					</form>
				</td>';
		} else {
			$html .= '<td data-label="" class="text-center">&nbsp;</td>';
		}
		$html .= '</tr>';

		// Load description
		if (isset($row['description']) && !empty($row['description'])) {
			$html .= '<tr>
					<td colspan="15" style="font-style:italic;font-weight:bold">' . $row['description'] . '</td>
				</tr>';
		}

		if ($row['direction'] == '0') {
			$this->_outbound_airline = $row['airline_code'];
			$this->_outbound_ticket_class = $row['ticket_class'];
		}

		if ($row['direction'] == '1') {
			$this->_inbound_airline = $row['airline_code'];
			$this->_inbound_ticket_class = $row['ticket_class'];
		}
		$i++;

		return $html;
	}

	private function appendLineItineraryTemplates($html, $editedRows = null)
	{
		/* CHANGE FLIGHT TIME INFO */
		$html .= $this->renderEditedLineItineraries($editedRows);
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

	// Direction (0: lượt đi ; 1: lượt về)	
	private function renderEditedLineItineraries($rows)
	{
		global $timedate;

		$date_format = $timedate->get_date_format();
		$user_list = get_user_array(true, '', '', true);
		$airport_list = $this->getAirportList();
		$pass_qty = $this->countOriginalBookingPassengers();
		$html = '';
		$i = 0;
		$j = ($this->bean->flight_type == 0) ? 3 : 2;
		$order_iti = 0;
		$print_iti = 0;

		foreach ($rows as $row) {
			if ($order_iti != $row['sabre_logs']) {
				$order_iti = $row['sabre_logs'];
				$applied_pass = $this->getEditedItineraryAppliedPassengerText($row, $pass_qty);
				$html .= $this->renderEditedItineraryGroupHeader($row, $applied_pass, $user_list);
				$i = 0;
			}

			$html .= $this->renderEditedItineraryRow($row, $i, $j, $date_format, $airport_list, $applied_pass, $print_iti);

			if (!empty($row['description'])) {
				$html .= '<tr><td colspan="15" class="fw-semibold fst-italic">' . $row['description'] . '</td></tr>';
			}

			$j++;
			$i++;
		}

		return $html;
	}

	private function getAirportList()
	{
		global $app_list_strings;

		return $app_list_strings['domestic_airport_list'] + $app_list_strings['africa_airport_list'] + $app_list_strings['americas_airport_list'] + $app_list_strings['australia_airport_list'] + $app_list_strings['europe_airport_list'] + $app_list_strings['northeast_asia_airport_list'] + $app_list_strings['southeast_asia_airport_list'];
	}

	private function countOriginalBookingPassengers()
	{
		$sql_qty = "SELECT COUNT(id)
			FROM ec_booking_passengers
			WHERE deleted = 0 AND add_type IS NULL
				AND booking_id = '{$this->bean->id}'";

		return (int) $this->bean->db->getOne($sql_qty);
	}

	private function getEditedItineraryAppliedPassengerText($row, $pass_qty)
	{
		$pass_name_arr = explode(',', (string) $row['pass_name']);

		return count($pass_name_arr) == $pass_qty ? 'tất cả hành khách' : implode(', ', $pass_name_arr);
	}

	private function renderEditedItineraryGroupHeader($row, $applied_pass, $user_list)
	{
		$modified_user = $user_list[$row['modified_user_id']] ?? '';

		return '<tr class="edited_iti_group">
			<td colspan="14" class="bg-yellow">
				<b>Lần thay đổi thứ ' . $row['sabre_logs'] . ': Áp dụng cho ' . $applied_pass . '. Thay đổi bởi: ' . $modified_user . '</b>
			</td>
		</tr>';
	}

	private function renderEditedItineraryRow($row, $i, $j, $date_format, $airport_list, $applied_pass, &$print_iti)
	{
		global $app_list_strings;

		$img_src = $this->renderEditedItineraryAirlineLogo($row);
		$action_html = $this->renderEditedItineraryActionCell($row, $j, $airport_list, $applied_pass, $print_iti);

		return '<tr class="edited_iti_line">
				<td class="hide-mobile text-center" style="vertical-align: middle;">
					<input type="checkbox" name="check-itinerary[]" class="check-itinerary" data-id="' . $row['id'] . '" value="' . $row['id'] . '" title="Select Itinerary" style="cursor: pointer;">
				</td>
				<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
				<td data-label="Chiều" class="text-center">' . $app_list_strings['bk_direction_list'][$row['direction']] . '</td>
				<td data-label="Mã hãng" class="text-center">' . $img_src . '</td>
				<td data-label="Số hiệu" class="text-center">' . $row['flight_number'] . '</td>
				<td data-label="Hạng vé" class="text-center ticket_class' . $row['direction'] . '">' . $row['ticket_class'] . '</td>
				<td data-label="Nơi đi" class="text-center">' . $row['departure'] . '</td>
				<td data-label="Nơi đến" class="text-center">' . ($row['is_layover'] ? '' : $row['arrival']) . '</td>
				<td data-label="Ngày giờ đi" class="text-center">' . (trim($row['departure_date']) != '' ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '') . '</td>
				<td data-label="Ngày giờ đến" class="text-center">' . (trim($row['arrival_date']) != '' ? date($date_format . ' H:i', strtotime($row['arrival_date'])) : '') . '</td>
				' . $action_html . '
				<td class="text-center p-2">
					<svg xmlns="http://www.w3.org/2000/svg" data-id="' . $row['iti_id'] . '" class="edit_iti_row cursor-pointer" width="20" height="20" viewBox="0 0 24 24" style="fill: #2a2a2a;transform: ;msFilter:;"><path d="m18.988 2.012 3 3L19.701 7.3l-3-3zM8 16h3l7.287-7.287-3-3L8 13z"></path><path d="M19 19H8.158c-.026 0-.053.01-.079.01-.033 0-.066-.009-.1-.01H5V5h6.847l2-2H5c-1.103 0-2 .896-2 2v14c0 1.104.897 2 2 2h14a2 2 0 0 0 2-2v-8.668l-2 2V19z"></path></svg>
				</td>
			</tr>';
	}

	private function renderEditedItineraryAirlineLogo($row)
	{
		$airline_code = EC_Airlines::normalizeIataCode($row['airline_code']);
		$logoUrl = $row['is_layover'] ? null : EC_Airlines::getLogoUrl($airline_code);
		$img_src = $logoUrl ? '<img class="h-auto" style="width:40px;object-fit:contain;" src="' . $logoUrl . '" alt="' . $airline_code . '" border="0" />' : '';

		if ($row['ticket_type'] == '2') {
			$img_src .= '<br />(<b>' . $row['airline_code'] . '</b>)';
		}

		return $img_src;
	}

	private function renderEditedItineraryActionCell($row, $j, $airport_list, $applied_pass, &$print_iti)
	{
		global $app_list_strings;
		$remind_btn = '';
		$checkin_status = '';
		if ($print_iti != $row['sabre_logs']) {
			$print_iti = $row['sabre_logs'];
			if ($row['is_remind'] == 0) {
				$remind_btn = '<div class="dropdown">
						<button class="btn btn-primary-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Remind</button>
						<ul class="dropdown-menu dropdown-menu-end box-list">
							<li class="box-item"><a class="dropdown-item btn-remind btn-voiceip-calling" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" booking_name="' . $row['bk_name'] . '" phone="' . $row['bk_phone'] . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay</a></li>
							<li class="box-item"><a class="dropdown-item confirm-remind" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" id="confirm-remind" href="javascript:void(0)">Đã nhắc nhở khách</a></li>
						</ul>
					</div>';
			}

			if ((int) $row['checkin_status'] !== 2 && in_array((int) $row['booking_status'], [7, 8])) {
				$jour_name = $row['departure'] . '-' . $row['arrival'];
				$checkin_status = '<select class="select-box checkin_status_iti" iti_id="' . $row['id'] . '" iti_name="' . $jour_name . '" booking_id="' . $this->bean->id . '" record_name="' . $row['bk_name'] . '" data-notes="' . htmlspecialchars($row['description'] ?? '', ENT_QUOTES) . '">' . get_select_options_with_id($app_list_strings['booking_checkin_status_list'], (int) $row['checkin_status']) . '</select>';
			}
		}

		$sms_depdate = date('d/m/Y H:i', strtotime($row['departure_date']));
		$journey = ucfirst(myRemoveUnicodeChars($airport_list[$row['departure']] ?? '')) . ' - ' . ucfirst(myRemoveUnicodeChars($airport_list[$row['arrival']] ?? ''));
		return '<td colspan="2" class="text-center">
				<form action="index.php?print=true" method="post" name="frmPrintEticket" id="frmPrintEticket' . $j . '" target="_blank">
					<input type="hidden" name="module" value="EC_Flight_Bookings" />
					<input type="hidden" name="action" value="printeticket" />
					<input type="hidden" name="record" value="' . $this->bean->id . '" />
					<input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
					<input type="hidden" name="contact_email" value="' . $this->bean->email . '" />
					<input type="hidden" name="contact_name" value="' . $this->bean->contact_name . '" />
					<input type="hidden" name="itinerary_id" value="' . $row['id'] . '" />
					<input type="hidden" name="direction" value="' . $row['direction'] . '" />
					<input type="hidden" name="airline_code" value="' . $row['airline_code'] . '" />
					<input type="hidden" name="ticket_type" value="' . $this->bean->ticket_type . '" />
					<div class="d-flex align-items-center gap-2 justify-content-center">
						<input type="button" name="btnSendSMS" value="SMS" title="Send SMS" class="btn btn-primary-2 fw-semibold flex-fill" direction="' . $row['direction'] . '" flightno="' . $row['flight_number'] . '" journey="' . $journey . '" date="' . explode(' ', $sms_depdate)[0] . '" time="' . explode(' ', $sms_depdate)[1] . '" applied_pass="' . $applied_pass . '" style="max-width:30%" />
						' . $remind_btn . '
						' . $checkin_status . '
					</div>
				</form>
			</td>';
	}


	private function getAppliedPassengerIti($booking_id, $direction)
	{
		$sql_pass = "SELECT GROUP_CONCAT(id) AS applied_pass
			FROM ec_booking_passengers 
			WHERE booking_id = '$booking_id' AND deleted = 0
				AND id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers 
					WHERE booking_id = '$booking_id' AND add_type = 2 AND deleted = 0
				) 
				AND id NOT IN (
					SELECT assigned_user_id
					FROM ec_booking_itineraries
					WHERE booking_id = '{$this->bean->id}'
						AND direction = '$direction'
						AND add_type = 3
						AND deleted = 0
				)
			GROUP BY booking_id
		";
		$res_pass = $this->bean->db->query($sql_pass);
		$row_pass = $this->bean->db->fetchByAssoc($res_pass);

		// Check null trong th tất cả passenter đều bị thay đổi hành trình
		$row_pass['applied_pass'] = (isset($row_pass['applied_pass'])) ? $row_pass['applied_pass'] : '';

		return $row_pass['applied_pass'];
	}

	/**
	 * Render table ticket details
	 */

	public function getResolvedItinerariesForPopup()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		$bookingId = $this->bean->id;
		$results = [];

		// Check which directions have been rescheduled
		$rescheduledDirections = [];
		$sqlCheck = "SELECT DISTINCT direction FROM ec_booking_itineraries
			WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0";
		$resCheck = $this->bean->db->query($sqlCheck);
		while ($row = $this->bean->db->fetchByAssoc($resCheck)) {
			$rescheduledDirections[] = (int)$row['direction'];
		}

		$fields = "i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction";

		if (empty($rescheduledDirections)) {
			// No rescheduled — just get originals
			$sql = "SELECT MIN(i.id) AS id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
				GROUP BY i.direction, i.flight_number, i.departure_date
				ORDER BY i.direction, i.departure_date";
		} else {
			$rescheduledDirList = implode(',', $rescheduledDirections);

			// Original itineraries for directions NOT rescheduled
			$sqlUnchanged = "SELECT $fields FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
				AND i.direction NOT IN ($rescheduledDirList)";

			// Latest rescheduled itineraries — GROUP BY to deduplicate per-passenger rows
			$sqlRescheduled = "SELECT MIN(i.id) AS id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
				FROM ec_booking_itineraries i
				INNER JOIN (
					SELECT direction, MAX(sabre_logs) AS max_logs
					FROM ec_booking_itineraries
					WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0
					GROUP BY direction
				) latest ON i.direction = latest.direction AND i.sabre_logs = latest.max_logs
				WHERE i.booking_id = '$bookingId' AND i.add_type = 3 AND i.deleted = 0
				GROUP BY i.direction, i.flight_number, i.departure_date";

			$sql = "($sqlUnchanged) UNION ALL ($sqlRescheduled) ORDER BY direction, departure_date";
		}

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$directionLabel = $app_list_strings['bk_direction_list'][(int)$row['direction']] ?? '';
			$airlineCode = EC_Airlines::normalizeIataCode($row['airline_code'] ?? '');
			$airlineName = EC_Airlines::getAirlineName($airlineCode) ?: $airlineCode;

			$results[] = [
				'id' => $row['id'],
				'direction' => (int)$row['direction'],
				'directionLabel' => $directionLabel,
				'airline' => $airlineCode ?? $row['airline_code'],
				'airlineName' => $airlineName,
				'logoUrl' => EC_Airlines::getLogoUrl($airlineCode),
				'flightNo' => $row['flight_number'] ?? '',
				'departure' => $row['departure'] ?? '',
				'arrival' => $row['arrival'] ?? '',
				'depDate' => !empty($row['departure_date']) ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '',
			];
		}

		return $results;
	}

	/**
	 * Check if itinerary changes (add_type=3) are per-passenger (assigned_user_id differs).
	 * Returns true when at least one add_type=3 record has a non-empty assigned_user_id
	 * that matches an actual passenger ID (i.e. changes target specific passengers).
	 */

	function hasPerPassengerItineraryChanges()
	{
		$bookingId = $this->bean->id;
		// Count distinct assigned_user_id values linked to actual passengers
		$sql = "SELECT COUNT(DISTINCT i.assigned_user_id) AS cnt
			FROM ec_booking_itineraries i
			INNER JOIN ec_booking_passengers p ON p.id = i.assigned_user_id AND p.booking_id = i.booking_id AND p.deleted = 0
			WHERE i.booking_id = '$bookingId' AND i.add_type = 3 AND i.deleted = 0
			AND i.assigned_user_id IS NOT NULL AND i.assigned_user_id != ''";
		$cnt = (int)$this->bean->db->getOne($sql);
		return $cnt > 0;
	}

	/**
	 * Get per-passenger itinerary data for the popup.
	 * Returns an array of passengers, each with their resolved itineraries.
	 * For each direction: use add_type=3 for that passenger if exists, else fallback to add_type=0.
	 */

	function getPerPassengerItinerariesForPopup()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		$bookingId = $this->bean->id;
		$results = [];

		$passengers = $this->getActivePassengersForItineraryPopup($bookingId);
		$originalItineraries = $this->getOriginalItinerariesByDirection($bookingId);
		$changedItineraries = $this->getChangedItinerariesByPassengerAndDirection($bookingId);
		$parentMap = $this->getPassengerParentMapForBooking($bookingId);

		// Get all directions
		$allDirections = array_keys($originalItineraries);
		sort($allDirections);

		foreach ($passengers as $pax) {
			$paxId = $pax['id'];
			$salutationText = $app_list_strings['passenger_salutation_list'][(int)$pax['salutation']] ?? '';

			$paxItineraries = [];
			$allPaxIds = $this->resolvePassengerIdChainFromMap($paxId, $parentMap);
			foreach ($allDirections as $dir) {
				$rowChanged = $this->findChangedItineraryForPassengerDirection($changedItineraries, $allPaxIds, $dir);
				if ($rowChanged) {
					$paxItineraries[] = $this->formatItineraryForPopup($rowChanged, $date_format);
				} else {
					// Fallback to original
					if (isset($originalItineraries[$dir])) {
						foreach ($originalItineraries[$dir] as $origRow) {
							$paxItineraries[] = $this->formatItineraryForPopup($origRow, $date_format);
						}
					}
				}
			}

			$results[] = [
				'passengerId' => $paxId,
				'passengerName' => trim($pax['name']),
				'salutation' => $salutationText,
				'type' => (int)$pax['type'],
				'itineraries' => $paxItineraries,
			];
		}

		return $results;
	}

	private function getActivePassengersForItineraryPopup($bookingId)
	{
		// Get active passengers (original not superseded + final renamed)
		$supersededIds = "SELECT parent_detail_id FROM ec_booking_passengers
			WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
			AND parent_detail_id IS NOT NULL";

		$notLatestRenames = "SELECT p2.id FROM ec_booking_passengers p2
			INNER JOIN (
				SELECT parent_detail_id, MAX(date_entered) AS max_date
				FROM ec_booking_passengers
				WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
				AND parent_detail_id IS NOT NULL
				GROUP BY parent_detail_id
			) latest ON p2.parent_detail_id = latest.parent_detail_id
			WHERE p2.booking_id = '$bookingId' AND p2.add_type = 2 AND p2.deleted = 0
			AND p2.date_entered < latest.max_date";

		$sql = "SELECT p.id, p.name, p.salutation, p.type, p.pnr_outbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$bookingId' AND p.deleted = 0
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
				AND p.id NOT IN ($supersededIds)
			UNION
			SELECT p.id, p.name, p.salutation, p.type, p.pnr_outbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$bookingId' AND p.add_type = 2 AND p.deleted = 0
				AND p.id NOT IN ($supersededIds)
				AND p.id NOT IN ($notLatestRenames)
			ORDER BY type, name";

		return $this->fetchRows($sql);
	}

	private function getOriginalItinerariesByDirection($bookingId)
	{
		$sql = "SELECT i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
			FROM ec_booking_itineraries i
			WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
			ORDER BY i.direction, i.departure_date";

		$rows = $this->fetchRows($sql);
		$originalItineraries = [];
		foreach ($rows as $row) {
			$dir = (int)$row['direction'];
			if (!isset($originalItineraries[$dir])) $originalItineraries[$dir] = [];
			$originalItineraries[$dir][] = $row;
		}

		return $originalItineraries;
	}

	private function getChangedItinerariesByPassengerAndDirection($bookingId)
	{
		$sql = "SELECT i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction, i.assigned_user_id, i.sabre_logs
			FROM ec_booking_itineraries i
			WHERE i.booking_id = '$bookingId'
				AND i.add_type = 3
				AND i.assigned_user_id IS NOT NULL
				AND i.assigned_user_id != ''
				AND i.deleted = 0
			ORDER BY i.assigned_user_id, i.direction, i.sabre_logs DESC";

		$map = [];
		foreach ($this->fetchRows($sql) as $row) {
			$passengerId = $row['assigned_user_id'];
			$direction = (int)$row['direction'];
			if (!isset($map[$passengerId][$direction])) {
				$map[$passengerId][$direction] = $row;
			}
		}

		return $map;
	}

	private function getPassengerParentMapForBooking($bookingId)
	{
		$sql = "SELECT id, parent_detail_id
			FROM ec_booking_passengers
			WHERE booking_id = '$bookingId'
				AND deleted = 0";

		$map = [];
		foreach ($this->fetchRows($sql) as $row) {
			$map[$row['id']] = $row['parent_detail_id'];
		}

		return $map;
	}

	private function resolvePassengerIdChainFromMap($passengerId, $parentMap)
	{
		$ids = [];
		$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $passengerId);
		$maxDepth = 10;

		for ($i = 0; $i < $maxDepth; $i++) {
			if (empty($currentId) || in_array($currentId, $ids)) break;
			$ids[] = $currentId;

			if (empty($parentMap[$currentId])) break;
			$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $parentMap[$currentId]);
		}

		return $ids;
	}

	private function findChangedItineraryForPassengerDirection($changedItineraries, $passengerIds, $direction)
	{
		$matchedRow = null;
		foreach ($passengerIds as $passengerId) {
			if (isset($changedItineraries[$passengerId][$direction])) {
				$row = $changedItineraries[$passengerId][$direction];
				if (!$matchedRow || (int)$row['sabre_logs'] > (int)$matchedRow['sabre_logs']) {
					$matchedRow = $row;
				}
			}
		}

		return $matchedRow;
	}

	private function fetchRows($sql)
	{
		$res = $this->bean->db->query($sql);
		$rows = [];
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Format a single itinerary row for popup JSON (shared by multiple popup methods).
	 */

	function formatItineraryForPopup($row, $date_format)
	{
		global $app_list_strings;
		$directionLabel = $app_list_strings['bk_direction_list'][(int)$row['direction']] ?? '';
		$airlineCode = EC_Airlines::normalizeIataCode($row['airline_code'] ?? '');

		return [
			'id' => $row['id'],
			'direction' => (int)$row['direction'],
			'directionLabel' => $directionLabel,
			'airline' => $airlineCode,
			'logoUrl' => EC_Airlines::getLogoUrl($airlineCode),
			'flightNo' => $row['flight_number'] ?? '',
			'departure' => $row['departure'] ?? '',
			'arrival' => $row['arrival'] ?? '',
			'depDate' => !empty($row['departure_date']) ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '',
		];
	}
}
