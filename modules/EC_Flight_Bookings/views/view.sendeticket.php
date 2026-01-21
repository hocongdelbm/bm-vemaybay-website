<?php
require_once("include/Sugar_Smarty.php");

class Viewsendeticket extends SugarView
{
	function display()
	{
		global $current_user;
		$smartyCont = new Sugar_Smarty();

		// Get booking info
		$booking_id = $_REQUEST['booking_id'] ?? '';
		$booking_number = $_REQUEST['booking'] ?? '';

		// Get booking bean to check email
		$bean = BeanFactory::getBean('EC_Flight_Bookings', $booking_id);

		// Check email is valid
		$not_allowed = [
			'gmail.vn',
			'gmail.com.vn',
			'gmall.com',
			'yahoo.con',
			'mobiphone.vn',
			'g.comail.com',
			'gsogov.vn',
			'gmail.con',
			'gmal.com',
			'gmail.vom',
			'g-mail.com',
			'gamil.com',
			'gmail.cim',
			'yhaoo.com',
			'gmal.vn',
			'gmail.co',
			'gmail.c',
			'gmai.com',
			'facebook.vn',
			'facebook.com.vn',
			'facebook.com',
			'facebook.net',
			'fb.com',
		];

		$email_domain = substr(strrchr($bean->email, '@'), 1);

		if (
			empty($bean->email)
			|| filter_var($bean->email, FILTER_VALIDATE_EMAIL) === false
			|| in_array($email_domain, $not_allowed)
			|| !checkdnsrr($email_domain, 'MX')
		) {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email không hợp lệ. Vui lòng kiểm tra lại.'));
			exit;
		}

		$is_ok = $this->sendEticket($smartyCont, $bean);
		if ($is_ok) {
			header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $booking_id);
			exit;
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode('Email hành trình gửi thất bại.'));
			exit;
		}
	}

	function sendEticket($smartyobj, $bean)
	{
		global $current_user;

		// Get language and khuhoi from request
		$lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn';
		$khuhoi = isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi']) ? $_REQUEST['khuhoi'] : 0;

		// Get booking info
		$booking_number = $_REQUEST['booking'] ?? '';
		$contact_name = $_REQUEST['contact_name'] ?? $bean->contact_name;
		$contact_email = $_REQUEST['contact_email'] ?? $bean->email;

		// Get department info
		$created_by = new User();
		$created_by->retrieve($bean->created_by);
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV

		// *** GET FULL DATA FROM JSON (sent from JavaScript) ***
		$passengersData = [];
		$itinerariesData = [];

		if (isset($_REQUEST['passengersData']) && !empty($_REQUEST['passengersData'])) {
			$passengersJSON = base64_decode($_REQUEST['passengersData']);
			$passengersData = json_decode($passengersJSON, true);
		}

		if (isset($_REQUEST['itinerariesData']) && !empty($_REQUEST['itinerariesData'])) {
			$itinerariesJSON = base64_decode($_REQUEST['itinerariesData']);
			$itinerariesData = json_decode($itinerariesJSON, true);
		}

		// Build HTML from received data (reuse the same functions from printeticket)
		$passengerHTML = $this->buildPassengerHTMLFromData($passengersData, $khuhoi, $lang);
		$itineraryHTML = $this->buildItineraryHTMLFromData($itinerariesData, $lang);

		// Prepare email subject
		$subject = ($lang == 'en') ? 'Eticket for booking ' . $booking_number : 'Vé điện tử cho đơn hàng ' . $booking_number;
		$subject .= ' - ' . ucwords(myRemoveUnicodeChars($contact_name));

		// Prepare booking info array for email template
		$booking_infos = array();
		$booking_infos['email_subject'] = $subject;
		$booking_infos['image_url_large'] = $department_info['company_logo'];
		$booking_infos['booking_num'] = $booking_number;
		$booking_infos['list_of_passenger'] = $passengerHTML;
		$booking_infos['list_of_itineraries'] = $itineraryHTML;
		$booking_infos['com_name'] = $lang === 'vn' ? $department_info['com_name'] : removeAccents($department_info['com_name']);
		$booking_infos['com_taxcode'] = $department_info['com_taxcode'];
		$booking_infos['com_address'] = $lang === 'vn' ? $department_info['com_address'] : $department_info['com_address2'];
		$booking_infos['com_phone'] = $department_info['com_phone'] . ' - ' . $department_info['com_hotline1'] . ' - ' . $department_info['com_hotline2'];
		$booking_infos['com_phone_support'] = $department_info['com_phone'];
		$booking_infos['com_website'] = $department_info['com_website2'];
		$booking_infos['com_website_slogan'] = $department_info['com_website'];
		$booking_infos['com_email'] = $department_info['com_email'];
		$booking_infos['minute_before'] = '120';

		// Generate email HTML
		require_once("modules/EC_Flight_Bookings/views/sendeticket_{$lang}.tpl.php");
		$body = generateSendmailHtml($booking_infos);
		// Check if this is preview or actual send
		if (!isset($_REQUEST['confirm_send']) || $_REQUEST['confirm_send'] != '1') {
			echo '<html><head><meta charset="UTF-8"><title>Preview Email</title></head><body>';
			echo '<div style="padding: 20px; background: #f5f5f5;">';
			echo '<h2>Xem trước email gửi cho: ' . htmlspecialchars($contact_email) . '</h2>';
			echo '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>';

			echo '<form method="POST" action="" style="margin: 20px 0;">';
			// Re-add all POST data
			foreach ($_REQUEST as $key => $value) {
				if ($key != 'confirm_send') {
					echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
				}
			}
			echo '<input type="hidden" name="confirm_send" value="1">';
			echo '<button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">✓ Xác nhận gửi email</button> ';
			echo '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $bean->id . '" style="padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">✗ Hủy</a>';
			echo '</form>';

			echo '<div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
			echo $body;
			echo '</div>';
			echo '</div>';
			echo '</body></html>';
			exit;
		}

		// Send email
		return mySendMail($current_user->id, $contact_email, $contact_name, $subject, $body);
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
					if (!empty($passenger['luggage']['outbound']) && !empty($passenger['luggage']['inbound'])) {
						$baggageDescription .= ' -';
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

		usort($itinerariesData, function ($a, $b) {
			$t1 = strtotime(str_replace('/', '-', $a['departureDate'] ?? ''));
			$t2 = strtotime(str_replace('/', '-', $b['departureDate'] ?? ''));
			return $t1 - $t2;
		});

		foreach ($itinerariesData as $itinerary) {

			$airlineCode = $itinerary['airlineCode'] ?? $itinerary['airline'] ?? '';
			$airline = myGetAirlineInfo2(trim($airlineCode), 'CODE');
			$airlineName = $airline['data'][0]['name'] ?? $itinerary['airline'];

			$departureCode = $itinerary['departure'] ?? '';
			$departure = myGetAirportInfo2(trim($departureCode));
			$departureName = ($departure['data'][0]['name'] ?? '') . ' (' . ($departure['data'][0]['code'] ?? $departureCode) . ')';

			$arrivalCode = $itinerary['arrival'] ?? '';
			$arrival = myGetAirportInfo2(trim($arrivalCode));
			$arrivalName = ($arrival['data'][0]['name'] ?? '') . ' (' . ($arrival['data'][0]['code'] ?? $arrivalCode) . ')';

			$departureDateTime = $itinerary['departureDate'] ?? '';
			$arrivalDateTime = $itinerary['arrivalDate'] ?? '';

			$dateDisplay = '';
			$timeRange = '';

			if (!empty($departureDateTime)) {
				$parts = explode(' ', $departureDateTime);
				if (isset($parts[0])) {
					$dateDisplay = str_replace('-', '/', $parts[0]);
				}
				if (isset($parts[1])) {
					$timeRange .= $parts[1];
				}
			}

			if (!empty($arrivalDateTime)) {
				$parts = explode(' ', $arrivalDateTime);
				if (isset($parts[1])) {
					if ($timeRange !== '')
						$timeRange .= ' - ';
					$timeRange .= $parts[1];
				}
			}

			$flightDisplay = $dateDisplay;
			if ($timeRange !== '') {
				$flightDisplay .= '<br>' . $timeRange;
			}

			$flightNumber = trim($itinerary['flightNumber']);

			$html .= '<tr>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . $flightDisplay . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($airlineName) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($flightNumber) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($departureName) . '</td>
            <td style="border:1px solid #ccc; padding: 10px 7px; text-align:center;">' . htmlspecialchars($arrivalName) . '</td>
        </tr>';
		}

		return $html;
	}

}