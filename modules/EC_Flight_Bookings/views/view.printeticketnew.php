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
	public $allPassengers;
	public $allItineraries;
	public $isPrintTicketMode = true;
	private $bookingBean = null;

	function display()
	{
		$this->sugarSmarty = new Sugar_Smarty();
		$this->lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn';
		$this->isRoundTrip = (isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi'])) ? (int)$_REQUEST['khuhoi'] : 0;
		$this->bookingId = $_REQUEST['booking_id'] ?? '';
		$this->bookingName = $_REQUEST['booking'] ?? '';
		$this->ticketType = $_REQUEST['ticket_type'] ?? '1';
		$this->isPrintTicketMode = true;

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

		// Get passengers from DB
		$passengers = $this->getPassengers();

		// Build passenger groups — each group shares the same itinerary set
		// When itinerary changes exist (add_type=3), resolve per-passenger;
		// different passengers may have different itineraries.
		if ($this->hasItineraryChanges()) {
			$groups = [];
			foreach ($passengers as $pax) {
				$paxItineraries = $this->getItinerariesForPassenger($pax['id']);
				$sig = $this->itinerarySignature($paxItineraries);
				if (!isset($groups[$sig])) {
					$groups[$sig] = [
						'itineraries' => $paxItineraries,
						'passengers' => [],
					];
				}
				$groups[$sig]['passengers'][] = $pax;
			}
			$passengerGroups = array_values($groups);
		} else {
			// No itinerary changes — single group with original itineraries
			$itineraries = $this->getItineraries();
			$passengerGroups = [[
				'itineraries' => $itineraries,
				'passengers' => $passengers,
			]];
		}

		// Assign data to template
		$this->sugarSmarty->assign('PASSENGER_GROUPS', $passengerGroups);
		$this->sugarSmarty->assign('IS_PRINT_TICKET_MODE', (bool)$this->isPrintTicketMode);
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
			$idList = "'" . implode("','", array_map(function ($id) {
				return preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
			}, $this->passengerIds)) . "'";
			$idFilter = "AND p.id IN($idList)";
		}

		$fields = "p.id, p.name, p.salutation, p.type,
				p.pnr_outbound, p.pnr_inbound,
				p.eticket_outbound, p.eticket_inbound,
				p.luggage_index_outbound, p.luggage_index_inbound,
				p.luggage_purchase_text, p.luggage_purchase_text_inbound,
				p.hand_baggage_outbound, p.hand_baggage_inbound,
				p.cic, p.passport_number, p.date_entered";

		// Collect ALL IDs that appear as parent_detail_id (= have been superseded)
		$supersededIds = "SELECT parent_detail_id FROM ec_booking_passengers
				WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
				AND parent_detail_id IS NOT NULL";

		// Also collect IDs of add_type=2 records that are NOT the latest per parent_detail_id
		// This handles cases where multiple renames point to the same parent_detail_id
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

		// Original passengers not superseded by any rename
		// LEFT JOIN add_type=1 records (luggage rows) to inherit baggage data.
		// Also pull aircode/ticket_class from itineraries for old-style generateLuggage().
		$sqlUnchanged = "SELECT
				p.id, p.name, p.salutation, p.type,
				p.pnr_outbound, p.pnr_inbound,
				p.eticket_outbound, p.eticket_inbound,
				COALESCE(NULLIF(p.luggage_index_outbound,''), bag.luggage_index_outbound) AS luggage_index_outbound,
				COALESCE(NULLIF(p.luggage_index_inbound,''), bag.luggage_index_inbound) AS luggage_index_inbound,
				COALESCE(NULLIF(p.luggage_purchase_text,''), bag.luggage_purchase_text) AS luggage_purchase_text,
				COALESCE(NULLIF(p.luggage_purchase_text_inbound,''), bag.luggage_purchase_text_inbound) AS luggage_purchase_text_inbound,
				COALESCE(NULLIF(p.luggage_price, 0), bag.luggage_price) AS luggage_price,
				COALESCE(NULLIF(p.luggage_price_inbound, 0), bag.luggage_price_inbound) AS luggage_price_inbound,
				p.hand_baggage_outbound, p.hand_baggage_inbound,
				p.cic, p.passport_number, p.date_entered, NULL AS parent_detail_id,
				(SELECT DATE_ADD(fb.date_entered, INTERVAL 7 HOUR) FROM ec_flight_bookings fb WHERE fb.id = p.booking_id LIMIT 1) AS bk_date_entered,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '0' AND i.deleted = 0 LIMIT 1) AS aircode_outbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '0' AND i.deleted = 0 LIMIT 1) AS ticket_class_outbound,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '1' AND i.deleted = 0 LIMIT 1) AS aircode_inbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '1' AND i.deleted = 0 LIMIT 1) AS ticket_class_inbound
			FROM ec_booking_passengers p
			LEFT JOIN (
				SELECT 
					bag_inner.parent_detail_id,
					MAX(NULLIF(bag_inner.luggage_index_outbound, '')) as luggage_index_outbound,
					MAX(NULLIF(bag_inner.luggage_index_inbound, '')) as luggage_index_inbound,
					MAX(NULLIF(bag_inner.luggage_purchase_text, '')) as luggage_purchase_text,
					MAX(NULLIF(bag_inner.luggage_purchase_text_inbound, '')) as luggage_purchase_text_inbound,
					MAX(bag_inner.luggage_price) as luggage_price,
					MAX(bag_inner.luggage_price_inbound) as luggage_price_inbound
				FROM ec_booking_passengers bag_inner
				INNER JOIN (
					SELECT parent_detail_id, MAX(date_entered) as max_date
					FROM ec_booking_passengers
					WHERE booking_id = '$bookingId' AND add_type = 1 AND deleted = 0
					GROUP BY parent_detail_id
				) bag_latest ON bag_inner.parent_detail_id = bag_latest.parent_detail_id 
					AND bag_inner.date_entered = bag_latest.max_date
				WHERE bag_inner.booking_id = '$bookingId' AND bag_inner.add_type = 1 AND bag_inner.deleted = 0
				GROUP BY bag_inner.parent_detail_id
			) bag ON bag.parent_detail_id = p.id
			WHERE p.booking_id = '$bookingId'
				$idFilter
				AND p.deleted = 0
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
				AND p.id NOT IN ($supersededIds)";

		// Final renamed version: add_type=2 whose own ID is NOT another's parent_detail_id
		// AND is the latest record when multiple renames share the same parent_detail_id
		// Use TWO LEFT JOINs for luggage: one for renamed pax luggage, one for original pax luggage
		// Priority: renamed pax luggage > original pax luggage > original pax own fields
		$sqlRenamed = "SELECT p.id, p.name, p.salutation, p.type,
				p.pnr_outbound, p.pnr_inbound,
				p.eticket_outbound, p.eticket_inbound,
				COALESCE(NULLIF(p.luggage_index_outbound,''), NULLIF(bag_renamed.luggage_index_outbound,''), NULLIF(bag_orig.luggage_index_outbound,''), orig.luggage_index_outbound) AS luggage_index_outbound,
				COALESCE(NULLIF(p.luggage_index_inbound,''), NULLIF(bag_renamed.luggage_index_inbound,''), NULLIF(bag_orig.luggage_index_inbound,''), orig.luggage_index_inbound) AS luggage_index_inbound,
				COALESCE(NULLIF(p.luggage_purchase_text,''), NULLIF(bag_renamed.luggage_purchase_text,''), NULLIF(bag_orig.luggage_purchase_text,''), orig.luggage_purchase_text) AS luggage_purchase_text,
				COALESCE(NULLIF(p.luggage_purchase_text_inbound,''), NULLIF(bag_renamed.luggage_purchase_text_inbound,''), NULLIF(bag_orig.luggage_purchase_text_inbound,''), orig.luggage_purchase_text_inbound) AS luggage_purchase_text_inbound,
				COALESCE(NULLIF(p.luggage_price, 0), NULLIF(bag_renamed.luggage_price, 0), NULLIF(bag_orig.luggage_price, 0), orig.luggage_price) AS luggage_price,
				COALESCE(NULLIF(p.luggage_price_inbound, 0), NULLIF(bag_renamed.luggage_price_inbound, 0), NULLIF(bag_orig.luggage_price_inbound, 0), orig.luggage_price_inbound) AS luggage_price_inbound,
				COALESCE(NULLIF(p.hand_baggage_outbound,''), orig.hand_baggage_outbound) AS hand_baggage_outbound,
				COALESCE(NULLIF(p.hand_baggage_inbound,''), orig.hand_baggage_inbound) AS hand_baggage_inbound,
				p.cic, p.passport_number, p.date_entered, p.parent_detail_id,
				(SELECT DATE_ADD(fb.date_entered, INTERVAL 7 HOUR) FROM ec_flight_bookings fb WHERE fb.id = p.booking_id LIMIT 1) AS bk_date_entered,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '0' AND i.deleted = 0 LIMIT 1) AS aircode_outbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '0' AND i.deleted = 0 LIMIT 1) AS ticket_class_outbound,
				(SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '1' AND i.deleted = 0 LIMIT 1) AS aircode_inbound,
				(SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id = p.booking_id AND i.direction = '1' AND i.deleted = 0 LIMIT 1) AS ticket_class_inbound
			FROM ec_booking_passengers p
			LEFT JOIN (
				SELECT 
					bag_inner.parent_detail_id,
					MAX(NULLIF(bag_inner.luggage_index_outbound, '')) as luggage_index_outbound,
					MAX(NULLIF(bag_inner.luggage_index_inbound, '')) as luggage_index_inbound,
					MAX(NULLIF(bag_inner.luggage_purchase_text, '')) as luggage_purchase_text,
					MAX(NULLIF(bag_inner.luggage_purchase_text_inbound, '')) as luggage_purchase_text_inbound,
					MAX(bag_inner.luggage_price) as luggage_price,
					MAX(bag_inner.luggage_price_inbound) as luggage_price_inbound
				FROM ec_booking_passengers bag_inner
				INNER JOIN (
					SELECT parent_detail_id, MAX(date_entered) as max_date
					FROM ec_booking_passengers
					WHERE booking_id = '$bookingId' AND add_type = 1 AND deleted = 0
					GROUP BY parent_detail_id
				) bag_latest ON bag_inner.parent_detail_id = bag_latest.parent_detail_id 
					AND bag_inner.date_entered = bag_latest.max_date
				WHERE bag_inner.booking_id = '$bookingId' AND bag_inner.add_type = 1 AND bag_inner.deleted = 0
				GROUP BY bag_inner.parent_detail_id
			) bag_renamed ON bag_renamed.parent_detail_id = p.id
			LEFT JOIN (
				SELECT 
					bag_inner.parent_detail_id,
					MAX(NULLIF(bag_inner.luggage_index_outbound, '')) as luggage_index_outbound,
					MAX(NULLIF(bag_inner.luggage_index_inbound, '')) as luggage_index_inbound,
					MAX(NULLIF(bag_inner.luggage_purchase_text, '')) as luggage_purchase_text,
					MAX(NULLIF(bag_inner.luggage_purchase_text_inbound, '')) as luggage_purchase_text_inbound,
					MAX(bag_inner.luggage_price) as luggage_price,
					MAX(bag_inner.luggage_price_inbound) as luggage_price_inbound
				FROM ec_booking_passengers bag_inner
				INNER JOIN (
					SELECT parent_detail_id, MAX(date_entered) as max_date
					FROM ec_booking_passengers
					WHERE booking_id = '$bookingId' AND add_type = 1 AND deleted = 0
					GROUP BY parent_detail_id
				) bag_latest ON bag_inner.parent_detail_id = bag_latest.parent_detail_id 
					AND bag_inner.date_entered = bag_latest.max_date
				WHERE bag_inner.booking_id = '$bookingId' AND bag_inner.add_type = 1 AND bag_inner.deleted = 0
				GROUP BY bag_inner.parent_detail_id
			) bag_orig ON bag_orig.parent_detail_id = p.parent_detail_id
			LEFT JOIN ec_booking_passengers orig ON orig.booking_id = p.booking_id
				AND (orig.add_type IS NULL OR orig.add_type = 0)
				AND orig.deleted = 0
				AND orig.type = p.type
				AND orig.id = p.parent_detail_id
			WHERE p.booking_id = '$bookingId'
				AND p.add_type = 2
				AND p.deleted = 0
				AND p.parent_detail_id IS NOT NULL
				AND p.id NOT IN ($supersededIds)
				AND p.id NOT IN ($notLatestRenames)";

		if (!$this->allPassengers) {
			// In case the frontend passes an ID which is a renamed record, or an original record that was renamed.
			// Because we don't know if JS gave us the original ID or the latest ID,
			// we just get the names of the requested passengers from the database,
			// and then wrap the main query to filter by name.
			$nameListSql = "SELECT name, type FROM ec_booking_passengers WHERE id IN ($idList) AND booking_id = '$bookingId'";
			$sql = "SELECT * FROM ( ($sqlUnchanged) UNION ALL ($sqlRenamed) ) AS combined
					WHERE EXISTS (
						SELECT 1 FROM ($nameListSql) AS req
						WHERE TRIM(req.name) = TRIM(combined.name) AND req.type = combined.type
					)
					ORDER BY type, date_entered DESC";
		} else {
			$sql = "($sqlUnchanged) UNION ALL ($sqlRenamed) ORDER BY type, date_entered DESC";
		}

		$res = $db->query($sql);
		$seen = []; // Dedup safety net: track by name+pnr_outbound+type

		// Determine which directions are selected based on itineraryIds
		$selectedDir = 0;
		if (!$this->isRoundTrip && !$this->allItineraries && !empty($this->itineraryIds)) {
			$idList = "'" . implode("','", array_map(function ($id) {
				return preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
			}, $this->itineraryIds)) . "'";
			$bookingId = $db->quote($this->bookingId);
			$sqlDir = "SELECT DISTINCT direction FROM ec_booking_itineraries WHERE id IN ($idList) AND booking_id = '$bookingId'";
			$resDir = $db->query($sqlDir);
			$dirs = [];
			while ($rDir = $db->fetchByAssoc($resDir)) {
				$dirs[] = (int)$rDir['direction'];
			}
			if (count($dirs) === 1 && $dirs[0] === 1) {
				$selectedDir = 1;
			}
		}

		while ($row = $db->fetchByAssoc($res)) {
			// PHP-level deduplication: skip if same name+pnr+type already added
			$dedupKey = mb_strtoupper(trim($row['name']), 'UTF-8') . '|' . trim($row['pnr_outbound'] ?? '') . '|' . $row['type'];
			if (isset($seen[$dedupKey])) continue;
			$seen[$dedupKey] = true;

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
				$pnrDisplay = strtoupper(!empty($pnrOut) ? $pnrOut : $eticketOut);
				$pnrDisplay2 = strtoupper(!empty($pnrIn) ? $pnrIn : $eticketIn);
				if ($pnrDisplay === $pnrDisplay2) {
					$pnr = $pnrDisplay;
				} else {
					$outbound_label = ($this->lang == 'en' ? 'Outbound' : 'Lượt đi');
					$inbound_label = ($this->lang == 'en' ? 'Inbound' : 'Lượt về');
					$pnr = $pnrDisplay;
					if (!empty($pnrDisplay2)) $pnr .= " ($outbound_label) / $pnrDisplay2 ($inbound_label)";
				}

				// Hide passenger if they have KHONG BAY on BOTH legs in round-trip view
				$ob_kb = (str_replace(['Ô', 'Õ', 'Ỏ', 'Ó', 'Ọ'], 'O', mb_strtoupper($pnrDisplay, 'UTF-8')) === 'KHONG BAY');
				$ib_kb = (str_replace(['Ô', 'Õ', 'Ỏ', 'Ó', 'Ọ'], 'O', mb_strtoupper($pnrDisplay2, 'UTF-8')) === 'KHONG BAY');
				if ($ob_kb && $ib_kb) {
					continue;
				}
			} else {
				if ($selectedDir == 1) {
					$pnr = strtoupper(!empty($pnrIn) ? $pnrIn : $eticketIn);
				} else {
					$pnr = strtoupper(!empty($pnrOut) ? $pnrOut : $eticketOut);
				}

				// Hide passenger if they have KHONG BAY on this specific leg
				$pnr_check = str_replace(['Ô', 'Õ', 'Ỏ', 'Ó', 'Ọ'], 'O', mb_strtoupper($pnr, 'UTF-8'));
				if ($pnr_check === 'KHONG BAY') {
					continue;
				}
			}

			// Prepare row for baggage parsing if printing only inbound
			$rowForBaggage = $row;
			if (!$this->isRoundTrip && $selectedDir == 1) {
				$rowForBaggage['luggage_index_outbound'] = $rowForBaggage['luggage_index_inbound'] ?? '';
				$rowForBaggage['luggage_purchase_text'] = $rowForBaggage['luggage_purchase_text_inbound'] ?? '';
				$rowForBaggage['hand_baggage_outbound'] = $rowForBaggage['hand_baggage_inbound'] ?? '';
				$rowForBaggage['luggage_price'] = $rowForBaggage['luggage_price_inbound'] ?? 0;
				$rowForBaggage['aircode_outbound'] = $rowForBaggage['aircode_inbound'] ?? '';
				$rowForBaggage['ticket_class_outbound'] = $rowForBaggage['ticket_class_inbound'] ?? '';
			}

			// Baggage info — mirror exact logic of old print ticket (isUseNewBaggage 2-branch)
			$baggageDescription = $this->buildBaggageDescription($rowForBaggage);
			$baggageDetails = $this->buildBaggageDetails($rowForBaggage);

			if (!$this->isRoundTrip) {
				$baggageDetails['hand_baggage_inbound'] = '';
				$baggageDetails['baggage_inbound'] = '';
			}

			$typeLabel = '';
			if ($row['type'] == '0') $typeLabel = ($this->lang == 'en' ? 'Adult' : 'Người lớn');
			elseif ($row['type'] == '1') $typeLabel = ($this->lang == 'en' ? 'Child' : 'Trẻ em');
			elseif ($row['type'] == '2') $typeLabel = ($this->lang == 'en' ? 'Infant' : 'Em bé');

			$results[] = [
				'id' => $row['id'],
				'parent_detail_id'      => $row['parent_detail_id'] ?? null,
				'name' => mb_strtoupper(trim($row['name']), 'UTF-8'),
				'salutation' => $salutationText,
				'type' => $typeLabel,
				'pnr' => $pnr,
				'pnr_inbound' => strtoupper(!empty($pnrIn) ? $pnrIn : ''),
				'eticket_outbound' => strtoupper($eticketOut),
				'eticket_inbound' => strtoupper($eticketIn),
				'baggage' => $baggageDescription,
				'hand_baggage_outbound' => $baggageDetails['hand_baggage_outbound'],
				'hand_baggage_inbound' => $baggageDetails['hand_baggage_inbound'],
				'baggage_outbound' => $baggageDetails['baggage_outbound'],
				'baggage_inbound' => $baggageDetails['baggage_inbound'],
				'cic' => $row['cic'] ?? '',
				'passport' => $row['passport_number'] ?? '',
			];
		}

		return $results;
	}

	/**
	 * Build baggage description using the same 2-branch logic as the old print ticket.
	 * Branch 1 (isUseNewBaggage=true): Baggage::renderAvailableBaggage() + generateCombinedPassengerBaggageInfo()
	 * Branch 2 (isUseNewBaggage=false): Clean purchase_text directly + generateLuggage() for old-format index
	 */
	function buildBaggageDescription($row)
	{
		$lang = $this->lang == 'en' ? 'en' : 'vn';
		$khuhoi = $this->isRoundTrip;

		// Load booking bean once (cached)
		if ($this->bookingBean === null) {
			$this->bookingBean = new EC_Flight_Bookings();
			$this->bookingBean->retrieve($this->bookingId);
		}

		// Use booking date_entered for isUseNewBaggage check (same as old ticket)
		$dateEntered = $row['bk_date_entered'] ?? ($row['date_entered'] ?? '');
		$baggageDescription = '';

		if ($this->bookingBean->isUseNewBaggage($dateEntered, $this->bookingBean->created_by)) {
			// New baggage format (after 2025-10-01 via website)
			$availOut = class_exists('Baggage') ? Baggage::renderAvailableBaggage($row['luggage_index_outbound'] ?? '') : '';
			$availIn  = class_exists('Baggage') ? Baggage::renderAvailableBaggage($row['luggage_index_inbound']  ?? '') : '';

			$purchOut = $this->cleanPurchaseText($row['luggage_purchase_text'] ?? '');
			$purchIn  = $this->cleanPurchaseText($row['luggage_purchase_text_inbound'] ?? '');

			if ($khuhoi)
				$baggageDescription = $this->bookingBean->generateCombinedPassengerBaggageInfo($availOut, $purchOut, $availIn, $purchIn, $lang);
			else
				$baggageDescription = $this->bookingBean->generateCombinedPassengerBaggageInfo($availOut, $purchOut, '', '', $lang);
		} else {
			// Old baggage format
			$purchOut = $this->cleanPurchaseText($row['luggage_purchase_text'] ?? '');
			$purchIn  = $this->cleanPurchaseText($row['luggage_purchase_text_inbound'] ?? '');

			// Step 1: Show purchase text
			if (!empty($purchOut) || !empty($purchIn)) {
				if ($khuhoi) {
					if (!empty($purchOut))
						$baggageDescription .= empty($baggageDescription) ? "$purchOut (Lượt đi)" : "\n$purchOut (Lượt đi)";
					if (!empty($purchIn))
						$baggageDescription .= empty($baggageDescription) ? "$purchIn (Lượt về)" : " - $purchIn (Lượt về)";
				} else {
					if (!empty($purchOut))
						$baggageDescription .= empty($baggageDescription) ? $purchOut : "\n$purchOut";
				}
			}

			// Step 2: Append generateLuggage() output (old index-based)
			// BUT: if luggage_index contains new format (x, _, T), use Baggage::renderAvailableBaggage() instead
			if (function_exists('generateLuggage') && $khuhoi) {
				// Outbound
				$luggage_idx_out = $row['luggage_index_outbound'] ?? '';
				$hasNewFormatOut = preg_match('/[x_T]/i', $luggage_idx_out);

				if ($hasNewFormatOut && class_exists('Baggage')) {
					// Use new format parser
					$bagOut = Baggage::renderAvailableBaggage($luggage_idx_out, $lang);
					if (!empty($bagOut)) {
						$baggageDescription .= empty($baggageDescription) ? $bagOut : "\n$bagOut";
						$baggageDescription .= ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)');
					}
				} else {
					// Use old generateLuggage logic
					$bag_out = generateLuggage($dateEntered, $row['aircode_outbound'] ?? '', $row['ticket_class_outbound'] ?? '', $row['type'] ?? '', $luggage_idx_out);
					$luggagePriceOut = $row['luggage_price'] ?? 0;
					if (!empty($luggage_idx_out) && is_numeric($luggage_idx_out)) $luggagePriceOut = $luggage_idx_out;
					$bag_out2 = $bag_out[(int)$luggagePriceOut] ?? '';
					$bag_weight_out = 0;
					if (!empty($bag_out2)) {
						preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
						$bag_weight_out = isset($ob_output[1]) ? (int)$ob_output[1] : 0;
					}
					if ($bag_weight_out > 0) {
						$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
						$baggageDescription .= ' ' . ($lang == 'en' ? '(Outbound)' : '(Lượt đi)');
					}
				}

				// Inbound
				$luggage_idx_in = $row['luggage_index_inbound'] ?? '';
				$hasNewFormatIn = preg_match('/[x_T]/i', $luggage_idx_in);

				if ($hasNewFormatIn && class_exists('Baggage')) {
					// Use new format parser
					$bagIn = Baggage::renderAvailableBaggage($luggage_idx_in, $lang);
					if (!empty($bagIn)) {
						$baggageDescription .= empty($baggageDescription) ? $bagIn : ' - ' . $bagIn;
						$baggageDescription .= ' ' . ($lang == 'en' ? '(Inbound)' : '(Lượt về)');
					}
				} else {
					// Use old generateLuggage logic
					$bag_in = generateLuggage($dateEntered, $row['aircode_inbound'] ?? '', $row['ticket_class_inbound'] ?? '', $row['type'] ?? '', $luggage_idx_in);
					if (!empty($bag_in)) {
						$luggagePriceIn = $row['luggage_price_inbound'] ?? 0;
						if (!empty($luggage_idx_in) && is_numeric($luggage_idx_in)) $luggagePriceIn = $luggage_idx_in;
						$bag_in2 = $bag_in[(int)$luggagePriceIn] ?? '';
						$bag_weight_in = 0;
						if (!empty($bag_in2)) {
							preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
							$bag_weight_in = isset($ib_output[1]) ? (int)$ib_output[1] : 0;
						}
						if ($bag_weight_in > 0) {
							$baggageDescription .= empty($baggageDescription) ? '' : ' - ';
							$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
							$baggageDescription .= ' ' . ($lang == 'en' ? '(Inbound)' : '(Lượt về)');
						}
					}
				}
			} elseif (function_exists('generateLuggage') && !$khuhoi) {
				$luggage_idx_out = $row['luggage_index_outbound'] ?? '';
				$hasNewFormatOut = preg_match('/[x_T]/i', $luggage_idx_out);

				if ($hasNewFormatOut && class_exists('Baggage')) {
					// Use new format parser
					$bagOut = Baggage::renderAvailableBaggage($luggage_idx_out, $lang);
					if (!empty($bagOut)) {
						$baggageDescription .= empty($baggageDescription) ? $bagOut : "\n$bagOut";
					}
				} else {
					// Use old generateLuggage logic
					$bag_out = generateLuggage($dateEntered, $row['aircode_outbound'] ?? '', $row['ticket_class_outbound'] ?? '', $row['type'] ?? '', $luggage_idx_out);
					$luggagePriceOut = $row['luggage_price'] ?? 0;
					if (!empty($luggage_idx_out) && is_numeric($luggage_idx_out)) $luggagePriceOut = $luggage_idx_out;
					$bag_out2 = $bag_out[(int)$luggagePriceOut] ?? '';
					$bag_weight_out = 0;
					if (!empty($bag_out2)) {
						preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
						$bag_weight_out = isset($ob_output[1]) ? (int)$ob_output[1] : 0;
					}
					if ($bag_weight_out > 0) {
						$baggageDescription .= $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
					}
				}
			}
		}

		return $baggageDescription;
	}

	/**
	 * Build separated baggage details for template.
	 * Returns array with hand_baggage_outbound, baggage_outbound, hand_baggage_inbound, baggage_inbound.
	 */
	function buildBaggageDetails($row)
	{
		$lang = $this->lang == 'en' ? 'en' : 'vn';
		$khuhoi = $this->isRoundTrip;

		if ($this->bookingBean === null) {
			$this->bookingBean = new EC_Flight_Bookings();
			$this->bookingBean->retrieve($this->bookingId);
		}

		$dateEntered = $row['bk_date_entered'] ?? ($row['date_entered'] ?? '');

		$result = [
			'hand_baggage_outbound' => '',
			'hand_baggage_inbound' => '',
			'baggage_outbound' => '',
			'baggage_inbound' => '',
		];

		// 1) Direct hand_baggage columns from DB (new format: e.g. "1x7")
		$dbHandOut = trim($row['hand_baggage_outbound'] ?? '');
		$dbHandIn  = trim($row['hand_baggage_inbound'] ?? '');

		if (!empty($dbHandOut) && class_exists('Baggage')) {
			$result['hand_baggage_outbound'] = Baggage::renderAvailableBaggage($dbHandOut, $lang);
		}
		if (!empty($dbHandIn) && class_exists('Baggage')) {
			$result['hand_baggage_inbound'] = Baggage::renderAvailableBaggage($dbHandIn, $lang);
		}

		// 2) Parse purchase text to separate hand baggage vs checked baggage
		$processPurchaseText = function ($rawText, &$handRef, &$bagRef) {
			$lines = explode("\n", $rawText);
			foreach ($lines as $line) {
				$line = trim($line);
				if (empty($line)) continue;
				$posPrice = stripos($line, 'Giá bán');
				if ($posPrice !== false) $line = substr($line, 0, $posPrice);
				$posOldPrice = stripos($line, 'Giá:');
				if ($posOldPrice !== false) $line = substr($line, 0, $posOldPrice);
				$pos = strpos($line, '(');
				if ($pos !== false) $line = substr($line, 0, $pos);
				$cleanLine = trim($line, " \t\n\r\0\x0B-:");
				// remove 'hành lý' string to clean up '20kg hành lý' into '20kg'
				$cleanLine = str_ireplace('hành lý', '', $cleanLine);
				$cleanLine = trim($cleanLine);

				if (empty($cleanLine)) continue;

				if (stripos($cleanLine, 'xách tay') !== false || stripos($cleanLine, 'xach tay') !== false || stripos($cleanLine, 'carry') !== false) {
					$val = preg_replace('/\s*(xách tay|xach tay|carry[- ]?on)\s*/iu', ' ', $cleanLine);
					$val = trim($val);
					// Only use purchase text carry-on if DB hasn't provided it (like '1x7')
					if (empty($handRef)) {
						$handRef = $val;
					}
				} else {
					$val = trim($cleanLine);
					if (empty($bagRef)) {
						$bagRef = $val;
					} else {
						// Avoid concatenating exact duplicates
						if (stripos($bagRef, $val) === false && stripos($val, $bagRef) === false) {
							$bagRef .= ' + ' . $val;
						}
					}
				}
			}
		};

		$purchOutRaw = trim($row['luggage_purchase_text'] ?? '');
		$purchInRaw = trim($row['luggage_purchase_text_inbound'] ?? '');

		$processPurchaseText($purchOutRaw, $result['hand_baggage_outbound'], $result['baggage_outbound']);
		$processPurchaseText($purchInRaw, $result['hand_baggage_inbound'], $result['baggage_inbound']);

		// Available checked baggage from luggage_index
		if ($this->bookingBean->isUseNewBaggage($dateEntered, $this->bookingBean->created_by)) {
			$availOut = class_exists('Baggage') ? Baggage::renderAvailableBaggage($row['luggage_index_outbound'] ?? '', $lang) : '';
			$availIn  = class_exists('Baggage') ? Baggage::renderAvailableBaggage($row['luggage_index_inbound']  ?? '', $lang) : '';

			// Combine available baggage with parsed purchase text checked baggage
			if (!empty($availOut) && !empty($result['baggage_outbound'])) {
				// Both available + purchased checked: combine via parsePackage
				$avaiParts = Baggage::parsePackage($availOut);
				$purchParts = Baggage::parsePackage($result['baggage_outbound']);
				if (
					$avaiParts['weight'] === $purchParts['weight'] && !is_null($avaiParts['weight'])
					&& $avaiParts['package'] > 0 && $purchParts['package'] > 0
				) {
					$result['baggage_outbound'] = ($avaiParts['package'] + $purchParts['package']) . ($lang == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiParts['weight'] . 'kg';
				} else {
					$result['baggage_outbound'] = $availOut . ' + ' . $result['baggage_outbound'];
				}
			} elseif (!empty($availOut) && empty($result['baggage_outbound'])) {
				$result['baggage_outbound'] = $availOut;
			}

			if (!empty($availIn) && !empty($result['baggage_inbound'])) {
				$avaiParts = Baggage::parsePackage($availIn);
				$purchParts = Baggage::parsePackage($result['baggage_inbound']);
				if (
					$avaiParts['weight'] === $purchParts['weight'] && !is_null($avaiParts['weight'])
					&& $avaiParts['package'] > 0 && $purchParts['package'] > 0
				) {
					$result['baggage_inbound'] = ($avaiParts['package'] + $purchParts['package']) . ($lang == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiParts['weight'] . 'kg';
				} else {
					$result['baggage_inbound'] = $availIn . ' + ' . $result['baggage_inbound'];
				}
			} elseif (!empty($availIn) && empty($result['baggage_inbound'])) {
				$result['baggage_inbound'] = $availIn;
			}
		} else {
			// Old baggage format: use generateLuggage for index-based baggage
			if (function_exists('generateLuggage')) {
				$luggage_idx_out = $row['luggage_index_outbound'] ?? '';
				$hasNewFormatOut = preg_match('/[x_T]/i', $luggage_idx_out);

				if ($hasNewFormatOut && class_exists('Baggage')) {
					$bagOut = Baggage::renderAvailableBaggage($luggage_idx_out, $lang);
					if (!empty($bagOut)) {
						if (empty($result['baggage_outbound'])) $result['baggage_outbound'] = $bagOut;
						else $result['baggage_outbound'] .= ' + ' . $bagOut;
					}
				} else {
					$bag_out = generateLuggage($dateEntered, $row['aircode_outbound'] ?? '', $row['ticket_class_outbound'] ?? '', $row['type'] ?? '', $luggage_idx_out);
					$luggagePriceOut = $row['luggage_price'] ?? 0;
					if (!empty($luggage_idx_out) && is_numeric($luggage_idx_out)) $luggagePriceOut = $luggage_idx_out;
					$bag_out2 = $bag_out[(int)$luggagePriceOut] ?? '';
					if (!empty($bag_out2)) {
						preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
						$bag_weight_out = isset($ob_output[1]) ? (int)$ob_output[1] : 0;
						if ($bag_weight_out > 0) {
							$cleaned = $lang == 'en' ? 'Extra ' . $bag_weight_out . 'kg' : substr_replace($bag_out2, '', strpos($bag_out2, '(') - 1);
							if (empty($result['baggage_outbound'])) $result['baggage_outbound'] = trim($cleaned);
							else $result['baggage_outbound'] .= ' + ' . trim($cleaned);
						}
					}
				}

				if ($khuhoi) {
					$luggage_idx_in = $row['luggage_index_inbound'] ?? '';
					$hasNewFormatIn = preg_match('/[x_T]/i', $luggage_idx_in);

					if ($hasNewFormatIn && class_exists('Baggage')) {
						$bagIn = Baggage::renderAvailableBaggage($luggage_idx_in, $lang);
						if (!empty($bagIn)) {
							if (empty($result['baggage_inbound'])) $result['baggage_inbound'] = $bagIn;
							else $result['baggage_inbound'] .= ' + ' . $bagIn;
						}
					} else {
						$bag_in = generateLuggage($dateEntered, $row['aircode_inbound'] ?? '', $row['ticket_class_inbound'] ?? '', $row['type'] ?? '', $luggage_idx_in);
						if (!empty($bag_in)) {
							$luggagePriceIn = $row['luggage_price_inbound'] ?? 0;
							if (!empty($luggage_idx_in) && is_numeric($luggage_idx_in)) $luggagePriceIn = $luggage_idx_in;
							$bag_in2 = $bag_in[(int)$luggagePriceIn] ?? '';
							if (!empty($bag_in2)) {
								preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
								$bag_weight_in = isset($ib_output[1]) ? (int)$ib_output[1] : 0;
								if ($bag_weight_in > 0) {
									$cleaned = $lang == 'en' ? 'Extra ' . $bag_weight_in . 'kg' : substr_replace($bag_in2, '', strpos($bag_in2, '(') - 1);
									if (empty($result['baggage_inbound'])) $result['baggage_inbound'] = trim($cleaned);
									else $result['baggage_inbound'] .= ' + ' . trim($cleaned);
								}
							}
						}
					}
				}
			}
		}

		// Post-process: detect hand baggage in baggage_outbound/inbound (may come from luggage_index path)
		if (empty($result['hand_baggage_outbound']) && !empty($result['baggage_outbound'])) {
			if (stripos($result['baggage_outbound'], 'xách tay') !== false || stripos($result['baggage_outbound'], 'xach tay') !== false || stripos($result['baggage_outbound'], 'carry') !== false) {
				$cleanHand = preg_replace('/\s*(xách tay|xach tay|carry[- ]?on)\s*/iu', ' ', $result['baggage_outbound']);
				$result['hand_baggage_outbound'] = trim($cleanHand);
				$result['baggage_outbound'] = '';
			}
		}
		if (empty($result['hand_baggage_inbound']) && !empty($result['baggage_inbound'])) {
			if (stripos($result['baggage_inbound'], 'xách tay') !== false || stripos($result['baggage_inbound'], 'xach tay') !== false || stripos($result['baggage_inbound'], 'carry') !== false) {
				$cleanHand = preg_replace('/\s*(xách tay|xach tay|carry[- ]?on)\s*/iu', ' ', $result['baggage_inbound']);
				$result['hand_baggage_inbound'] = trim($cleanHand);
				$result['baggage_inbound'] = '';
			}
		}

		return $result;
	}

	/**
	 * Clean purchase text by removing anything from the first '(' onwards.
	 * Handles nested parentheses like "1 kiện 23kg (0 VND) (Giá mua (VAT): ...)"
	 */
	function cleanPurchaseText($text)
	{
		$text = trim($text);
		$pos  = strpos($text, '(');
		if ($pos !== false) {
			$text = trim(substr($text, 0, $pos));
		}
		return $text;
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
			$idList = "'" . implode("','", array_map(function ($id) {
				return preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
			}, $this->itineraryIds)) . "'";
			$idFilter = "AND i.id IN($idList)";
		}

		$fields = "i.id, i.departure_date, i.arrival_date, i.flight_number,
				i.ticket_class, i.departure, i.arrival, i.airline_code, i.direction, i.transit_order";

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
				GROUP BY i.direction, i.transit_order, i.flight_number, i.departure_date
				ORDER BY i.direction, i.transit_order ASC, i.departure_date ASC";
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

			// Latest rescheduled itineraries (max sabre_logs per direction),
			// grouped to deduplicate when multiple passenger rows share same sabre_logs
			$sqlRescheduled = "SELECT MIN(i.id) AS id, i.departure_date, i.arrival_date, i.flight_number,
					i.ticket_class, i.departure, i.arrival, i.airline_code, i.direction, i.transit_order
				FROM ec_booking_itineraries i
				INNER JOIN (
					SELECT direction, MAX(sabre_logs) AS max_logs
					FROM ec_booking_itineraries
					WHERE booking_id = '$bookingId' $idFilter AND add_type = 3 AND deleted = 0
					GROUP BY direction
				) latest ON i.direction = latest.direction AND i.sabre_logs = latest.max_logs
				WHERE i.booking_id = '$bookingId'
					AND i.add_type = 3
					AND i.deleted = 0
				GROUP BY i.direction, i.transit_order, i.flight_number, i.departure_date";

			$sql = "($sqlUnchanged) UNION ALL ($sqlRescheduled) ORDER BY direction, transit_order ASC, departure_date ASC";
		}

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$depCode = $row['departure'] ?? '';
			$arrCode = $row['arrival'] ?? '';
			$airlineCode = $row['airline_code'] ?? '';

			// Get airport names
			$depAirport = Flight::getAirport($depCode);
			$arrAirport = Flight::getAirport($arrCode);

			$airlineInfo = function_exists('myGetAirlineInfo2') ? myGetAirlineInfo2($airlineCode, 'CODE') : ['data' => [['name' => $airlineCode]]];
			$airlineName = (!empty($airlineInfo['data'][0]['name'])) ? $airlineInfo['data'][0]['name'] : $airlineCode;

			// Use myGetAirportInfo2 to get proper localized city name
			$depInfo = function_exists('myGetAirportInfo2') ? myGetAirportInfo2($depCode) : [];
			$arrInfo = function_exists('myGetAirportInfo2') ? myGetAirportInfo2($arrCode) : [];

			$depCityName = (!empty($depInfo['data'][0]['name'])) ? $depInfo['data'][0]['name'] : ($depAirport['CityName'] ?? $depCode);
			$arrCityName = (!empty($arrInfo['data'][0]['name'])) ? $arrInfo['data'][0]['name'] : ($arrAirport['CityName'] ?? $arrCode);

			$depAirportName = $depAirport['AirPortName'] ?? '';
			$arrAirportName = $arrAirport['AirPortName'] ?? '';

			$results[] = [
				'id' => $row['id'],
				'direction' => (int)($row['direction'] ?? 0),
				'direction_label' => ((int)$row['direction'] === 0)
					? ($this->lang == 'en' ? 'Outbound' : 'Lượt đi')
					: ($this->lang == 'en' ? 'Inbound' : 'Lượt về'),
				'airline_code' => $airlineCode,
				'airline' => $airlineName,
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

		$uniqueDirections = array_unique(array_column($results, 'direction'));
		if (count($uniqueDirections) === 1) { //nếu 1 chiều đi thì đổi label thành "Hành trình" hoặc "Journey"
			foreach ($results as &$item) {
				$item['direction_label'] = ($this->lang == 'en') ? 'Journey' : 'Hành trình';
			}
			unset($item);
		}

		return $results;
	}

	/**
	 * Check if any itinerary changes (add_type=3) exist for this booking.
	 */
	function hasItineraryChanges()
	{
		global $db;
		$bookingId = $db->quote($this->bookingId);
		$sql = "SELECT COUNT(*) FROM ec_booking_itineraries
			WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0";
		return (int)$db->getOne($sql) > 0;
	}

	/**
	 * Get the resolved itineraries for a specific passenger.
	 * For each direction, find the latest add_type=3 record with assigned_user_id = passengerId.
	 * If no such record exists, fall back to original (add_type=0).
	 *
	 * This handles per-passenger itinerary changes where only some passengers
	 * have their itinerary changed while others keep the original.
	 */
	function getItinerariesForPassenger($passengerId)
	{
		global $db;
		$results = [];
		$bookingId = $db->quote($this->bookingId);
		$passengerId = preg_replace('/[^a-zA-Z0-9\-]/', '', $passengerId);

		$allIds = $this->resolvePassengerIdChain($passengerId);
		$idListStr = "'" . implode("','", $allIds) . "'";

		$fields = "i.id, i.departure_date, i.arrival_date, i.flight_number,
				i.ticket_class, i.departure, i.arrival, i.airline_code, i.direction, i.transit_order";

		// Build ID filter (skip if all itineraries requested)
		$idFilter = '';
		if (!$this->allItineraries) {
			$idList = "'" . implode("','", array_map(function ($id) {
				return preg_replace('/[^a-zA-Z0-9\-]/', '', $id);
			}, $this->itineraryIds)) . "'";
			$idFilter = "AND i.id IN($idList)";
		}

		// Determine which directions exist in this booking (0=outbound, 1=inbound)
		$sqlDirs = "SELECT DISTINCT direction FROM ec_booking_itineraries i
			WHERE i.booking_id = '$bookingId'
			$idFilter
			AND i.add_type IN (0, 3) AND i.deleted = 0";
		$resDirs = $db->query($sqlDirs);
		$directions = [];
		while ($rowDir = $db->fetchByAssoc($resDirs)) {
			$directions[] = (int)$rowDir['direction'];
		}
		sort($directions);

		foreach ($directions as $dir) {
			// Find the latest sabre_logs for this passenger + direction
			$sqlMaxLog = "SELECT MAX(sabre_logs) as max_logs
                      FROM ec_booking_itineraries i
                      WHERE i.booking_id = '$bookingId'
                        AND i.direction = $dir
                        AND i.add_type = 3
                        AND i.assigned_user_id IN ($idListStr)
                        AND i.deleted = 0";

			$resMaxLog = $db->query($sqlMaxLog);
			$rowMaxLog = $db->fetchByAssoc($resMaxLog);
			$maxLog = $rowMaxLog ? $rowMaxLog['max_logs'] : null;

			if ($maxLog !== null) {
				// Fetch all segments for this latest change
				$sqlChanged = "SELECT $fields FROM ec_booking_itineraries i
					WHERE i.booking_id = '$bookingId'
					AND i.direction = $dir
					AND i.add_type = 3
					AND i.assigned_user_id IN ($idListStr)
					AND i.sabre_logs = '$maxLog'
					AND i.deleted = 0
					ORDER BY i.transit_order ASC, i.departure_date ASC";
				$resChanged = $db->query($sqlChanged);
				while ($rowChanged = $db->fetchByAssoc($resChanged)) {
					$results[] = $this->formatItineraryRow($rowChanged);
				}
			} else {
				// Fall back to original (add_type=0)
				// there can be multiple ones!
				$sqlOrig = "SELECT $fields FROM ec_booking_itineraries i
					WHERE i.booking_id = '$bookingId'
					$idFilter
					AND i.direction = $dir
					AND i.add_type = 0
					AND i.deleted = 0
					ORDER BY i.transit_order ASC, i.departure_date ASC";
				$resOrig = $db->query($sqlOrig);
				while ($rowOrig = $db->fetchByAssoc($resOrig)) {
					$results[] = $this->formatItineraryRow($rowOrig);
				}
			}
		}

		return $results;
	}

	/**
	 * Format a raw itinerary row from DB into the display array.
	 * Extracted from getItineraries() to avoid duplication.
	 */
	function formatItineraryRow($row)
	{
		$depCode = $row['departure'] ?? '';
		$arrCode = $row['arrival'] ?? '';
		$airlineCode = $row['airline_code'] ?? '';

		$depAirport = Flight::getAirport($depCode);
		$arrAirport = Flight::getAirport($arrCode);

		$airlineInfo = function_exists('myGetAirlineInfo2') ? myGetAirlineInfo2($airlineCode, 'CODE') : ['data' => [['name' => $airlineCode]]];
		$airlineName = (!empty($airlineInfo['data'][0]['name'])) ? $airlineInfo['data'][0]['name'] : $airlineCode;

		$depInfo = function_exists('myGetAirportInfo2') ? myGetAirportInfo2($depCode) : [];
		$arrInfo = function_exists('myGetAirportInfo2') ? myGetAirportInfo2($arrCode) : [];

		$depCityName = (!empty($depInfo['data'][0]['name'])) ? $depInfo['data'][0]['name'] : ($depAirport['CityName'] ?? $depCode);
		$arrCityName = (!empty($arrInfo['data'][0]['name'])) ? $arrInfo['data'][0]['name'] : ($arrAirport['CityName'] ?? $arrCode);

		$depAirportName = $depAirport['AirPortName'] ?? '';
		$arrAirportName = $arrAirport['AirPortName'] ?? '';

		return [
			'id' => $row['id'],
			'direction' => (int)($row['direction'] ?? 0),
			'direction_label' => ((int)$row['direction'] === 0)
				? ($this->lang == 'en' ? 'Outbound' : 'Lượt đi')
				: ($this->lang == 'en' ? 'Inbound' : 'Lượt về'),
			'airline_code' => $airlineCode,
			'airline' => $airlineName,
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

	/**
	 * Create a unique signature for an itinerary set to group passengers.
	 * Passengers with the same signature share the same flights.
	 */
	function itinerarySignature($itineraries)
	{
		$parts = [];
		foreach ($itineraries as $iti) {
			$parts[] = $iti['direction'] . '|' . $iti['flight_number'] . '|' . $iti['dep_date'] . '|' . $iti['dep_time'];
		}
		sort($parts);
		return implode('||', $parts);
	}

	/**
	 * Walk the parent_detail_id chain upward to collect all ancestor IDs
	 * for a passenger. This is needed because assigned_user_id in itinerary
	 * change records may reference any version in the rename chain,
	 * not necessarily the latest one returned by getPassengers().
	 */
	function resolvePassengerIdChain($passengerId)
	{
		global $db;
		$ids       = [];
		$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $passengerId);
		$bookingId = $db->quote($this->bookingId);
		$maxDepth  = 10; // chống vòng lặp vô hạn

		for ($i = 0; $i < $maxDepth; $i++) {
			if (empty($currentId) || in_array($currentId, $ids)) break;
			$ids[] = $currentId;

			$sql = "SELECT parent_detail_id
                FROM ec_booking_passengers
                WHERE id = '$currentId'
                  AND booking_id = '$bookingId'
                  AND deleted = 0
                LIMIT 1";
			$res = $db->query($sql);
			$row = $db->fetchByAssoc($res);

			if (!$row || empty($row['parent_detail_id'])) break;
			$currentId = preg_replace('/[^a-zA-Z0-9\-]/', '', $row['parent_detail_id']);
		}

		return $ids;
	}
}
