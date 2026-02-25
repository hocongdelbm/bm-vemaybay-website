<?php
require_once("include/Sugar_Smarty.php");
if (file_exists("custom/include/utils/Flight.php")) require_once("custom/include/utils/Flight.php");
if (file_exists("custom/include/utils/string.php")) require_once("custom/include/utils/string.php");
if (file_exists("custom/include/utils/Baggage.php")) require_once("custom/include/utils/Baggage.php");

class Viewprinteticketnew extends SugarView
{
	public $sugarSmarty;
	public $lang;
	public $isRoundTrip;
	public $bookingId;
	public $bookingName;
	public $ticketType;
	public $passengerIds;
	public $itineraryIds;

	function display()
	{
		$this->sugarSmarty = new Sugar_Smarty();
		$this->lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn';
		$this->isRoundTrip = (isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi'])) ? (int)$_REQUEST['khuhoi'] : 0;
		$this->bookingId = $_REQUEST['booking_id'] ?? '';
		$this->bookingName = $_REQUEST['booking'] ?? '';
		$this->ticketType = $_REQUEST['ticket_type'] ?? '1';

		// Parse comma-separated IDs from GET params ("All" = select all)
		$this->passengerIds = [];
		$this->allPassengers = false;
		if (isset($_REQUEST['passengers']) && !empty($_REQUEST['passengers'])) {
			if (strtolower($_REQUEST['passengers']) === 'all') {
				$this->allPassengers = true;
				$this->passengerIds = ['all']; // placeholder so empty check passes
			} else {
				$this->passengerIds = array_filter(explode(',', $_REQUEST['passengers']));
			}
		}
		$this->itineraryIds = [];
		$this->allItineraries = false;
		if (isset($_REQUEST['itineraries']) && !empty($_REQUEST['itineraries'])) {
			if (strtolower($_REQUEST['itineraries']) === 'all') {
				$this->allItineraries = true;
				$this->itineraryIds = ['all']; // placeholder so empty check passes
			} else {
				$this->itineraryIds = array_filter(explode(',', $_REQUEST['itineraries']));
			}
		}

		if (empty($this->bookingId) || empty($this->passengerIds) || empty($this->itineraryIds)) {
			echo '<h3 style="text-align:center; padding:40px;">Thiếu thông tin booking, hành khách hoặc hành trình.</h3>';
			return;
		}

		$this->populateContent();
		$this->sugarSmarty->display("modules/EC_Flight_Bookings/tpls/view_printeticketnew.tpl");
	}

	function populateContent()
	{
		// Department info
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // MHV

		$com_phone = $department_info['com_phone'];
		if (!empty($department_info['com_hotline1'])) $com_phone .= ' - ' . $department_info['com_hotline1'];
		if (!empty($department_info['com_hotline2'])) $com_phone .= ' - ' . $department_info['com_hotline2'];

		// Get data from DB
		$passengers = $this->getPassengers();
		$itineraries = $this->getItineraries();

		// Build boarding pass data
		$this->sugarSmarty->assign('PASSENGERS', $passengers);
		$this->sugarSmarty->assign('ITINERARIES', $itineraries);
		$this->sugarSmarty->assign('IS_ROUND_TRIP', $this->isRoundTrip);
		$this->sugarSmarty->assign('LANG', $this->lang);
		$this->sugarSmarty->assign('BOOKING_NUMBER', $this->bookingName);
		$this->sugarSmarty->assign('COM_NAME', ($this->lang == 'en' ? removeAccents($department_info['com_name']) : $department_info['com_name']));
		$this->sugarSmarty->assign('COM_TAXCODE', $department_info['com_taxcode']);
		$this->sugarSmarty->assign('COM_ADDRESS', ($this->lang == 'en' ? $department_info['com_address2'] : $department_info['com_address']));
		$this->sugarSmarty->assign('COM_TOP_PHONE', $department_info['com_phone']);
		$this->sugarSmarty->assign('COM_PHONE', $com_phone);
		$this->sugarSmarty->assign('COM_WEBSITE', $department_info['com_website2']);
		$this->sugarSmarty->assign('COM_EMAIL', $department_info['com_email']);
		$this->sugarSmarty->assign('IMAGE_URL_LARGE', $department_info['company_logo']);
		$this->sugarSmarty->assign('MINUTE_BEFORE', $this->ticketType == '2' ? '180' : '120');
	}

	/**
	 * Get passengers from DB by IDs
	 * - Original passengers: add_type NOT IN (1,2), not in any rename chain
	 * - Renamed passengers: add_type=2, final version only (ID not used as parent_detail_id by another)
	 * Note: parent_detail_id chains (original→lần1→lần2), so we exclude
	 *       any add_type=2 whose ID appears as another record's parent_detail_id
	 */
	function getPassengers()
	{
		global $db;
		$results = [];

		if (empty($this->passengerIds)) return $results;

		$bookingId = $db->quote($this->bookingId);

		// Build ID filter (skip if all passengers requested)
		$idFilter = '';
		if (!$this->allPassengers) {
			$idList = "'" . implode("','", array_map(function($id) { return preg_replace('/[^a-zA-Z0-9\-]/', '', $id); }, $this->passengerIds)) . "'";
			$idFilter = "AND p.id IN($idList)";
		}

		$fields = "p.id, p.name, p.salutation, p.type,
				p.pnr_outbound, p.pnr_inbound,
				p.eticket_outbound, p.eticket_inbound,
				p.luggage_index_outbound, p.luggage_index_inbound,
				p.luggage_purchase_text, p.luggage_purchase_text_inbound,
				p.cic, p.passport_number, p.date_entered";

		// Collect ALL IDs that appear as parent_detail_id (= have been superseded)
		$supersededIds = "SELECT parent_detail_id FROM ec_booking_passengers
				WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
				AND parent_detail_id IS NOT NULL";

		// Original passengers not superseded by any rename
		$sqlUnchanged = "SELECT $fields, NULL AS parent_detail_id
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$bookingId'
				$idFilter
				AND p.deleted = 0
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
				AND p.id NOT IN ($supersededIds)";

		// Final renamed version: add_type=2 whose own ID is NOT another's parent_detail_id
		// Use subquery to inherit baggage from original passenger when renamed has empty baggage
		$sqlRenamed = "SELECT p.id, p.name, p.salutation, p.type,
				p.pnr_outbound, p.pnr_inbound,
				p.eticket_outbound, p.eticket_inbound,
				COALESCE(NULLIF(p.luggage_index_outbound,''), orig.luggage_index_outbound) AS luggage_index_outbound,
				COALESCE(NULLIF(p.luggage_index_inbound,''), orig.luggage_index_inbound) AS luggage_index_inbound,
				COALESCE(NULLIF(p.luggage_purchase_text,''), orig.luggage_purchase_text) AS luggage_purchase_text,
				COALESCE(NULLIF(p.luggage_purchase_text_inbound,''), orig.luggage_purchase_text_inbound) AS luggage_purchase_text_inbound,
				p.cic, p.passport_number, p.date_entered, p.parent_detail_id
			FROM ec_booking_passengers p
			LEFT JOIN ec_booking_passengers orig ON orig.booking_id = p.booking_id
				AND (orig.add_type IS NULL OR orig.add_type = 0)
				AND orig.deleted = 0
				AND orig.type = p.type
				AND orig.id IN ($supersededIds)
			WHERE p.booking_id = '$bookingId'
				AND p.add_type = 2
				AND p.deleted = 0
				AND p.id NOT IN ($supersededIds)";

		$sql = "($sqlUnchanged) UNION ALL ($sqlRenamed) ORDER BY type, date_entered";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$salutationText = '';
			if ($this->lang == 'en') {
				$salutationText = ($row['salutation'] == '0') ? 'Mr.' : 'Ms.';
			} else {
				$salutationText = ($row['salutation'] == '0') ? 'Ông' : 'Bà';
			}

			$pnrOut = trim($row['pnr_outbound'] ?? '');
			$pnrIn = trim($row['pnr_inbound'] ?? '');
			$eticketOut = trim($row['eticket_outbound'] ?? '');
			$eticketIn = trim($row['eticket_inbound'] ?? '');

			// Determine PNR display
			$pnr = '';
			if ($this->isRoundTrip) {
				$pnrDisplay = !empty($pnrOut) ? $pnrOut : $eticketOut;
				$pnrDisplay2 = !empty($pnrIn) ? $pnrIn : $eticketIn;
				if ($pnrDisplay === $pnrDisplay2) {
					$pnr = $pnrDisplay;
				} else {
					$pnr = $pnrDisplay;
					if (!empty($pnrDisplay2)) $pnr .= ' / ' . $pnrDisplay2;
				}
			} else {
				$pnr = !empty($pnrOut) ? $pnrOut : $eticketOut;
			}

			// Baggage info — sum kg from available + purchased
			$baggageOut = $this->sumBaggage($row['luggage_index_outbound'] ?? '', $row['luggage_purchase_text'] ?? '');
			$baggageIn = '';
			if ($this->isRoundTrip) {
				$baggageIn = $this->sumBaggage($row['luggage_index_inbound'] ?? '', $row['luggage_purchase_text_inbound'] ?? '');
			}

			$typeLabel = '';
			if ($row['type'] == '0') $typeLabel = ($this->lang == 'en' ? 'Adult' : 'Người lớn');
			elseif ($row['type'] == '1') $typeLabel = ($this->lang == 'en' ? 'Child' : 'Trẻ em');
			elseif ($row['type'] == '2') $typeLabel = ($this->lang == 'en' ? 'Infant' : 'Em bé');

			$results[] = [
				'id' => $row['id'],
				'name' => strtoupper(trim($row['name'])),
				'salutation' => $salutationText,
				'type' => $typeLabel,
				'pnr' => strtoupper($pnr),
				'eticket_outbound' => strtoupper($eticketOut),
				'eticket_inbound' => strtoupper($eticketIn),
				'baggage_outbound' => $baggageOut,
				'baggage_inbound' => $baggageIn,
				'cic' => $row['cic'] ?? '',
				'passport' => $row['passport_number'] ?? '',
			];
		}

		return $results;
	}

