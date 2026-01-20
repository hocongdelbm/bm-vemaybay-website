<?php
require_once("include/Sugar_Smarty.php");

class Viewprinteticket extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn';
		$khuhoi = (isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi'])) ? $_REQUEST['khuhoi'] : 0;

		$this->populateContent($smartyCont, $lang, $khuhoi);
		$smartyCont->display("modules/EC_Flight_Bookings/tpls/view_printeticket_$lang.tpl");
	}
	function populateContent($smartyCont, $lang, $khuhoi)
	{
		global $db;
		$booking_id = $_REQUEST['booking_id'] ?? '';
		$booking_number = $_REQUEST['booking'] ?? '';

		$bean = BeanFactory::getBean('EC_Flight_Bookings', $booking_id);
		$created_by = new User();
		$created_by->retrieve($bean->created_by);
		// $department_info = myGetDepartmentInfo($created_by->department_id);
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f");

		$com_website = $department_info['com_website2'];
		$com_phone = $department_info['com_phone'];
		if (!empty($department_info['com_hotline1']))
			$com_phone .= ' - ' . $department_info['com_hotline1'];
		if (!empty($department_info['com_hotline2']))
			$com_phone .= ' - ' . $department_info['com_hotline2'];

		// *** DECODE BASE64 AND GET FULL DATA FROM JSON ***
		$passengersData = [];
		$itinerariesData = [];

		if (isset($_REQUEST['passengersData']) && !empty($_REQUEST['passengersData'])) {
			try {
				$passengersJSON = base64_decode($_REQUEST['passengersData']);
				$passengersData = json_decode($passengersJSON, true);
			} catch (Exception $e) {
				error_log("Error decoding passengers data: " . $e->getMessage());
			}
		}

		if (isset($_REQUEST['itinerariesData']) && !empty($_REQUEST['itinerariesData'])) {
			try {
				$itinerariesJSON = base64_decode($_REQUEST['itinerariesData']);
				$itinerariesData = json_decode($itinerariesJSON, true);
			} catch (Exception $e) {
				error_log("Error decoding itineraries data: " . $e->getMessage());
			}
		}

		$passengerHTML = $this->buildPassengerHTMLFromData($passengersData, $khuhoi, $lang);
		$itineraryHTML = $this->buildItineraryHTMLFromData($itinerariesData, $lang);

		$smartyCont->assign('BOOKING_NUMBER', $booking_number);
		$smartyCont->assign('LIST_OF_PASSENGER', $passengerHTML);
		$smartyCont->assign('LIST_OF_ITINERARIES', $itineraryHTML);
		$smartyCont->assign('COM_NAME', $department_info['com_name']);
		$smartyCont->assign('COM_TAXCODE', $department_info['com_taxcode']);
		$smartyCont->assign('COM_ADDRESS', ($lang == 'en' ? $department_info['com_address2'] : $department_info['com_address']));
		$smartyCont->assign('COM_TOP_PHONE', $department_info['com_phone']);
		$smartyCont->assign('COM_PHONE', $com_phone);
		$smartyCont->assign('COM_WEBSITE', $com_website);
		$smartyCont->assign('COM_EMAIL', $department_info['com_email']);
		$smartyCont->assign('IMAGE_URL_LARGE', $department_info['company_logo']);
		$smartyCont->assign('MINUTE_BEFORE', '120');
	}

	function buildPassengerHTMLFromData($passengersData, $khuhoi, $lang)
	{
		$html = '';

		if (empty($passengersData) || !is_array($passengersData)) {
			return '<tr><td colspan="3" style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">Không có hành khách</td></tr>';
		}

		$labelOutbound = ($lang == 'en') ? 'Outbound' : 'Lượt đi';
		$labelInbound = ($lang == 'en') ? 'Inbound' : 'Lượt về';

		foreach ($passengersData as $passenger) {

			// Get PNR
			$pnr = '';
			if ($khuhoi) {
				$pnr = (!empty($passenger['pnrOutbound']) ? $passenger['pnrOutbound'] : (!empty($passenger['eticketOutbound']) ? $passenger['eticketOutbound'] : ''));
				$pnr .= trim($pnr) != '' ? ' - ' : '';
				$pnr .= (!empty($passenger['pnrInbound']) ? $passenger['pnrInbound'] : (!empty($passenger['eticketInbound']) ? $passenger['eticketInbound'] : ''));
			} else {
				$pnr = (!empty($passenger['pnrOutbound']) ? $passenger['pnrOutbound'] : (!empty($passenger['eticketOutbound']) ? $passenger['eticketOutbound'] : ''));
			}

			$baggageDescription = '';
			if (isset($passenger['luggage'])) {
				if ($khuhoi) {
					if (!empty($passenger['luggage']['outbound'])) {
						$luggageOutbound = $this->cleanLuggageText($passenger['luggage']['outbound']);
						$luggageOutbound = $this->translateLuggageText($luggageOutbound, $lang);
						$baggageDescription .= $luggageOutbound . ' (' . $labelOutbound . ')';
					}
					if (!empty($passenger['luggage']['inbound'])) {
						$luggageInbound = $this->cleanLuggageText($passenger['luggage']['inbound']);
						$luggageInbound = $this->translateLuggageText($luggageInbound, $lang);
						$baggageDescription .= ($baggageDescription ? ' ' : '') . $luggageInbound . ' (' . $labelInbound . ')';
					}
				} else {
					$baggageDescription = $this->cleanLuggageText($passenger['luggage']['outbound'] ?? '');
					$baggageDescription = $this->translateLuggageText($baggageDescription, $lang);
				}
			}

			// Build HTML row
			$html .= '<tr>
            <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . htmlspecialchars($passenger['fullname']) . '</td>
            <td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . strtoupper($pnr) . '</td>
            <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . $baggageDescription . '</td>
        </tr>';
		}

		return $html;
	}


	function cleanLuggageText($text)
	{
		$text = strip_tags($text);

		$text = preg_replace('/\s*\([^)]*\)/', '', $text);

		$text = preg_replace('/Giá bán:.*?VND/i', '', $text);
		$text = preg_replace('/Giá mua:.*?VND/i', '', $text);
		$text = preg_replace('/Nhà cung cấp:.*?(\n|$)/i', '', $text);

		$text = preg_replace('/Lượt đi:/i', '', $text);
		$text = preg_replace('/Lượt về:/i', '', $text);
		$text = preg_replace('/Outbound:/i', '', $text);
		$text = preg_replace('/Inbound:/i', '', $text);

		$text = preg_replace('/Thêm\s+/i', '+ ', $text);

		$text = preg_replace('/\s+/', ' ', $text);

		$text = trim($text);

		return $text;
	}

	function translateLuggageText($text, $lang)
	{
		if ($lang != 'en') {
			return $text;
		}

		$translations = [
			'kiện' => 'piece',
			'Kiện' => 'Piece',
			'kg' => 'kg',
			'hành lý' => 'baggage',
			'Hành lý' => 'Baggage',
			'x' => 'x',
			'+' => '+'
		];

		$translatedText = str_replace(array_keys($translations), array_values($translations), $text);

		return $translatedText;
	}

	function buildItineraryHTMLFromData($itinerariesData, $lang)
	{
		$html = '';

		if (empty($itinerariesData) || !is_array($itinerariesData)) {
			return '<tr><td colspan="5" style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">Không có hành trình</td></tr>';
		}

		foreach ($itinerariesData as $itinerary) {

			// Get airline info
			$airlineCode = $itinerary['airlineCode'] ?? $itinerary['airline'] ?? '';
			$airline = myGetAirlineInfo2(trim($airlineCode), 'CODE');
			$airlineName = $airline['data'][0]['name'] ?? $itinerary['airline'];

			// Get departure airport info
			$departureCode = $itinerary['departure'] ?? '';
			$departure = myGetAirportInfo2(trim($departureCode));
			$departureName = ($departure['data'][0]['name'] ?? '') . ' (' . ($departure['data'][0]['code'] ?? $departureCode) . ')';

			// Get arrival airport info
			$arrivalCode = $itinerary['arrival'] ?? '';
			$arrival = myGetAirportInfo2(trim($arrivalCode));
			$arrivalName = ($arrival['data'][0]['name'] ?? '') . ' (' . ($arrival['data'][0]['code'] ?? $arrivalCode) . ')';

			// Format dates
			$departureDateTime = $itinerary['departureDate'] ?? '';
			$arrivalDateTime = $itinerary['arrivalDate'] ?? '';

			// Extract time from arrival date
			$arrivalTime = '';
			if (!empty($arrivalDateTime)) {
				$parts = explode(' ', $arrivalDateTime);
				$arrivalTime = isset($parts[1]) ? $parts[1] : '';
			}

			// Build flight display
			$flightDisplay = $departureDateTime;
			if ($arrivalTime) {
				$flightDisplay .= ' - ' . $arrivalTime;
			}

			// Get flight number
			$flightNumber = trim($itinerary['flightNumber']);

			// Build HTML row
			$html .= '<tr>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($flightDisplay) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($airlineName) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($flightNumber) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($departureName) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($arrivalName) . '</td>
        </tr>';
		}

		return $html;
	}

	// function populateContent($smartyobj, $lang, $khuhoi, $listPassengerIDs) {
	// 	// Lấy danh sách, số lượng, thông tin hành khách
	// 	$pass_inf = $this->listOfPassengers($_REQUEST['booking_id'], $_REQUEST['direction'], $_REQUEST['airline_code'], $khuhoi, $lang, $_REQUEST['itinerary_id'], $listPassengerIDs, $smartyobj);

	// 	if ($pass_inf['pass_cnt'] <= 1 && isset($_REQUEST['add_type']) && $_REQUEST['add_type'] == "3") {
	// 		$is_change_inf = 1;
	// 		$_REQUEST['add_type'] = "0";
	// 	}
	// 	else {
	// 		$is_change_inf = $_REQUEST['add_type'] == "3" ? 1 : 0;
	// 		if(!isset($_REQUEST['add_type'])) $_REQUEST['add_type'] = "0";
	// 	}
	// 	$smartyobj->assign('ADD_TYPE', $_REQUEST['add_type']);

	// 	// Không thay đổi hành trình
	// 	if (!isset($_REQUEST['add_type']) || $_REQUEST['add_type'] == "0") {
	// 		$iti_inf = $this->listOfItineraries($_REQUEST['booking_id'], $khuhoi, $_REQUEST['wayflight'], $lang, $_REQUEST['itinerary_id'], $is_change_inf);

	// 		if ($is_change_inf && $khuhoi) {
	// 			if ($iti_inf['direction'] == 1) $fdirection = 0;
	// 			else $fdirection = 1;

	// 			$another_iti 	= $this->getAnotherIti($_REQUEST['booking_id'], $fdirection, $pass_inf['pass_id'], $pass_inf['edit_no']);

	// 			if(!empty($another_iti)){
	// 				$airline 			= myGetAirlineInfo2(trim($another_iti['airline_code']), 'CODE');
	// 				$departure 			= myGetAirportInfo2(trim($another_iti['departure']));
	// 				$arrival 			= myGetAirportInfo2(trim($another_iti['arrival']));
	// 				$departure_date 	= date('d/m/Y', strtotime($another_iti['departure_date'])) . ' <br /> ' . date('H:i', strtotime($another_iti['departure_date'])) .' - ' .date('H:i', strtotime($another_iti['arrival_date']));
	// 				$airline 			= $airline['data'][0]['name'];
	// 				$flight_number 	= $another_iti['flight_number'];
	// 				$departure_inf 	= $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
	// 				$arrival_inf 		= $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';

	// 				$html1 = '<tr class="no-change-iti">
	// 							<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px; line-height: 20px;">' . $departure_date . '</td>
	// 							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $airline . '</td>
	// 							<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px;">' . $flight_number . '</td>
	// 							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure_inf . '</td>
	// 							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival_inf . '</td>
	// 						</tr>';
	// 			} else {
	// 				$html1 = '';
	// 			}

	// 			if ($fdirection == 0){
	// 				$iti_html = $html1 . $iti_inf['html'];
	// 			}
	// 			else {
	// 				$iti_html = $iti_inf['html'] . $html1;
	// 			}
	// 		} 
	// 		else $iti_html = $iti_inf['html'];

	// 		$smartyobj->assign('LIST_OF_ITINERARIES', $iti_html);
	// 	}
	// 	// Chắc là có đổi hành trình
	// 	else if(isset($_REQUEST['add_type']) && $_REQUEST['add_type'] == "3") {
	// 		$iti_inf = $this->listOfItineraries($_REQUEST['booking_id'], $khuhoi, $_REQUEST['wayflight'], $lang, $_REQUEST['itinerary_id'], $is_change_inf);

	// 		if($khuhoi) {
	// 			if ($iti_inf['direction'] == 1) $fdirection = 0;
	// 			else $fdirection = 1;

	// 			$another_iti = $this->getAnotherIti($_REQUEST['booking_id'], $fdirection, $pass_inf['pass_id'], $pass_inf['edit_no']);

	// 			if(!empty($another_iti)){
	// 				$airline 		= myGetAirlineInfo2(trim($another_iti['airline_code']), 'CODE');
	// 				$departure 		= myGetAirportInfo2(trim($another_iti['departure']));
	// 				$arrival 		= myGetAirportInfo2(trim($another_iti['arrival']));
	// 				$departure_date = date('d/m/Y', strtotime($another_iti['departure_date'])) . ' <br /> ' . date('H:i', strtotime($another_iti['departure_date'])) .' - ' .date('H:i', strtotime($another_iti['arrival_date']));
	// 				$airline 		= $airline['data'][0]['name'];
	// 				$flight_number 	= $another_iti['flight_number'];
	// 				$departure_inf 	= $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
	// 				$arrival_inf 	= $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';

	// 				$html1 = '<tr class="no-change-iti">
	// 					<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px; line-height: 20px;">' . $departure_date . '</td>
	// 					<td style="border:1px solid #ccc; padding: 10px 7px;">' . $airline . '</td>
	// 					<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px;">' . $flight_number . '</td>
	// 					<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure_inf . '</td>
	// 					<td style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival_inf . '</td>
	// 				</tr>';
	// 			} else {
	// 				$html1 = '';
	// 			}

	// 			if ($fdirection == 0){
	// 				$iti_html = $html1 . $iti_inf['html'];
	// 			}
	// 			else {
	// 				$iti_html = $iti_inf['html'] . $html1;
	// 			}
	// 		}
	// 		else $iti_html = $iti_inf['html'];

	// 		$smartyobj->assign('LIST_OF_ITINERARIES', $iti_html);
	// 	}

	// 	// // Detect department id
	// 	// $created_by = new User();
	// 	// $created_by->retrieve($this->bean->created_by);
	// 	$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // MHV
	// 	$com_website = $department_info['com_website2'];
	// 	$com_phone = $department_info['com_phone'];
	// 	if (!empty($department_info['com_hotline1']))
	// 		$com_phone .= ' - ' . $department_info['com_hotline1'];
	// 	if (!empty($department_info['com_hotline2']))
	// 		$com_phone .= ' - ' . $department_info['com_hotline2'];

	// 	$smartyobj->assign('BOOKING_NUMBER', $_REQUEST['booking']);
	// 	$smartyobj->assign('LIST_OF_PASSENGER', $pass_inf['html']);
	// 	$smartyobj->assign('COM_NAME', $department_info['com_name']);
	// 	$smartyobj->assign('COM_TAXCODE', $department_info['com_taxcode']);
	// 	$smartyobj->assign('COM_ADDRESS', ($lang == 'en' ? $department_info['com_address2'] : $department_info['com_address']));
	// 	$smartyobj->assign('COM_TOP_PHONE', $department_info['com_phone']);
	// 	$smartyobj->assign('COM_PHONE', $com_phone);
	// 	$smartyobj->assign('COM_WEBSITE', $com_website);
	// 	$smartyobj->assign('COM_EMAIL', $department_info['com_email']);
	// 	$smartyobj->assign('IMAGE_URL_LARGE', $department_info['company_logo']);
	// 	$smartyobj->assign('MINUTE_BEFORE', $_REQUEST['ticket_type'] == '2' ? '120' : '120');
	// 	// 2: quốc tế là 180p
	// }

	// Lấy danh sách hành khách
	function listOfPassengers($booking_id, $direction, $airline_code, $khuhoi, $lang, $iti_id, $listPassengerIDs = [], $smartyobj = null)
	{
		global $db, $current_user;

		$iti = new EC_Booking_Itineraries;
		$iti->retrieve($iti_id);
		$airline = myGetAirlineInfo2(trim($iti->airline_code), 'CODE');
		$departure = myGetAirportInfo2(trim($iti->departure));
		$arrival = myGetAirportInfo2(trim($iti->arrival));
		$pass_id = '';

		// Lấy thông tin của 1 chiều đang có
		// Lượt đi
		if ($iti->direction == 0) {
			$departure_date1 = date('d/m/Y H:i', strtotime(' -7 hours', strtotime($iti->departure_date)));
			$airline1 = $airline['data'][0]['name'] ?? '';
			$flight_number1 = $iti->flight_number;
			$departure1 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
			$arrival1 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
		}
		// Lượt về 
		else {
			$departure_date2 = date('d/m/Y H:i', strtotime(' -7 hours', strtotime($iti->departure_date)));
			$airline2 = $airline['data'][0]['name'] ?? '';
			$flight_number2 = $iti->flight_number;
			$departure2 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
			$arrival2 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
		}

		// Nếu là hành trình ban đầu
		if (empty($iti->add_type)) {
			$sql_con = ' 
				AND p.id NOT IN (
					SELECT assigned_user_id
					FROM ec_booking_itineraries
					WHERE 
						booking_id = "' . $booking_id . '"
						AND add_type IN (1, 3)
						AND deleted = 0
				) 
				AND p.id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers
					WHERE
						booking_id = "' . $booking_id . '"
						AND add_type = 2
						AND deleted = 0
				)';
		}
		// Là hành trình thay đổi
		else {

			$passengerList = "'" . implode("','", $listPassengerIDs) . "'";

			$sql_con = ' 
			AND (
				p.id IN (
					SELECT assigned_user_id FROM ec_booking_itineraries
					WHERE booking_id = "' . $booking_id . '" AND add_type = 3 AND deleted = 0
						AND sabre_logs = (
							SELECT sabre_logs
							FROM ec_booking_itineraries
							WHERE id = "' . $iti_id . '"
						)
				) 
				OR p.id IN(' . $passengerList . ')
			)
			AND p.id NOT IN (
				SELECT assigned_user_id FROM ec_booking_itineraries
				WHERE booking_id = "' . $booking_id . '" AND add_type = 3 AND deleted = 0
					AND sabre_logs > (
						SELECT sabre_logs
						FROM ec_booking_itineraries
						WHERE id = "' . $iti_id . '"
					)
			)';

			// $sql_con = ' 
			// AND p.id IN (
			// 	SELECT assigned_user_id FROM ec_booking_itineraries
			// 	WHERE booking_id = "' . $booking_id . '" AND add_type = 3 AND deleted = 0
			// 		AND sabre_logs = (
			// 			SELECT sabre_logs
			// 			FROM ec_booking_itineraries
			// 			WHERE id = "' . $iti_id . '"
			// 		)
			// ) 
			// AND p.id NOT IN (
			// 	SELECT assigned_user_id FROM ec_booking_itineraries
			// 	WHERE booking_id = "' . $booking_id . '" AND add_type = 3 AND deleted = 0
			// 		AND sabre_logs > (
			// 			SELECT sabre_logs
			// 			FROM ec_booking_itineraries
			// 			WHERE id = "' . $iti_id . '"
			// 		)
			// )';
		}

		$html = '';
		$html_itineraries = '';
		$sql = "SELECT p.id,
				p.name,
				p.salutation,
				p.type,
				p.pnr_outbound,
				p.pnr_inbound,
				p.eticket_outbound,
				p.eticket_inbound,
				p.luggage_price,
				p.luggage_price_inbound,
				p.luggage_index_outbound,
				p.luggage_index_inbound,
				p.luggage_purchase_text,
				p.luggage_purchase_text_inbound,
				p.cic,
				p.passport_number,
				p.booking_id,
				(SELECT DATE_ADD(date_entered, INTERVAL 7 HOUR) FROM ec_flight_bookings WHERE id = p.booking_id) AS date_entered,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS aircode_outbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS ticket_class_outbound,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS aircode_inbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ticket_class_inbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$booking_id'
				AND p.id IN('" . implode("','", $listPassengerIDs) . "')
				AND p.deleted = 0
				$sql_con
			ORDER BY p.type, p.date_entered ";


		$res = $db->query($sql);
		$rowCount = $db->countRows($res);

		if ($iti->add_type == 3 && $rowCount > 1) {
			$html_dep_itineraries = $html_ret_itineraries = '';

			while ($row = $db->fetchByAssoc($res)) {
				// $pnr = (trim($row['pnr_outbound']) != '' ? $row['pnr_outbound'] : (trim($row['eticket_outbound']) != '' ? $row['eticket_outbound'] : ''));
				if ($khuhoi) {
					$pnr = (trim($row['pnr_outbound']) != '' ? $row['pnr_outbound'] : (trim($row['eticket_outbound']) != '' ? $row['eticket_outbound'] : ''));
					$pnr .= trim($pnr) != '' ? ' - ' : '';
					$pnr .= (trim($row['pnr_inbound']) != '' ? $row['pnr_inbound'] : (trim($row['eticket_inbound']) != '' ? $row['eticket_inbound'] : ''));
				} else if ($direction == '1') {
					$pnr = (trim($row['pnr_inbound']) != '' ? $row['pnr_inbound'] : (trim($row['eticket_inbound']) != '' ? $row['eticket_inbound'] : ''));
				} else {
					$pnr = (trim($row['pnr_outbound']) != '' ? $row['pnr_outbound'] : (trim($row['eticket_outbound']) != '' ? $row['eticket_outbound'] : ''));
				}

				// Hành lý mới
				$baggageDescription = '';
				if ($this->bean->isUseNewBaggage($row['date_entered'], $this->bean->created_by)) {
					// Available baggage text
					$luggage_index_outbound = Baggage::renderAvailableBaggage($row['luggage_index_outbound'] ?? '');
					$luggage_index_inbound = Baggage::renderAvailableBaggage($row['luggage_index_inbound'] ?? '');

					// Purchased baggage text
					$luggage_purchase_text = preg_replace('/\s*\([^)]*\)/', '', ($row['luggage_purchase_text'] ?? ''));
					$luggage_purchase_text_inbound = preg_replace('/\s*\([^)]*\)/', '', $row['luggage_purchase_text_inbound']);

					if ($khuhoi)
						$baggageDescription = $this->bean->generateCombinedPassengerBaggageInfo($luggage_index_outbound, $luggage_purchase_text, $luggage_index_inbound, $luggage_purchase_text_inbound, $lang);
					else
						$baggageDescription = $this->bean->generateCombinedPassengerBaggageInfo($luggage_index_outbound, $luggage_purchase_text, '', '', $lang);
				}
				// Hành lý cũ
				else {
					// Hành lý mua thêm
					$luggage_purchase_text = preg_replace('/\s*\([^)]*\)/', '', ($row['luggage_purchase_text'] ?? ''));
					$luggage_purchase_text_inbound = preg_replace('/\s*\([^)]*\)/', '', $row['luggage_purchase_text_inbound']);
					if (!empty($luggage_purchase_text) || !empty($luggage_purchase_text_inbound)) {
						if ($khuhoi) {
							if (!empty($luggage_purchase_text))
								$baggageDescription .= empty($baggageDescription) ? "$luggage_purchase_text (Lượt đi)" : "\n$luggage_purchase_text (Lượt đi)";
							if (!empty($luggage_purchase_text_inbound))
								$baggageDescription .= empty($baggageDescription) ? "$luggage_purchase_text_inbound (Lượt về)" : " - $luggage_purchase_text_inbound (Lượt về)";
						} elseif ($direction == '1') {
							if (!empty($luggage_purchase_text_inbound))
								$baggageDescription .= empty($baggageDescription) ? $luggage_purchase_text_inbound : "\n$luggage_purchase_text_inbound";
						} else {
							if (!empty($luggage_purchase_text))
								$baggageDescription .= empty($baggageDescription) ? $luggage_purchase_text : "\n$luggage_purchase_text";
						}
					}

					if ($khuhoi) {
						// Hành lý chiều đi
						$bag_out = generateLuggage($row['date_entered'], $row['aircode_outbound'], $row['ticket_class_outbound'], $row['type'], $row['luggage_index_outbound']);
						if (!is_null($row['luggage_index_outbound']) && !empty($row['luggage_index_outbound'])) {
							$row['luggage_price'] = $row['luggage_index_outbound'];
						}

						$bag_out2 = $bag_out[(int) $row['luggage_price']];
						$bag_weight_out = 0;
						if (isset($bag_out2) && !empty($bag_out2)) {
							preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
							$bag_weight_out = isset($ob_output[1]) ? (int) $ob_output[1] : 0;
						}
						// if ($bag_weight_out >= 0) {
						if ($bag_weight_out > 0) {
							$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
							$baggageDescription .= $khuhoi ? ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)') : '';
						}

						// Hành lý chiều về
						$bag_in = generateLuggage($row['date_entered'], $row['aircode_inbound'], $row['ticket_class_inbound'], $row['type'], $row['luggage_index_inbound']);
						if (!empty($bag_in)) {
							if (!is_null($row['luggage_index_inbound']) && !empty($row['luggage_index_inbound'])) {
								$row['luggage_price_inbound'] = $row['luggage_index_inbound'];
							}

							$bag_in2 = $bag_in[(int) $row['luggage_price_inbound']];
							$bag_weight_in = 0;

							if (isset($bag_in2) && !empty($bag_in2)) {
								preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
								$bag_weight_in = isset($ib_output[1]) ? (int) $ib_output[1] : 0;
							}

							// if ($bag_weight_in >= 0) {
							if ($bag_weight_in > 0) {
								$baggageDescription .= $bag_weight_out > 0 ? ' <br /> ' : '';
								$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
								$baggageDescription .= $lang == 'en' ? ' (Inbound)' : ' (Lượt về)';
							}
						}

						// Lấy thông tin của chiều còn lại nếu là booking 2 chiều
						if ($iti->direction == 0)
							$fdirection = 1;
						else
							$fdirection = 0;

						$iti2 = $this->getAnotherIti($booking_id, $fdirection, $row['id'], $iti->sabre_logs);
						if (!empty($iti2)) {
							$airline = myGetAirlineInfo2(trim($iti2['airline_code']), 'CODE');
							$departure = myGetAirportInfo2(trim($iti2['departure']));
							$arrival = myGetAirportInfo2(trim($iti2['arrival']));

							if ($iti2['direction'] == 0) {
								$departure_date1 = date('d/m/Y H:i', strtotime($iti2['departure_date']));
								$airline1 = $airline['data'][0]['name'];
								$flight_number1 = $iti2['flight_number'];
								$departure1 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
								$arrival1 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
							}
							// Lượt về
							else {
								$departure_date2 = date('d/m/Y H:i', strtotime($iti2['departure_date']));
								$airline2 = $airline['data'][0]['name'];
								$flight_number2 = $iti2['flight_number'];
								$departure2 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
								$arrival2 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
							}
						}
					} elseif ($direction == '1') {
						// Hành lý chiều về
						$bag_in = generateLuggage($row['date_entered'], $row['aircode_inbound'], $row['ticket_class_inbound'], $row['type'], $row['luggage_index_inbound']);
						if (!empty($bag_in)) {
							if (!is_null($row['luggage_index_inbound']) && !empty($row['luggage_index_inbound'])) {
								$row['luggage_price_inbound'] = $row['luggage_index_inbound'];
							}

							$bag_in2 = $bag_in[(int) $row['luggage_price_inbound']];
							$bag_weight_in = 0;

							if (isset($bag_in2) && !empty($bag_in2)) {
								preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
								$bag_weight_in = isset($ib_output[1]) ? (int) $ib_output[1] : 0;
							}

							// if ($bag_weight_in >= 0) {
							if ($bag_weight_in > 0) {
								$baggageDescription .= $bag_weight_out > 0 ? ' <br /> ' : '';
								$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
								$baggageDescription .= $lang == 'en' ? ' (Inbound)' : ' (Lượt về)';
							}
						}

						// Lấy thông tin của chiều còn lại nếu là booking 2 chiều
						if ($iti->direction == 0)
							$fdirection = 1;
						else
							$fdirection = 0;

						$iti2 = $this->getAnotherIti($booking_id, $fdirection, $row['id'], $iti->sabre_logs);
						if (!empty($iti2)) {
							$airline = myGetAirlineInfo2(trim($iti2['airline_code']), 'CODE');
							$departure = myGetAirportInfo2(trim($iti2['departure']));
							$arrival = myGetAirportInfo2(trim($iti2['arrival']));

							if ($iti2['direction'] == 0) {
								$departure_date1 = date('d/m/Y H:i', strtotime($iti2['departure_date']));
								$airline1 = $airline['data'][0]['name'];
								$flight_number1 = $iti2['flight_number'];
								$departure1 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
								$arrival1 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
							}
							// Lượt về
							else {
								$departure_date2 = date('d/m/Y H:i', strtotime($iti2['departure_date']));
								$airline2 = $airline['data'][0]['name'];
								$flight_number2 = $iti2['flight_number'];
								$departure2 = $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
								$arrival2 = $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
							}
						}
					} else {
						// Hành lý chiều đi
						$bag_out = generateLuggage($row['date_entered'], $row['aircode_outbound'], $row['ticket_class_outbound'], $row['type'], $row['luggage_index_outbound']);
						if (!is_null($row['luggage_index_outbound']) && !empty($row['luggage_index_outbound'])) {
							$row['luggage_price'] = $row['luggage_index_outbound'];
						}

						$bag_out2 = $bag_out[(int) $row['luggage_price']];
						$bag_weight_out = 0;
						if (isset($bag_out2) && !empty($bag_out2)) {
							preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
							$bag_weight_out = isset($ob_output[1]) ? (int) $ob_output[1] : 0;
						}
						// if ($bag_weight_out >= 0) {
						if ($bag_weight_out > 0) {
							$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
							$baggageDescription .= $khuhoi ? ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)') : '';
						}
					}
				}

				$html .= '<tr>
					<td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . (empty($new_name['name']) ? $row['name'] : $new_name['name']) . '</td>
					<td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . strtoupper($pnr) . '</td>
					<td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . $baggageDescription . '</td>
				</tr>';

				if (empty($html_dep_itineraries)) {
					$html_dep_itineraries .= '
						<tr class="dep_itineraries">
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure_date1 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $airline1 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $flight_number1 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure1 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival1 . '</td>
						</tr>';
				}

				// Booking có 2 chiều
				if ($khuhoi && empty($html_ret_itineraries)) {
					$html_ret_itineraries .= '
						<tr class="ret_itineraries">
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure_date2 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $airline2 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $flight_number2 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure2 . '</td>
							<td style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival2 . '</td>
						</tr>';
				}
			}

			if (!is_null($smartyobj) && !empty($smartyobj)) {
				$smartyobj->assign('LIST_OF_ITINERARIES', $html_dep_itineraries . $html_ret_itineraries);
				$html_itineraries .= $html_dep_itineraries . $html_ret_itineraries;
			}
		} else {
			if ($rowCount > 0) {
				while ($row = $db->fetchByAssoc($res)) {
					$pass_id = $row['id'];

					if ($khuhoi) {
						$pnr = (trim($row['pnr_outbound']) != '' ? $row['pnr_outbound'] : (trim($row['eticket_outbound']) != '' ? $row['eticket_outbound'] : ''));
						$pnr .= trim($pnr) != '' ? ' - ' : '';
						$pnr .= (trim($row['pnr_inbound']) != '' ? $row['pnr_inbound'] : (trim($row['eticket_inbound']) != '' ? $row['eticket_inbound'] : ''));
					} else if ($direction == '1') {
						$pnr = (trim($row['pnr_inbound']) != '' ? $row['pnr_inbound'] : (trim($row['eticket_inbound']) != '' ? $row['eticket_inbound'] : ''));
					} else {
						$pnr = (trim($row['pnr_outbound']) != '' ? $row['pnr_outbound'] : (trim($row['eticket_outbound']) != '' ? $row['eticket_outbound'] : ''));
					}

					// Hành lý mới
					$baggageDescription = '';
					if ($this->bean->isUseNewBaggage($row['date_entered'], $this->bean->created_by)) {
						// Available baggage text
						$luggage_index_outbound = Baggage::renderAvailableBaggage($row['luggage_index_outbound'] ?? '');
						$luggage_index_inbound = Baggage::renderAvailableBaggage($row['luggage_index_inbound'] ?? '');

						// Purchased baggage text
						$luggage_purchase_text = preg_replace('/\s*\([^)]*\)/', '', ($row['luggage_purchase_text'] ?? ''));
						$luggage_purchase_text_inbound = preg_replace('/\s*\([^)]*\)/', '', $row['luggage_purchase_text_inbound']);

						if ($khuhoi)
							$baggageDescription = $this->bean->generateCombinedPassengerBaggageInfo($luggage_index_outbound, $luggage_purchase_text, $luggage_index_inbound, $luggage_purchase_text_inbound, $lang);
						else
							$baggageDescription = $this->bean->generateCombinedPassengerBaggageInfo($luggage_index_outbound, $luggage_purchase_text, '', '', $lang);
					}
					// Hành lý cũ
					else {
						// Hành lý mua thêm
						$luggage_purchase_text = preg_replace('/\s*\([^)]*\)/', '', ($row['luggage_purchase_text'] ?? ''));
						$luggage_purchase_text_inbound = preg_replace('/\s*\([^)]*\)/', '', $row['luggage_purchase_text_inbound']);
						if (!empty($luggage_purchase_text) || !empty($luggage_purchase_text_inbound)) {
							if ($khuhoi) {
								if (!empty($luggage_purchase_text))
									$baggageDescription .= empty($baggageDescription) ? "$luggage_purchase_text (Lượt đi)" : "\n$luggage_purchase_text (Lượt đi)";
								if (!empty($luggage_purchase_text_inbound))
									$baggageDescription .= empty($baggageDescription) ? "$luggage_purchase_text_inbound (Lượt về)" : " - $luggage_purchase_text_inbound (Lượt về)";
							} elseif ($direction == '1') {
								if (!empty($luggage_purchase_text_inbound))
									$baggageDescription .= empty($baggageDescription) ? $luggage_purchase_text_inbound : "\n$luggage_purchase_text_inbound";
							} else {
								if (!empty($luggage_purchase_text))
									$baggageDescription .= empty($baggageDescription) ? $luggage_purchase_text : "\n$luggage_purchase_text";
							}
						}

						if ($khuhoi) {
							// Thông tin hành lý lượt đi
							$bag_out = generateLuggage($row['date_entered'], $row['aircode_outbound'], $row['ticket_class_outbound'], $row['type'], $row['luggage_index_outbound']);

							if (!is_null($row['luggage_index_outbound']) && !empty($row['luggage_index_outbound'])) {
								$row['luggage_price'] = $row['luggage_index_outbound'];
							}

							$bag_out2 = $bag_out[(int) $row['luggage_price']];
							$bag_weight_out = 0;

							if (isset($bag_out2) && !empty($bag_out2)) {
								preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
								$bag_weight_out = isset($ob_output[1]) ? (int) $ob_output[1] : 0;
							}
							// if ($bag_weight_out >= 0) {
							if ($bag_weight_out > 0) {
								$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
								$baggageDescription .= $khuhoi ? ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)') : '';
							}

							// Thông tin hành lý lượt về
							$bag_in = generateLuggage($row['date_entered'], $row['aircode_inbound'], $row['ticket_class_inbound'], $row['type'], $row['luggage_index_inbound']);

							if (!empty($bag_in)) {
								if (!is_null($row['luggage_index_inbound']) && !empty($row['luggage_index_inbound'])) {
									$row['luggage_price_inbound'] = $row['luggage_index_inbound'];
								}

								$bag_in2 = $bag_in[(int) $row['luggage_price_inbound']];
								$bag_weight_in = 0;

								if (isset($bag_in2) && !empty($bag_in2)) {
									preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
									$bag_weight_in = isset($ib_output[1]) ? (int) $ib_output[1] : 0;
								}
								// if ($bag_weight_in >= 0) {
								if ($bag_weight_in > 0) {
									// $baggageDescription .= $bag_weight_out >= 0 ? ' - ' : '';
									$baggageDescription .= $bag_weight_out > 0 ? ' - ' : '';
									$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
									$baggageDescription .= $lang == 'en' ? ' (Inbound)' : ' (Lượt về)';
								}
							}
						} elseif ($direction == '1') {
							// Thông tin hành lý lượt về
							$bag_in = generateLuggage($row['date_entered'], $row['aircode_inbound'], $row['ticket_class_inbound'], $row['type'], $row['luggage_index_inbound']);

							if (!empty($bag_in)) {
								if (!is_null($row['luggage_index_inbound']) && !empty($row['luggage_index_inbound'])) {
									$row['luggage_price_inbound'] = $row['luggage_index_inbound'];
								}

								$bag_in2 = $bag_in[(int) $row['luggage_price_inbound']];
								$bag_weight_in = 0;

								if (isset($bag_in2) && !empty($bag_in2)) {
									preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
									$bag_weight_in = isset($ib_output[1]) ? (int) $ib_output[1] : 0;
								}

								if ($bag_weight_in > 0) {
									// $baggageDescription .= $bag_weight_out >= 0 ? ' - ' : '';
									$baggageDescription .= $bag_weight_out > 0 ? ' - ' : '';
									$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
									$baggageDescription .= $lang == 'en' ? ' (Inbound)' : ' (Lượt về)';
								}
							}
						} else {
							// Thông tin hành lý lượt đi
							$bag_out = generateLuggage($row['date_entered'], $row['aircode_outbound'], $row['ticket_class_outbound'], $row['type'], $row['luggage_index_outbound']);

							if (!is_null($row['luggage_index_outbound']) && !empty($row['luggage_index_outbound'])) {
								$row['luggage_price'] = $row['luggage_index_outbound'];
							}

							$bag_out2 = $bag_out[(int) $row['luggage_price']];
							$bag_weight_out = 0;

							if (isset($bag_out2) && !empty($bag_out2)) {
								preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
								$bag_weight_out = isset($ob_output[1]) ? (int) $ob_output[1] : 0;
							}
							// if ($bag_weight_out >= 0) {
							if ($bag_weight_out > 0) {
								$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
								$baggageDescription .= $khuhoi ? ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)') : '';
							}
						}
					}

					$html .= '<tr class="initital_iti">
						<td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . (empty($new_name['name']) ? $row['name'] : $new_name['name']) . '</td>
						<td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . strtoupper($pnr) . '</td>
						<td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . $baggageDescription . '</td>
					</tr>';
				}
			} else {
				$html .= '<tr><td colspan="3" style="border:1px solid #ccc; padding: 10px 7px;">Không có hành khách nào áp dụng hành trình này</td></tr>';
			}
		}

		return array('html' => $html, 'html_itineraries' => $html_itineraries, 'pass_cnt' => $rowCount, 'pass_id' => $pass_id, 'edit_no' => $iti->sabre_logs);
	}

	function getAnotherIti($booking_id, $direction, $passenger_id, $line)
	{
		// assigned_user_id IS NULL or Empty
		global $db;
		$sql = "SELECT * FROM ec_booking_itineraries 
				WHERE booking_id = '{$booking_id}'
					AND direction = '{$direction}'
					-- AND (assigned_user_id IS NULL OR assigned_user_id = '' OR assigned_user_id = '{$passenger_id}')
					AND (assigned_user_id IS NULL OR assigned_user_id = '{$passenger_id}' OR name != 'route')
					AND deleted = 0
					AND IF(sabre_logs = 0, 0, sabre_logs) <= {$line}
				ORDER BY sabre_logs DESC
				LIMIT 1";
		// global $current_user;
		// if($current_user->id == '1') pr($sql);
		$res = $db->query($sql);
		return $db->fetchByAssoc($res);
	}

	// Lấy danh sách hành trình bay
	function listOfItineraries($booking_id, $khuhoi, $way_flight = '0', $lang, $iti_id, $is_change_inf = 0)
	{
		global $db;
		$html = '';

		$sql = "SELECT 
				i.id,
				i.departure_date,
				i.arrival_date,
				i.flight_number,
				i.ticket_class,
				i.stops,
				i.departure,
				i.arrival,
				i.airline_code,
				i.direction
			FROM ec_booking_itineraries i
			WHERE i.booking_id = '$booking_id'
				AND i.deleted = 0 
				AND IF((i.sabre_logs = 0 or i.sabre_logs IS NULL), 0, i.sabre_logs) = (
					SELECT IF((sabre_logs = 0 or sabre_logs IS NULL), 0, sabre_logs)
					FROM ec_booking_itineraries
					WHERE id = '$iti_id'
				)";

		if (!$khuhoi || $is_change_inf == 1)
			$sql .= " AND i.direction = '$way_flight' ";

		$sql .= "GROUP BY IF(sabre_logs = 0, i.id, i.direction)";

		if ($this->bean->ticket_type === '2')
			$sql .= "ORDER BY i.direction, i.transit_order";
		else
			$sql .= "ORDER BY i.direction, i.departure_date, i.date_entered";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {

			if (strtoupper($row['airline_code']) === 'VN') {
				$row['airline_code'] = 'VNA';
			}

			$airline = myGetAirlineInfo2(trim($row['airline_code']), 'CODE');
			$departure = myGetAirportInfo2(trim($row['departure']));
			$arrival = myGetAirportInfo2(trim($row['arrival']));
			$direction = $row['direction'];

			$html .= '<tr class="listOfItineraries">
					<td align="center" style="border:1px solid #ccc; padding: 10px 7px; line-height: 20px;">' . (trim($row['departure_date']) != '' ? date('d/m/Y', strtotime(empty($new_flight_time['departure_date']) ? $row['departure_date'] : $new_flight_time['departure_date'])) : '&nbsp;') . ' <br /> ' . (trim($row['departure_date']) != '' ? date('H:i', strtotime(empty($new_flight_time['departure_date']) ? $row['departure_date'] : $new_flight_time['departure_date'])) : '&nbsp;') . ' - ' . (trim($row['arrival_date']) != '' ? date('H:i', strtotime(empty($new_flight_time['arrival_date']) ? $row['arrival_date'] : $new_flight_time['arrival_date'])) : '&nbsp;') . '</td>
					<td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . ($airline['data'][0]['name'] ?? '') . '</td>
					<td align="center" style="border:1px solid #ccc; padding: 10px 7px;">' . (empty($new_flight_time['flight_number']) ? $row['flight_number'] : $new_flight_time['flight_number']) . '</td>
					<td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')</td>
					<td align="left" style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')</td>
				</tr>';
		}

		return array('html' => $html, 'direction' => $direction);
	}

	function checkNewInfo($type, $booking_id, $parent_id)
	{
		global $db;
		if ($type == 1) {
			$sql = 'SELECT * FROM ec_booking_passengers 
					WHERE booking_id = "' . $booking_id . '" AND parent_detail_id = "' . $parent_id . '" AND add_type = 1
					ORDER BY date_entered DESC
					LIMIT 1';
		}
		if ($type == 2) {
			$sql = 'SELECT name FROM ec_booking_passengers 
					WHERE booking_id = "' . $booking_id . '" AND parent_detail_id = "' . $parent_id . '" AND add_type = 2
					ORDER BY date_entered DESC
					LIMIT 1';
		}
		if ($type == 3) {
			$sql = 'SELECT * FROM ec_booking_itineraries
					WHERE booking_id = "' . $booking_id . '" AND parent_detail_id = "' . $parent_id . '" AND add_type = 3
					ORDER BY date_entered DESC
					LIMIT 1';
		}
		$res = $db->query($sql);
		$row = $db->fetchByAssoc($res);
		return $row;
	}
}
