<?php
require_once("include/Sugar_Smarty.php");
class Viewprinteticketnew extends SugarView {
	public $sugarSmarty;
	public $lang;
	public $direction;
	public $isRoundTrip;
	public $bookingId;
	public $itineraryId; // Current itinerary ID
	public $listPassengerId; // List selected passengers

	public function __construct() {
        parent::__construct();

		$this->sugarSmarty 	= new Sugar_Smarty();
		$this->lang 		= isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn'; // Default is VN
		$this->direction	= (int)($_REQUEST['directions'] ?? 0);
		$this->isRoundTrip 	= (isset($_REQUEST['isRoundTrip']) && !empty($_REQUEST['isRoundTrip'])) ? (int)$_REQUEST['isRoundTrip'] : 0;
		$this->bookingId 	= $_REQUEST['booking_id'] ?? '';
		$this->itineraryId 	= $_REQUEST['itinerary_id'] ?? '';
		$this->listPassengerId = explode(',', (isset($_REQUEST['listPassengers']) && !empty($_REQUEST['listPassengers'])) ? $_REQUEST['listPassengers'] : []);
    }

	public function display() {
		$this->populateContent();
		$this->sugarSmarty->display("modules/EC_Flight_Bookings/tpls/view_printeticketnew.tpl");
	}

	public function populateContent() {
		// Detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);
		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // travelpass $created_by->department_id
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // MHV

		$com_website = $department_info['com_website2'];
		$com_phone 	 = $department_info['com_phone'];
		if (!empty($department_info['com_hotline1'])) $com_phone .= ' - ' . $department_info['com_hotline1'];
		if (!empty($department_info['com_hotline2'])) $com_phone .= ' - ' . $department_info['com_hotline2'];

		$passengersData = $this->getListPassengers();
		$itinerariesData = $this->getListItineraries();
		$this->sugarSmarty->assign('CONTENT', $this->renderContent($passengersData, $itinerariesData));

		$this->sugarSmarty->assign('BOOKING_NUMBER', $_REQUEST['booking']);
		// $this->sugarSmarty->assign('LIST_OF_PASSENGER', $pass_inf['html']);
		$this->sugarSmarty->assign('COM_NAME', $department_info['com_name']);
		$this->sugarSmarty->assign('COM_TAXCODE', $department_info['com_taxcode']);
		$this->sugarSmarty->assign('COM_ADDRESS', ($this->lang == 'en' ? $department_info['com_address2'] : $department_info['com_address']));
		$this->sugarSmarty->assign('COM_TOP_PHONE', $department_info['com_phone']);
		$this->sugarSmarty->assign('COM_PHONE', $com_phone);
		$this->sugarSmarty->assign('COM_WEBSITE', $com_website);
		$this->sugarSmarty->assign('COM_EMAIL', $department_info['com_email']);
		$this->sugarSmarty->assign('IMAGE_URL_LARGE', $department_info['company_logo']);
		$this->sugarSmarty->assign('MINUTE_BEFORE', $_REQUEST['ticket_type'] == '2' ? '120' : '120');
		// 2: quốc tế là 180p
	}