	/**
	 * Sum available baggage + purchased baggage into total kg
	 * e.g. available "23_1" (23kg) + purchased "20kg" = "43kg"
	 */
	function sumBaggage($luggageIndex, $purchaseText)
	{
		$totalKg = 0;
		$hasData = false;

		// Available baggage (from luggage_index field)
		if (!empty($luggageIndex) && class_exists('Baggage')) {
			$rendered = Baggage::renderAvailableBaggage($luggageIndex);
			$parsed = Baggage::parsePackage($rendered);
			if (!empty($parsed['weight'])) {
				$totalKg += $parsed['weight'];
				$hasData = true;
			} elseif (!empty($rendered)) {
				// Fallback: if can't parse kg (e.g. "1 kiện"), return text as-is
				if (empty($purchaseText)) return strip_tags($rendered);
				return strip_tags($rendered . ' + ' . preg_replace('/\s*\([^)]*\)/', '', $purchaseText));
			}
		}

		// Purchased baggage (from luggage_purchase_text field)
		if (!empty($purchaseText)) {
			$cleanText = preg_replace('/\s*\([^)]*\)/', '', $purchaseText);
			$parsed = Baggage::parsePackage($cleanText);
			if (!empty($parsed['weight'])) {
				$totalKg += $parsed['weight'];
				$hasData = true;
			} elseif (!empty($cleanText)) {
				// Fallback: can't parse, append text
				if ($totalKg > 0) return $totalKg . 'kg + ' . strip_tags($cleanText);
				return strip_tags($cleanText);
			}
		}

		return $hasData ? $totalKg . 'kg' : '';
	}

