<?php
require_once("include/Sugar_Smarty.php");
class Viewsendeticket extends SugarView {
	function display() {
        global $current_user;
		$smartyCont = new Sugar_Smarty();

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
        $email_domain = substr(strrchr($this->bean->email, '@'), 1);
		if(empty($this->bean->email) 
			|| filter_var($this->bean->email, FILTER_VALIDATE_EMAIL) === false 
			|| in_array($email_domain, $not_allowed)
			|| !checkdnsrr($email_domain, 'MX')
		){
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode('Email không hợp lệ. Vui lòng kiểm tra lại.'));
            exit;
        }

		$is_ok = $this->sendEticket($smartyCont);
		if($is_ok){
            header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=".$_REQUEST['return_id']);
            exit;
		} else {
            header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode('Email hành trình gửi thất bại.'));
            exit;
		}
	}

	function sendEticket($smartyobj) {
		global $current_user;
		$send_ok = true;
		require_once('modules/EC_Flight_Bookings/views/view.printeticket.php');
		$pe = new Viewprinteticket();
		$pe->bean = $this->bean;

		// detect department id
		$created_by = new User();
		$created_by->retrieve($this->bean->created_by);
		
		$contact_name = $this->bean->contact_name;
		$contact_email = $this->bean->email;
		// $department_info = myGetDepartmentInfo("48840c01-3a4f-c430-f703-56f32c7cd8a4"); // Security travelpass
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV
		
		$lang = isset($_REQUEST['lang']) && !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 'vn'; // mặc định là in tiếng Việt
		$khuhoi = isset($_REQUEST['khuhoi']) && !empty($_REQUEST['khuhoi']) ? $_REQUEST['khuhoi'] : 0; // mặc định là in một chiều
		$subject = ($lang == 'en') ? 'Eticket for booking '.$_REQUEST['booking'] : 'Vé điện tử cho đơn hàng '.$_REQUEST['booking'];
		$subject .= ' - '.ucwords(myRemoveUnicodeChars($this->bean->contact_name));
		
		$booking_infos = array();
		$booking_infos['email_subject'] = $subject;
		$booking_infos['image_url_large'] = $department_info['company_logo'];
		$booking_infos['booking_num'] = $_REQUEST['booking'];
		$booking_infos['add_type'] = $_REQUEST['add_type'];
		$listPassengerIDs = explode(',', (isset($_REQUEST['listPassengers']) && !empty($_REQUEST['listPassengers'])) ? $_REQUEST['listPassengers'] : []);
		$pass_inf = $pe->listOfPassengers($_REQUEST['booking_id'], $_REQUEST['direction'], $_REQUEST['airline_code'], $khuhoi, $lang, $_REQUEST['itinerary_id'], $listPassengerIDs, $smartyobj);

		if (
			$pass_inf['pass_cnt'] <= 1 
			&& isset($_REQUEST['add_type']) 
			&& $_REQUEST['add_type'] == 3
		) {
			$is_change_inf = 1;
			$_REQUEST['add_type'] = 0;
		} else {
			$is_change_inf = 0;
			if(!isset($_REQUEST['add_type'])) {
				$_REQUEST['add_type'] = 0;
			}
		}
		$booking_infos['list_of_passenger'] = $pass_inf['html'];
		$booking_infos['list_of_itineraries_changed'] = $pass_inf['html_itineraries'];

		if ($_REQUEST['add_type'] != 3 || !isset($_REQUEST['add_type'])) {
			$iti_info = $pe->listOfItineraries($_REQUEST['booking_id'], $khuhoi, $_REQUEST['wayflight'],  $lang, $_REQUEST['itinerary_id'], $is_change_inf);
			
			if ($is_change_inf && $khuhoi) {
				if ($iti_info['direction'] == 1) $fdirection = 0;
				else $fdirection = 1;

				$another_iti 		= $pe->getAnotherIti($_REQUEST['booking_id'], $fdirection, $pass_inf['pass_id'], $pass_inf['edit_no']);
				$airline 			= myGetAirlineInfo2(trim($another_iti['airline_code']), 'CODE');
				$departure 		= myGetAirportInfo2(trim($another_iti['departure']));
				$arrival 			= myGetAirportInfo2(trim($another_iti['arrival']));
				$departure_date 	= date('d/m/Y', strtotime($another_iti['departure_date'])) . ' <br /> ' . date('H:i', strtotime($another_iti['departure_date'])) . ' - ' .date('H:i', strtotime($another_iti['arrival_date']));
				$airline 			= $airline['data'][0]['name'];
				$flight_number 	= $another_iti['flight_number'];
				$departure_inf 	= $departure['data'][0]['name'] . ' (' . $departure['data'][0]['code'] . ')';
				$arrival_inf 		= $arrival['data'][0]['name'] . ' (' . $arrival['data'][0]['code'] . ')';
				$html1 = '
					<tr class="no-change-iti">
						<td align="center" style="padding:3px; border:1px solid #ccc;">' . $departure_date . '</td>
						<td align="left" style="padding:3px; border:1px solid #ccc;">' . $airline . '</td>
						<td align="center" style="padding:3px; border:1px solid #ccc;">' . $flight_number . '</td>
						<td align="left" style="padding:3px; border:1px solid #ccc;">' . $departure_inf . '</td>
						<td align="left" style="padding:3px; border:1px solid #ccc;">' . $arrival_inf . '</td>
					</tr>';

				if ($fdirection == 0) 
					$iti_html = $html1 . $iti_info['html'];
				else 
					$iti_html = $iti_info['html'] . $html1;
			} 
			else $iti_html = $iti_info['html'];
			$booking_infos['list_of_itineraries'] = $iti_html;
		}

		$booking_infos['com_name'] 				= $lang === 'vn' ? $department_info['com_name'] : removeAccents($department_info['com_name']);
		$booking_infos['com_taxcode'] 			= $department_info['com_taxcode'];
		$booking_infos['com_address'] 			= $lang === 'vn' ? $department_info['com_address'] : $department_info['com_address2'];
		$booking_infos['com_phone'] 			= $department_info['com_phone'].' - '.$department_info['com_hotline1'].' - '.$department_info['com_hotline2'];
		$booking_infos['com_phone_support'] 	= $department_info['com_phone'];
		$booking_infos['com_website'] 			= $department_info['com_website2'];
		$booking_infos['com_website_slogan'] 	= $department_info['com_website'];
		$booking_infos['com_email'] 			= $department_info['com_email'];
		$booking_infos['minute_before'] 		= $_REQUEST['ticket_type'] == '2' ? '120' : '120'; // vé quốc tế là 180p

		require_once('modules/EC_Flight_Bookings/views/sendeticket_'.$lang.'.tpl.php');
		
		$body = generateSendmailHtml($booking_infos);
			 
		return mySendMail($current_user->id, $contact_email, $contact_name, $subject, $body);  
	}

}