	public function renderContent($passengersData, $itinerariesData) {
		$borderColor = '#dbdbdb';

		$tbody = '';
		foreach($itinerariesData as $round => $segments) {
			$trHead = '';
			if(count($itinerariesData) > 1) {
				$trHead = '<tr>
					<td colspan="5" style="padding:10px; border-top:2px solid; border-bottom:2px solid;">
						<p style="font-size:16px; font-weight:600; padding:0; margin:0;">
							'. ($round == 0 ? 'Lượt đi' : 'Lượt về') .'
						</p>
					</td>
				</tr>';
			}

			$trIti = '';
			foreach($segments as $seg) {
				$trIti .= '<tr>
					<td width="10%" style="vertical-align:top; padding:10px">
						<b style="font-size:12px; line-height:1.5;">'. $seg['airline'] .'</b>
					</td>
					<td width="39%" style="vertical-align:top; text-align:right; padding:10px 16px 5px 10px">
						<p style="font-size:16px; font-weight:500; padding:0; margin:0 0 6px 0;">
							'. $seg['depCode'] .' <b>'. $seg['depTime'] .'</b>
						</p>
						<p style="font-size:14px; font-weight:500; padding:0; margin:0 0 6px 0; letter-spacing:.5px;">
							'. $seg['depDate'] .'
						</p>
						<p style="font-size:12px; font-weight:500; line-height:1.5; padding:0; margin:0 0 6px 0;">
							'. ($seg['depAirport']['CityName'] ?? 'Unknown') .', Sân bay '. ($seg['depAirport']['AirPortName'] ?? 'Unknown') .'
						</p>
					</td>
					<td>
						<svg fill="#000000" width="20px" height="20px" viewBox="0 -32 576 576" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M480 192H365.71L260.61 8.06A16.014 16.014 0 0 0 246.71 0h-65.5c-10.63 0-18.3 10.17-15.38 20.39L214.86 192H112l-43.2-57.6c-3.02-4.03-7.77-6.4-12.8-6.4H16.01C5.6 128-2.04 137.78.49 147.88L32 256 .49 364.12C-2.04 374.22 5.6 384 16.01 384H56c5.04 0 9.78-2.37 12.8-6.4L112 320h102.86l-49.03 171.6c-2.92 10.22 4.75 20.4 15.38 20.4h65.5c5.74 0 11.04-3.08 13.89-8.06L365.71 320H480c35.35 0 96-28.65 96-64s-60.65-64-96-64z"></path></g></svg>
					</td>
					<td width="39%" style="vertical-align:top; text-align:left; padding:10px 10px 5px 16px">
						<p style="font-size:16px; font-weight:500; padding:0; margin:0 0 6px 0;">
							'. $seg['desCode'] .' <b>'. $seg['desTime'] .'</b>
						</p>
						<p style="font-size:14px; font-weight:500; padding:0; margin:0 0 6px 0; letter-spacing:.5px;">
							'. $seg['desDate'] .'
						</p>
						<p style="font-size:12px; font-weight:500; line-height:1.5; padding:0; margin:0 0 6px 0;">
							'. ($seg['desAirport']['CityName'] ?? 'Unknown') .', Sân bay '. ($seg['desAirport']['AirPortName'] ?? 'Unknown') .'
						</p>
					</td>
					<td width="10%"></td>
				</tr>';
			}

			$trPass = '';
			foreach($passengersData[$round] as $p) {
				$salutation = $p['salutation'] == 0 ? 'Mr. ' : 'Ms. ';

				$style = "font-size:12px; vertical-align:top; border-bottom:1px solid; border-right:1px solid; border-color:$borderColor; padding:10px 8px;";
				$trPass .= '<tr>
					<td style="'. $style .'">
						'. $salutation . $p['name'] .'
					</td>
					<td style="'. $style .' text-align:center;">'. $p['pnr'] .'</td>
					<td style="'. $style .' text-align:center;">'. $p['ticketNo'] .'</td>
					<td style="'. $style .' border-right:none">
						<p style="font-size:12px; line-height:1.5; padding:0; margin:0 0 4px 0;">'. Baggage::renderAvailableBaggage($p['bagIndex']) .'</p>
						<p style="font-size:12px; line-height:1.5; padding:0; margin:0 0 4px 0;">'. $p['bagText'] .'</p>
					</td>
				</tr>';
			}
			$style = "font-size:12px; border-top:2px solid; border-bottom:1px solid; border-right:1px solid; border-color:$borderColor; padding:10px 8px;";
			$tablePassengers = '<tr>
				<td colspan="5" style="padding:24px 16px 24px">
					<table>
						<thead>
							<tr>
								<th width="30%" style="'.$style.'">
									HÀNH KHÁCH
								</th>
								<th width="18%" style="'.$style.' text-align:center;">
									MÃ ĐẶT CHỖ
								</th>
								<th width="17%" style="'.$style.' text-align:center;">
									SỐ VÉ
								</th>
								<th style="'.$style.' border-right:none;">
									HÀNH LÝ / DỊCH VỤ
								</th>
							</tr>
						</thead>
						<tbody>'.$trPass .'</tbody>
					</table>
				</td>
			</tr>';

			$tbody .= $trHead . $trIti . $tablePassengers;
		}

		return $tbody;
	}

	/**
	 * Get list passengers
	 * 
	 * @return array
	 */
	public function getListPassengers() {
		global $db;
		$results = [];

		$iti = new EC_Booking_Itineraries;
		$iti->retrieve($this->itineraryId);

		// Nếu là hành trình ban đầu
		if (empty($iti->add_type)) {
			$sql_con = "
				AND p.id NOT IN (
					SELECT assigned_user_id
					FROM ec_booking_itineraries
					WHERE booking_id = '$this->bookingId'
						AND add_type IN (1, 3)
						AND deleted = 0
				) 
				AND p.id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers
					WHERE booking_id = '$this->bookingId'
						AND add_type = 2
						AND deleted = 0
				)";
		} 
		// Là hành trình thay đổi
		else {
			$sql_con = "
			AND p.id IN (
				SELECT assigned_user_id FROM ec_booking_itineraries
				WHERE booking_id = '$this->bookingId' AND add_type = 3 AND deleted = 0
					AND sabre_logs = (
						SELECT sabre_logs
						FROM ec_booking_itineraries
						WHERE id = '$this->itineraryId'
					)
			) 
			AND p.id NOT IN (
				SELECT assigned_user_id FROM ec_booking_itineraries
				WHERE booking_id = '$this->bookingId' AND add_type = 3 AND deleted = 0
					AND sabre_logs > (
						SELECT sabre_logs
						FROM ec_booking_itineraries
						WHERE id = '$this->itineraryId'
					)
			)";
		}

		$sql = "SELECT p.id,
				p.name,
				p.salutation,
				p.pnr_outbound,
				p.pnr_inbound,
				p.eticket_outbound,
				p.eticket_inbound,

