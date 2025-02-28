<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if (!empty($_SESSION['authenticated_user_id'])) {
	$module 					= trim($_POST['module']);
	$action 					= trim($_POST['action']);
	$record 					= trim($_POST['record']);
	$record_name 				= trim($_POST['record_name']);

	$booking_status 			= isset($_POST['booking_status']) ? trim($_POST['booking_status']) : null;
	$is_paid 					= isset($_POST['is_paid']) ? (int)$_POST['is_paid'] : null;
	$is_invoice_export 			= isset($_POST['is_invoice_export']) ? (int)$_POST['is_invoice_export'] : null;
	$is_invoice_input_export 	= isset($_POST['is_invoice_input_export']) ? (int)$_POST['is_invoice_input_export'] : null;
	$recheck_status 			= isset($_POST['recheck_status']) ? $_POST['recheck_status'] : null;
	$support_customer 			= isset($_POST['support_customer']) ? $_POST['support_customer'] : null;
	$recall_status 				= isset($_POST['recall_status']) ? $_POST['recall_status'] : null;
	$check_debt 				= isset($_POST['check_debt']) ? $_POST['check_debt'] : null;
	$bonus 						= isset($_POST['bonus']) ? $_POST['bonus'] : null;
	$txtWorkingProcessNote 		= isset($_POST['txtWorkingProcessNote']) ? trim(addslashes($_POST['txtWorkingProcessNote'])) : '';

	if ($module && $action && $action == 'Save' && $record) {

		// Kiểm tra đối với trường hợp booking đã gọi, chỉ tính 1 lần
		if ($booking_status == '6') {
			$sql_exist = 'SELECT IF(id IS NOT NULL, 1, 0) 
						  FROM ec_working_process
						  WHERE parent_id = "'.$record.'" deleted = 0 AND called > 0';

			$is_exist = $db->getOne($sql_exist);
			if ($is_exist) {
				echo 2;
				exit();
			}
		}

		// Kiểm tra đối với trường hợp booking đã thanh toán, chỉ tính 1 lần
		if (!is_null($is_paid) && $is_paid != 0) {
			// Kiểm tra đã tồn tại
			$sql_exist = 'SELECT IF(id IS NOT NULL, 1, 0) 
						  FROM ec_working_process 
						  WHERE parent_id = "' . $record . '" AND paid > 0 AND deleted = 0';

			$is_exist = $db->getOne($sql_exist);
			if ($is_exist) {
				echo 2;
				exit();
			}
		}

		// Save note in db
		if (!empty($txtWorkingProcessNote)) {
			$note = new Note();
			$note->id 			= '';
			$note->name 			= $record_name;
			$note->description 		= $txtWorkingProcessNote;
			$note->parent_type 		= $module;
			$note->parent_id 		= $record;
			$note->booking_status 	= $booking_status;
		}

		// Save working process
		if (!is_null($is_paid) && $is_paid == 0) {
			// myRemoveWorkingProcess($module, $record, 'paid');
			echo 1;
			exit();
		} 
		else if (!is_null($is_invoice_export) && $is_invoice_export == 0) {
			myRemoveWorkingProcess($module, $record, 'invoice_issued');
			echo 1;
			exit();
		} 
		else if (!is_null($is_invoice_input_export) && $is_invoice_input_export == 0) {
			myRemoveWorkingProcess($module, $record, 'invoice_input_issued');
			echo 1;
			exit();
		} 
		else {
			$work = new EC_Working_Process();
			$work->id 			= '';
			$work->name 			= $record_name;
			$work->description 		= $txtWorkingProcessNote;
			$work->parent_type 		= $module;
			$work->parent_id 		= $record;
			$work->assigned_user_id = $current_user->id;

			if (!is_null($is_paid) && $is_paid == 1) {
				$work->paid = 1;

				// Update booking description
				$update = "UPDATE ec_flight_bookings 
						   SET is_paid = 1, description = CONCAT(IFNULL(description, ''), IF(description IS NOT NULL AND description <> '', ', ', ''), '" . $txtWorkingProcessNote . "') 
						   WHERE id = '" . $record . "' ";
				$db->query($update);
			} 
			else if (!is_null($is_invoice_export) && $is_invoice_export == 1) {
				$work->invoice_issued = 1;
				update_field_booking($record, 'is_invoice_export', $is_invoice_export);
			} 
			else if (!is_null($is_invoice_input_export) && $is_invoice_input_export == 1) {
				$work->invoice_input_issued = 1;
				update_field_booking($record, 'is_invoice_input_export', $is_invoice_input_export);
			} 
			else if ($booking_status == '4' && !empty($txtWorkingProcessNote)) {
				// Update booking description when cancel or complete
				$update = "UPDATE ec_flight_bookings 
						   SET description = '" . $txtWorkingProcessNote . "'
						   WHERE id = '" . $record . "' ";
				$db->query($update);
			} 
			else {
				// if ($booking_status == '6') // Called
				// 	$work->called = 1;
				// else 
				
				if ($booking_status == '3') // Confirmed
					$work->confirmed = 1;
				else if ($booking_status == '8' && is_null($support_customer)) // Completed
					$work->completed = 1;

				if ($recheck_status == '2') // Đã recheck
					$work->recheck = 1;

				// if ($recall_status == '2') // Đã recall
					// $work->recall = 1;

				if ($check_debt == '2') // Đối chiếu công nợ
					$work->check_debt = 1;

				if ($support_customer == '2') // Hỗ trợ KH
					$work->support = 1;

				if (!is_null($bonus)) { // Bonus
					myRemoveWorkingProcess($module, $record, 'bonus');
					$work->bonus = !empty($bonus) ? unformat_number($bonus) : 0;
				}
			}

			$work->save();

			// Save ok
			if (!empty($work->id)) {
				// Kiểm tra nếu booking hoàn tất thì recheck x2
				if(!is_null($booking_status) && $booking_status == '8'){
					$sql_udt_recheck = 'UPDATE ec_working_process 
									SET recheck = IF(recheck > 0, 2, recheck) 
									WHERE parent_id = "' . $record . '" AND deleted = 0';
					$db->query($sql_udt_recheck);
				}

				// Cập nhật hỗ trợ xong thì chuyển sang status "Đã TT"
				if($support_customer == '2' && !is_null($booking_status) && $booking_status == '1'){
					update_field_booking($record, 'booking_status', '2');
					update_field_booking($record, 'assigned_user_id', $current_user->id);
				} else {
					if($record && $booking_status){
						update_field_booking($record, 'booking_status', $booking_status);
					}
				}
				
				// Cập nhật giao cho khi bấm Đã gọi lần đầu
				if($booking_status == '6') update_field_booking($record, 'assigned_user_id', $current_user->id);

				// UPDATE booking_status - ec_customer
				UpdateInforBookingOfCustomer($record);

				// Add attribute for notes
				$note->working_process_id = $work->id;

				echo 1;

				// // Save and send message add points to contact when paid successfully
				// if (!is_null($is_paid) && $is_paid == 1) {
				// 	$con_id = $con_phone = $con_zalo_id = '';
				// 	$sql_get_phone_and_zalo = "
				// 		SELECT c.id, bk.phone, c.zalo_id
				// 		FROM ec_flight_bookings bk
				// 			LEFT JOIN contacts c ON c.phone_mobile = bk.phone AND c.deleted = 0
				// 		WHERE bk.id = '$record' AND bk.deleted = 0
				// 	";
				// 	$res_get_phone_and_zalo = $db->query($sql_get_phone_and_zalo);
				// 	while ($row = $db->fetchByAssoc($res_get_phone_and_zalo)) {
				// 		$con_id = $row['id'] ?? '';
				// 		$con_phone = $row['phone'] ?? '';
				// 		$con_zalo_id = $row['zalo_id'] ?? '';
				// 	}

				// 	if(!empty($con_phone) && !empty($con_zalo_id)) {
				// 		$point = calculatePointsFromBooking($record);
				// 		if($point > 0) {
				// 			$sql_update_point = "UPDATE contacts SET points = points + $point WHERE id = '$con_id'";
				// 			$db->query($sql_update_point);
				// 			// Send zalo here
				// 		}
				// 	}
				// }
			} 
			else echo 0;
		}

		// Save note
		if (!empty($txtWorkingProcessNote)) $note->save();
		exit();
	}
}

function update_field_booking($id, $field, $value, $datatype = 'string') {
	if(is_null($id) || is_null($field) || is_null($value) || empty($id) || empty($field) || empty($value)) return false;

	global $db;

	if($datatype == 'string') $value_format = '"'.$value.'"';
	else $value_format = $value;
 
	$sql = 'UPDATE ec_flight_bookings
			SET '.$field.' = '.$value_format.' 
			WHERE id = "'. $id .'" AND deleted = 0';

	$db->query($sql);
}
