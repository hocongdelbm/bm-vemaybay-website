<?php
require_once("include/Sugar_Smarty.php");

class Viewsendeticketnew extends SugarView
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
		$contact_name = (!empty($_REQUEST['contact_name']) ? $_REQUEST['contact_name'] : null) ?? $bean->contact_name;
		// $contact_email = (!empty($_REQUEST['contact_email']) ? $_REQUEST['contact_email'] : null) ?? $bean->email;
		$contact_email = "dahyvan@giaonhanh.net"; //test

		// Get department info
		$created_by = new User();
		$created_by->retrieve($bean->created_by);
		$department_info = myGetDepartmentInfo("f15f801d-a9bc-cc92-4152-655f5e89867f"); // Security MHV

		// *** INSTEAD OF JSON, WE USE THE EXACT Print View CLASS TO RENDER HTML ***
		require_once('modules/EC_Flight_Bookings/views/view.printeticketnew.php');
		$printView = new Viewprinteticketnew();
		
		// Map parameters that Viewprinteticketnew expects in display()
		$printView->sugarSmarty = new Sugar_Smarty();
		$printView->lang = $lang;
		$printView->isRoundTrip = $khuhoi;
		$printView->bookingId = $bean->id;
		$printView->bookingName = $booking_number;
		$printView->ticketType = $_REQUEST['ticket_type'] ?? '1';

		// Parse comma-separated IDs from POST form 
		$printView->allPassengers = false;
		$printView->passengerIds = [];
		if (isset($_REQUEST['passengers']) && !empty($_REQUEST['passengers'])) {
			if (strtolower($_REQUEST['passengers']) === 'all') {
				$printView->allPassengers = true;
				$printView->passengerIds = ['all'];
			} else {
				$printView->passengerIds = array_filter(explode(',', $_REQUEST['passengers']));
			}
		}

		$printView->allItineraries = false;
		$printView->itineraryIds = [];
		if (isset($_REQUEST['itineraries']) && !empty($_REQUEST['itineraries'])) {
			if (strtolower($_REQUEST['itineraries']) === 'all') {
				$printView->allItineraries = true;
				$printView->itineraryIds = ['all'];
			} else {
				$printView->itineraryIds = array_filter(explode(',', $_REQUEST['itineraries']));
			}
		}

		// Run population logic in Viewprinteticketnew to generate SMARTY variables
		$printView->populateContent();

		// Fetch the HTML string using Smarty
		$body = $printView->sugarSmarty->fetch("modules/EC_Flight_Bookings/tpls/view_printeticketnew.tpl");

		// Prepare email subject
		$subject = ($lang == 'en') ? 'Eticket for booking ' . $booking_number : 'Vé điện tử cho đơn hàng ' . $booking_number;
		$subject .= ' - ' . ucwords(myRemoveUnicodeChars($contact_name));
		if (!isset($_REQUEST['confirm_send']) || $_REQUEST['confirm_send'] != '1') {
			echo '<div style="padding: 20px; background: #f5f5f5;">';
			echo '<h4>Xem trước NỘI DUNG email gửi cho: ' . htmlspecialchars($contact_email) . '</h2>';
			echo '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>';

			echo '<form method="POST" action="index.php" style="margin: 20px 0;">';
			// Re-add all POST data
			foreach ($_REQUEST as $key => $value) {
				if ($key != 'confirm_send' && $key != 'module' && $key != 'action') {
					echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
				}
			}
			echo '<input type="hidden" name="module" value="EC_Flight_Bookings">';
			echo '<input type="hidden" name="action" value="sendeticketnew">';
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
		$is_ok = mySendMail($current_user->id, $contact_email, $contact_name, $subject, $body);
		if ($is_ok) {
			header("Location: index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $bean->id);
			exit;
		}
	}
}