				p.luggage_index_outbound,
				p.luggage_index_inbound,
				p.luggage_price,
				p.luggage_price_inbound,
				p.luggage_purchase_no_vat,
				p.vat_luggage_purchase,
				p.luggage_purchase,
				p.luggage_purchase_text,
				p.luggage_purchase_inbound_no_vat,
				p.vat_luggage_purchase_inbound,
				p.luggage_purchase_inbound,
				p.luggage_purchase_text_inbound,
				p.cic,
				p.passport_number,
				p.booking_id
				-- (SELECT DATE_ADD(date_entered, INTERVAL 7 HOUR) FROM ec_flight_bookings WHERE id = p.booking_id) AS date_entered,
				-- (SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS aircode_outbound,
				-- (SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='0' AND i.deleted=0 LIMIT 1) AS ticket_class_outbound,
				-- (SELECT i.airline_code FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS aircode_inbound,
				-- (SELECT i.ticket_class FROM ec_booking_itineraries i WHERE i.booking_id=p.booking_id AND i.direction='1' AND i.deleted=0 LIMIT 1) AS ticket_class_inbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$this->bookingId'
				AND p.id IN('". implode("','", $this->listPassengerId) ."')
				AND p.deleted = 0
				$sql_con
			ORDER BY p.type, p.date_entered ";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$passId 		= $row['id'] ?? '';
			$passName 		= $row['name'] ?? '';
			$passSalutation = $row['salutation'] ?? '';
			$pnrOut 		= trim($row['pnr_outbound'] ?? '');
			$ticketNoOut 	= trim($row['eticket_outbound'] ?? '');
			$pnrIn 			= trim($row['pnr_inbound'] ?? '');
			$ticketNoIn 	= trim($row['eticket_inbound'] ?? '');
			
			if(strlen($pnrOut) >= 6 || strlen($ticketNoOut) > 5) {
				$results[0][$passId] = [
					'name' 			=> $passName,
					'salutation' 	=> $passSalutation,
					'pnr' 			=> $pnrOut,
					'ticketNo' 		=> $ticketNoOut,
					'bagIndex' 		=> $row['luggage_index_outbound'] ?? '',
					'bagText' 		=> $row['luggage_purchase_text'] ?? '',
					'bagTicketNum' 	=> $row['eluggage_outbound'] ?? '',
				];
			}

			if($this->isRoundTrip && (strlen($pnrIn) >= 6 || strlen($ticketNoIn) > 5)) {
				$results[1][$passId] = [
					'name' 			=> $passName,
					'salutation' 	=> $passSalutation,
					'pnr' 			=> $pnrIn,
					'ticketNo' 		=> $ticketNoIn,
					'bagIndex' 		=> $row['luggage_index_inbound'] ?? '',
					'bagText' 		=> $row['luggage_purchase_text_inbound'] ?? '',
					'bagTicketNum' 	=> $row['eluggage_inbound'] ?? '',
				];
			}
		}

		return $results;
	}

	/**
	 * Lấy danh sách hành trình bay
	 * 
	 * @param string $way_flight
	 * @param object $is_change_inf
	 * @return array
	 */
	public function getListItineraries($way_flight = '0', $is_change_inf = 0) {
		global $db;
		$results = [];

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
			WHERE i.booking_id = '$this->bookingId'
				AND i.deleted = 0 
				AND IF((i.sabre_logs = 0 or i.sabre_logs IS NULL), 0, i.sabre_logs) = (
					SELECT IF((sabre_logs = 0 or sabre_logs IS NULL), 0, sabre_logs)
					FROM ec_booking_itineraries
					WHERE id = '$this->itineraryId'
				)
		";

		if (!$this->isRoundTrip || $is_change_inf == 1) {
			$sql .= " AND i.direction = '$way_flight' ";
		}
		$sql .= "
			GROUP BY IF(sabre_logs = 0, i.id, i.direction) 
			ORDER BY i.direction, i.departure_date, i.date_entered
		";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$direction = (int)($row['direction'] ?? 0);
			$depCode = $row['departure'] ?? '';
			$desCode = $row['arrival'] ?? '';
			$depAirport = Flight::getAirport($depCode);
			$desAirport = Flight::getAirport($desCode);

			$depDate = date('d/m/Y', strtotime($row['departure_date']));
			$depTime = date('H:i', strtotime($row['departure_date']));
			$desDate = date('d/m/Y', strtotime($row['arrival_date']));
			$desTime = date('H:i', strtotime($row['arrival_date']));

			$airlineCode = $row['airline_code'] ?? '';
			$airline = Flight::getAirline($airlineCode);

			$results[$direction][] = [
				'depCode' => $depCode,
				'desCode' => $desCode,
				'depAirport' => $depAirport,
				'desAirport' => $desAirport,
				'depDate' => $depDate,
				'depTime' => $depTime,
				'desDate' => $desDate,
				'desTime' => $desTime,
				'airlineCode' => $airlineCode,
				'airline' => $airline,
			];
		}

		return $results;
	}
}
