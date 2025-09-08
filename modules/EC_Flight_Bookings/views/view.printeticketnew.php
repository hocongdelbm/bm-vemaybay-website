<?php
require_once("include/Sugar_Smarty.php");
class Viewprinteticketnew extends SugarView {
	public $sugarSmarty;
	public $lang;
	public $isRoundTrip;
	public $bookingId;
	public $itineraryId; // Current itinerary ID
	public $listPassengerId; // List selected passengers

	function display() {
		if(in_array($this->bean->created_by, $this->bean->list_website_new_baggage)) {
			$this->sugarSmarty = new Sugar_Smarty();
			$this->lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn'; // Default is VN;
			$this->isRoundTrip = (isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi'])) ? (int)$_REQUEST['khuhoi'] : 0;
			$this->bookingId = $_REQUEST['booking_id'] ?? '';
			$this->itineraryId = $_REQUEST['itinerary_id'] ?? '';
			$this->listPassengerId = explode(',', (isset($_REQUEST['listPassengers']) && !empty($_REQUEST['listPassengers'])) ? $_REQUEST['listPassengers'] : []);

			$this->populateContent();
			$this->sugarSmarty->display("modules/EC_Flight_Bookings/tpls/view_printeticketnew.tpl");
		}
	}

	function populateContent() {
		// Detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);
		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // travelpass $created_by->department_id
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // MHV

		$com_website = $department_info['com_website2'];
		$com_phone 	 = $department_info['com_phone'];
		if (!empty($department_info['com_hotline1'])) $com_phone .= ' - ' . $department_info['com_hotline1'];
		if (!empty($department_info['com_hotline2'])) $com_phone .= ' - ' . $department_info['com_hotline2'];

		// Lấy danh sách, số lượng, thông tin hành khách 
		$passengersData = $this->getListPassengers();
		$itinerariesData = $this->getListItineraries();

		$borderColor = '#dbdbdb';
		$tbody = '';
		foreach($itinerariesData as $round => $segments) {
			$trHead = '';
			if(count($itinerariesData) > 1) {
				$trHead = '<tr>
					<td colspan="4" style="padding:10px; border-top:2px solid; border-bottom:2px solid;">
						<h5 style="font-weight:700; margin:0; padding:0">'. ($round == 0 ? 'Lượt đi' : 'Lượt về') .'</h5>
					</td>
				</tr>';
			}

			$trIti = '';
			foreach($segments as $seg) {
				$trIti .= '<tr>
					<td width="10%" style="vertical-align:top; padding:10px">
						<b style="line-height:1.5;">'. $seg['airline'] .'</b>
					</td>
					<td width="39%" style="vertical-align:top; text-align:right; padding:10px 16px 5px 10px">
						<h5>'. $seg['depCode'] .' <b>'. $seg['depTime'] .'</b></h5>
						<h6>'. $seg['depDate'] .'</b></h6>
						<p style="line-height:1.5;">'. ($seg['depAirport']['CityName'] ?? 'Unknown') .', Sân bay '. ($seg['depAirport']['AirPortName'] ?? 'Unknown') .'</p>
					</td>
					<td><span style="display:inline-block; font-size:25px; transform:rotate(90deg);">&#128743;</span></td>
					<td width="39%" style="vertical-align:top; text-align:left; padding:10px 10px 5px 16px">
						<h5>'. $seg['desCode'] .' <b>'. $seg['desTime'] .'</b></h5>
						<h6>'. $seg['desDate'] .'</b></h6>
						<p style="line-height:1.5;">'. ($seg['desAirport']['CityName'] ?? 'Unknown') .', Sân bay '. ($seg['desAirport']['AirPortName'] ?? 'Unknown') .'</p>
					</td>
					<td width="10%"></td>
				</tr>';
			}

			$trPass = '';
			foreach($passengersData[$round] as $p) {
				$salutation = $p['salutation'] == 0 ? 'Mr. ' : 'Ms. ';
				$trPass .= '<tr style="border-bottom:1px solid; border-color:'.$borderColor.'">
					<td style="vertical-align:top; padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.'; ">
						'. $salutation . $p['name'] .'
					</td>
					<td style="vertical-align:top; text-align:center; padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.';">'. $p['pnr'] .'</td>
					<td style="vertical-align:top; text-align:center; padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.';">'. $p['ticketNo'] .'</td>
					<td style="vertical-align:top; padding:10px 8px">
						<p style="line-height:1.5;">'. Baggage::renderAvailableBaggage($p['bagIndex']) .'</p>
						<p style="line-height:1.5;">'. $p['bagText'] .'</p>
					</td>
				</tr>';
			}
			$tablePassengers = '<tr>
				<td colspan="4" style="padding: 30px 20px 30px">
					<table>
						<thead>
							<tr style="border-bottom:1px solid; border-top:2px solid; border-color:'.$borderColor.';">
								<th width="30%" style="padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.';">
									HÀNH KHÁCH
								</th>
								<th width="15%" style="text-align:center; padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.';">
									MÃ ĐẶT CHỖ
								</th>
								<th width="15%" style="text-align:center; padding:10px 8px; border-right:1px solid; border-color:'.$borderColor.';">
									SỐ VÉ
								</th>
								<th style="padding:10px 8px;">
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

		$this->sugarSmarty->assign('DATA', $tbody);


		// if ($pass_inf['pass_cnt'] <= 1 && isset($_REQUEST['add_type']) && $_REQUEST['add_type'] == 3) {
		// 	$is_change_inf = 1;
		// 	$_REQUEST['add_type'] = 0;
		// } else {
		// 	$is_change_inf = 0;
		// 	if(!isset($_REQUEST['add_type'])) {
		// 		$_REQUEST['add_type'] = 0;
		// 	}
		// }
		// $smartyobj->assign('ADD_TYPE', $_REQUEST['add_type']);


		// // KHÔNG THAY ĐỔI HÀNH TRÌNH
		// if ((isset($_REQUEST['add_type']) && $_REQUEST['add_type'] == 0) || !isset($_REQUEST['add_type'])) {
		// 	$iti_inf = $this->listOfItineraries($_REQUEST['booking_id'], $khuhoi, $_REQUEST['wayflight'], $lang, $_REQUEST['itinerary_id'], $is_change_inf);
		// 	if ($is_change_inf && $khuhoi) {
		// 		if ($iti_inf['direction'] == 1) $fdirection = 0;
		// 		else $fdirection = 1;

		// 		$another_iti 	= $this->getAnotherIti($_REQUEST['booking_id'], $fdirection, $pass_inf['pass_id'], $pass_inf['edit_no']);
				
		// 		if(!empty($another_iti)){
		// 			$airline 			= myGetAirlineInfo2(trim($another_iti['airline_code']), 'CODE');
		// 			$departure 			= myGetAirportInfo2(trim($another_iti['departure']));
		// 			$arrival 			= myGetAirportInfo2(trim($another_iti['arrival']));
		// 			$departure_date 	= date('d/m/Y', strtotime($another_iti['departure_date'])) . ' <br /> ' . date('H:i', strtotime($another_iti['departure_date'])) .' - ' .date('H:i', strtotime($another_iti['arrival_date']));
		// 			$airline 			= $airline['data'][0]['name'];
		// 			$flight_number 	= $another_iti['flight_number'];
		// 			$departure_inf 	= $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
		// 			$arrival_inf 		= $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';

		// 			$html1 = '<tr class="no-change-iti">
		// 						<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px; line-height: 20px;">' . $departure_date . '</td>
		// 						<td style="border:1px solid #ccc; padding: 10px 7px;">' . $airline . '</td>
		// 						<td class="text-center" style="border:1px solid #ccc; padding: 10px 7px;">' . $flight_number . '</td>
		// 						<td style="border:1px solid #ccc; padding: 10px 7px;">' . $departure_inf . '</td>
		// 						<td style="border:1px solid #ccc; padding: 10px 7px;">' . $arrival_inf . '</td>
		// 					</tr>';
		// 		} else {
		// 			$html1 = '';
		// 		}

		// 		if ($fdirection == 0){
		// 			$iti_html = $html1 . $iti_inf['html'];
		// 		}
		// 		else {
		// 			$iti_html = $iti_inf['html'] . $html1;
		// 		}
		// 	} 
		// 	else $iti_html = $iti_inf['html'];

		// 	$smartyobj->assign('LIST_OF_ITINERARIES', $iti_html);
		// }

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

	/**
	 * Get list passengers
	 * 
	 * @return array
	 */
	function getListPassengers() {
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