	/**
	 * Get itineraries from DB by IDs
	 * - Original itineraries: add_type=0, no rescheduled version exists for that direction
	 * - Rescheduled itineraries: add_type=3, latest by sabre_logs per direction
	 * Note: parent_detail_id is NOT set for itinerary changes;
	 *       changes are tracked by direction + sabre_logs
	 */
	function getItineraries()
	{
		global $db;
		$results = [];

		if (empty($this->itineraryIds)) return $results;

		$bookingId = $db->quote($this->bookingId);

		// Build ID filter (skip if all itineraries requested)
		$idFilter = '';
		if (!$this->allItineraries) {
			$idList = "'" . implode("','", array_map(function($id) { return preg_replace('/[^a-zA-Z0-9\-]/', '', $id); }, $this->itineraryIds)) . "'";
			$idFilter = "AND i.id IN($idList)";
		}

		$fields = "i.id, i.departure_date, i.arrival_date, i.flight_number,
				i.ticket_class, i.departure, i.arrival, i.airline_code, i.direction";

		// Check which directions have been rescheduled (have add_type=3 records)
		$rescheduledDirections = [];
		$sqlCheck = "SELECT DISTINCT direction FROM ec_booking_itineraries
			WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0";
		$resCheck = $db->query($sqlCheck);
		while ($row = $db->fetchByAssoc($resCheck)) {
			$rescheduledDirections[] = (int)$row['direction'];
		}

		if (empty($rescheduledDirections)) {
			// No rescheduled itineraries — just get originals
			$sql = "SELECT $fields
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId'
					$idFilter
					AND i.deleted = 0
					AND i.add_type = 0
				ORDER BY i.direction, i.departure_date";
		} else {
			$rescheduledDirList = implode(',', $rescheduledDirections);

			// Original itineraries for directions NOT rescheduled
			$sqlUnchanged = "SELECT $fields
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId'
					$idFilter
					AND i.deleted = 0
					AND i.add_type = 0
					AND i.direction NOT IN ($rescheduledDirList)";

			// Latest rescheduled itineraries (max sabre_logs per direction)
			$sqlRescheduled = "SELECT $fields
				FROM ec_booking_itineraries i
				INNER JOIN (
					SELECT direction, MAX(sabre_logs) AS max_logs
					FROM ec_booking_itineraries
					WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0
					GROUP BY direction
				) latest ON i.direction = latest.direction AND i.sabre_logs = latest.max_logs
				WHERE i.booking_id = '$bookingId'
					AND i.add_type = 3
					AND i.deleted = 0";

			$sql = "($sqlUnchanged) UNION ALL ($sqlRescheduled) ORDER BY direction, departure_date";
		}

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$depCode = $row['departure'] ?? '';
			$arrCode = $row['arrival'] ?? '';
			$airlineCode = $row['airline_code'] ?? '';

			// Get airport names
			$depAirport = Flight::getAirport($depCode);
			$arrAirport = Flight::getAirport($arrCode);
			$airline = Flight::getAirline($airlineCode);

			$depCityName = $depAirport['CityName'] ?? $depCode;
			$arrCityName = $arrAirport['CityName'] ?? $arrCode;
			$depAirportName = $depAirport['AirPortName'] ?? '';
			$arrAirportName = $arrAirport['AirPortName'] ?? '';

			$results[] = [
				'id' => $row['id'],
				'direction' => (int)($row['direction'] ?? 0),
				'direction_label' => ((int)$row['direction'] === 0) 
					? ($this->lang == 'en' ? 'Outbound' : 'Lượt đi') 
					: ($this->lang == 'en' ? 'Inbound' : 'Lượt về'),
				'airline_code' => $airlineCode,
				'airline' => $airline,
				'flight_number' => $row['flight_number'] ?? '',
				'ticket_class' => $row['ticket_class'] ?? '',
				'dep_code' => $depCode,
				'arr_code' => $arrCode,
				'dep_city' => $depCityName,
				'arr_city' => $arrCityName,
				'dep_airport' => $depAirportName,
				'arr_airport' => $arrAirportName,
				'dep_date' => date('d/m/Y', strtotime($row['departure_date'])),
				'dep_time' => date('H:i', strtotime($row['departure_date'])),
				'arr_date' => date('d/m/Y', strtotime($row['arrival_date'])),
				'arr_time' => date('H:i', strtotime($row['arrival_date'])),
			];
		}

		return $results;
	}
}
