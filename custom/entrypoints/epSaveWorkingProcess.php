<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// $GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
// $GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
// $app_strings = return_application_language($GLOBALS['current_language']);
// $mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');
// global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;
global $db, $current_user, $sugar_config;

if (!empty($_SESSION['authenticated_user_id'])) {
	$module 					= trim($_POST['module'] ?? '');
	$action 					= trim($_POST['action'] ?? '');
	$record 					= trim($_POST['record'] ?? '');
	$record_name 				= trim($_POST['record_name'] ?? '');
	$contact_name				= trim($_POST['contact_name'] ?? '');
	$booking_status 			= isset($_POST['booking_status']) ? trim($_POST['booking_status']) : null;
	$is_paid 					= isset($_POST['is_paid']) ? (int)$_POST['is_paid'] : null;
	$is_invoice_export 			= isset($_POST['is_invoice_export']) ? (int)$_POST['is_invoice_export'] : null;
	$is_invoice_input_export 	= isset($_POST['is_invoice_input_export']) ? (int)$_POST['is_invoice_input_export'] : null;
	$recheck_status 			= isset($_POST['recheck_status']) ? $_POST['recheck_status'] : null;
	$support_customer 			= isset($_POST['support_customer']) ? $_POST['support_customer'] : null;
	$recall_status 				= isset($_POST['recall_status']) ? $_POST['recall_status'] : null;
	$check_debt 				= isset($_POST['check_debt']) ? $_POST['check_debt'] : null;
	$bonus 						= isset($_POST['bonus']) ? $_POST['bonus'] : null;
	$total_amount 	= $_POST['total_amount'] ?? null; // Booking total amount
	$total_qty  	= $_POST['total_qty'] ?? null; // Booking total quantity
	$txtWorkingProcessNote 		= isset($_POST['txtWorkingProcessNote']) ? trim(addslashes($_POST['txtWorkingProcessNote'])) : '';

	if ($module && $action && $action == 'Save' && $record) {

		// Kiểm tra đối với trường hợp booking đã gọi, chỉ tính 1 lần
		if ($booking_status == '6') {
			$sql_exist = "SELECT IF(id IS NOT NULL, 1, 0) 
						FROM ec_working_process
						WHERE parent_id = '$record' AND deleted = 0 AND called > 0";

			$is_exist = $db->getOne($sql_exist);
			if ($is_exist) {
				echo 2;
				exit();
			}
		}

		// Kiểm tra đối với trường hợp booking đã thanh toán, chỉ tính 1 lần
		if (!is_null($is_paid) && $is_paid != 0) {
			// Kiểm tra đã tồn tại
			$sql_exist = "SELECT IF(id IS NOT NULL, 1, 0) 
						FROM ec_working_process 
						WHERE parent_id = '$record' AND paid > 0 AND deleted = 0";

			$is_exist = $db->getOne($sql_exist);
			if ($is_exist) {
				echo 2;
				exit();
			}
		}

		// Save note in db
		if (!empty($txtWorkingProcessNote)) {
			$note = new Note();
			$note->id = '';
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
			$work->id = '';
			$work->name = $record_name;
			$work->description = $txtWorkingProcessNote;
			$work->parent_type = $module;
			$work->parent_id = $record;
			$work->assigned_user_id = $current_user->id;

			if (!is_null($is_paid) && $is_paid == 1) {
				$work->paid = 1;

				// Update booking description
				$update = "UPDATE ec_flight_bookings 
						SET is_paid = 1
							,description = CONCAT(IFNULL(description, ''), IF(description IS NOT NULL AND description <> '', ', ', ''), '$txtWorkingProcessNote') 
						WHERE id = '$record' AND deleted = 0";
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
						   SET description = '$txtWorkingProcessNote'
						   WHERE id = '$record'";
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
					$sql_udt_recheck = "UPDATE ec_working_process 
									SET recheck = IF(recheck > 0, 2, recheck) 
									WHERE parent_id = '$record' AND deleted = 0";
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
				// try {
				// 	if (!is_null($is_paid) && $is_paid == 1) {
				// 		$con_id = $con_phone = $con_zalo_id = $con_name = '';
				// 		$sql_get_phone_and_zalo = "
				// 			SELECT c.id, bk.phone, c.zalo_id, c.last_name
				// 			FROM ec_flight_bookings bk
				// 				LEFT JOIN contacts c ON c.phone_mobile = bk.phone AND c.deleted = 0
				// 			WHERE bk.id = '$record' AND bk.deleted = 0
				// 		";
				// 		$res_get_phone_and_zalo = $db->query($sql_get_phone_and_zalo);
				// 		while ($row = $db->fetchByAssoc($res_get_phone_and_zalo)) {
				// 			$con_id = $row['id'] ?? '';
				// 			$con_phone = $row['phone'] ?? '';
				// 			$con_zalo_id = $row['zalo_id'] ?? '';
				// 			// $con_name = $row['last_name'] ?? 'bạn';

				// 			// if(stripos($con_name, "Khách") !== false || stripos($con_name, "Khach") !== false || stripos($con_name, "Tele") !== false || preg_match('/^[0-9 ]*$/', $con_name)) {
				// 			// 	$con_name = 'bạn';
				// 			// }
				// 		}
	
				// 		if(!empty($con_phone)) {
				// 			// Update point to contact
				// 			$point = calculatePointsFromBooking($record);
				// 			if($point > 0) {
				// 				$sql_update_point = "UPDATE contacts SET points = points + $point WHERE id = '$con_id' AND deleted = 0";
				// 				$db->query($sql_update_point);

				// 				// Get total point
				// 				$sql = "SELECT points FROM contacts WHERE id = '$con_id' AND deleted = 0";
				// 				$total_point = $db->getOne($sql);

				// 				// Record point log
				// 				$point_log = new EC_Contact_Points_Log();
				// 				$point_log->id = '';
				// 				$point_log->name = 'Tích điểm từ booking';
				// 				$point_log->contact_id = $con_id;
				// 				$point_log->contact_phone = $con_phone;
				// 				$point_log->up = $point;
				// 				$point_log->down = 0;
				// 				$point_log->current_point = $total_point;
				// 				$point_log->parent_type = 'EC_Flight_Bookings';
				// 				$point_log->parent_id = $record;
				// 				$point_log->save();
								
				// 				// Send point info to customer via Zalo
				// 				require_once "custom/include/helpers/api/APIZaloOA.php";
				// 				$zaloOA = new APIZaloOA();
				// 				$Booking = new EC_Flight_Bookings();
				// 				if(!empty($con_zalo_id)) {
				// 					$total_discount = (int)($total_point/$Booking->point_step) * $Booking->point_step * 1000;
				// 					$total_discount_text = $total_discount > 0 ? number_format($total_discount, 0, ',', '.') . "đ" : "";
				// 					$total_discount_text = !empty($total_discount_text) ? " Bạn được giảm $total_discount_text cho lần mua vé tiếp theo." : "";

				// 					$header = "CHÚC MỪNG BẠN ĐÃ TÍCH LŨY $point ĐIỂM!";
				// 					$text = "Cảm ơn bạn đã tin tưởng lựa chọn Tìm Chuyến Bay.$total_discount_text Điểm số càng cao, càng nhiều ưu đãi hấp dẫn.";
				// 					$text2 = "Chúc bạn có một chuyến đi an toàn, vui vẻ & như ý.";
				// 					$table = [
				// 						[
				// 							"key" => "Mã booking",
				// 							"value" => "$record_name",
				// 						],
				// 						[
				// 							"key" => "Số điện thoại",
				// 							"value" => "$con_phone",
				// 						],
				// 						[
				// 							"key" => "Tổng tích lũy",
				// 							"value" => "$total_point điểm",
				// 						],
				// 					];
									
				// 					$json = $zaloOA->send_transaction($con_zalo_id, 'transaction_reward', $header, $text, $table, $text2);
				// 					$arr = json_decode($json, true);

				// 					if(isset($arr['error']) && $arr['error'] == 0) {
				// 						// $content = "**(AUTO) TIN NHẮN TÍCH ĐIỂM**";
				// 						// $content .= "\nĐã gửi tin nhắn tích điểm đến khách hàng qua zalo id\n";
				// 						// $content .= "- Booking: **$record_name**\n";
				// 						// $content .= "- Số điện thoại: **$con_phone**\n";
				// 						// $content .= "- Điểm cộng thêm: **$point điểm**\n";
				// 						// $content .= "- Tổng tích lũy: **$total_point điểm**";
				// 						// Mattermost::sendMessage($sugar_config['mattermost']['channel_id_zalo_oa'] ?? '', $content);

				// 						$content = "<b>⭐️ TIN NHẮN TÍCH ĐIỂM</b>";
				// 						$content .= "\nĐã gửi tin nhắn tích điểm đến khách hàng qua Zalo ID";
				// 						$content .= "\nBooking: <b>$record_name</b>";
				// 						$content .= "\nSĐT: <b>$con_phone</b>";
				// 						$content .= "\nĐiểm cộng thêm: <b>$point điểm</b>";
				// 						$content .= "\nTổng tích lũy: <b>$total_point điểm</b>";
				// 						$botToken 	= $sugar_config['telegram']['zalo']['bot_token'] ?? '';
				// 						$chatId 	= $sugar_config['telegram']['zalo']['chat_id'] ?? '';
				// 						Telegram::sendMessage($content, $botToken, $chatId);
				// 					}
				// 					else {
				// 						// $content = Mattermost::$line_separation;
				// 						// $content .= Mattermost::markdownHeading("[WARNING] Failed to send point-accumulation message to Zalo ID");
				// 						// $content .= "\nBooking: **$record_name**";
				// 						// $content .= "\nPhone: **$con_phone**";
				// 						// $content .= "\nZalo ID: **$con_zalo_id**";
				// 						// $content .= "\nExtra points: **$point**";
				// 						// $content .= "\nTotal points: **$total_point**";
				// 						// $content .= "\n\n$json";
				// 						// Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $content);

				// 						$content = "<b>[WARNING] Failed to send point-accumulation message to Zalo ID</b>";
				// 						$content .= "\nBooking: <b>$record_name</b>";
				// 						$content .= "\nPhone: <b>$con_phone</b>";
				// 						$content .= "\nZalo ID: <b>$con_zalo_id</b>";
				// 						$content .= "\nExtra points: <b>$point</b>";
				// 						$content .= "\nTotal points: <b>$total_point</b>";
				// 						$content .= "\n<pre>$json</pre>";
				// 						$botToken   = $sugar_config['telegram']['bot_token'] ?? '';
				// 						$chatId     = $sugar_config['telegram']['chat_id'] ?? '';
				// 						$threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
				// 						Telegram::sendMessage($message, $botToken, $chatId, $threadId);
				// 					}
				// 				}
				// 				else if(5 < date('H') && date('H') < 22) {
				// 					require_once "custom/include/helpers/api/APIOMNI.php";
				// 					$Omni = new APIOMNI();
				// 					$template_id = $Omni->getTemplateCode('points');
				// 					$template_data = json_encode([
				// 						"point" => $point,
				// 						"name" => "bạn",
				// 						"booking" => $record_name,
				// 						"total_point" => $total_point
				// 					]);
				// 					$json = $Omni->sendMessage($con_phone, $template_id, $template_data);
				// 					$arr  = json_decode($json, true);

				// 					if((isset($arr['error']) && $arr['error'] == 0) || (isset($arr['status']) && $arr['status'] == 1)) {
				// 						$m = new EC_Messages();
				// 						$m->send_from       = $Zalo->get_oa_id();
				// 						$m->send_to         = $con_phone;
				// 						$m->content         = $Omni->getTemplateName($template_id);
				// 						$m->type            = 'zalo_zns';
				// 						$m->category        = 'transaction';
				// 						$m->send_time       = date("Y-m-d H:i:s", strtotime('-7 hours')); // Lưu xuống db giảm 7 tiếng
				// 						$m->parent_type     = 'EC_Flight_Bookings';
				// 						$m->parent_id       = $record;
				// 						$m->data            = $template_data;
				// 						$m->response        = $json;
				// 						$m->status          = 'done';
				// 						$m->cost            = 220;
				// 						$m->save();

				// 						// $content = "**(AUTO) TIN NHẮN TÍCH ĐIỂM**";
				// 						// $content .= "\nĐã gửi tin nhắn tích điểm đến khách hàng qua ZNS\n";
				// 						// $content .= "- Booking: **$record_name**\n";
				// 						// $content .= "- Số điện thoại: **$con_phone**\n";
				// 						// $content .= "- Điểm cộng thêm: **$point điểm**\n";
				// 						// $content .= "- Tổng tích lũy: **$total_point điểm**";
				// 						// Mattermost::sendMessage($sugar_config['mattermost']['channel_id_zalo_oa'] ?? '', $content);

				// 						$content = "<b>⭐️ TIN NHẮN TÍCH ĐIỂM</b>";
				// 						$content .= "\nĐã gửi tin nhắn tích điểm đến khách hàng qua <b>ZNS</b>";
				// 						$content .= "\nBooking: <b>$record_name</b>";
				// 						$content .= "\nSĐT: <b>$con_phone</b>";
				// 						$content .= "\nĐiểm cộng thêm: <b>$point điểm</b>";
				// 						$content .= "\nTổng tích lũy: <b>$total_point điểm</b>";
				// 						$botToken 	= $sugar_config['telegram']['zalo']['bot_token'] ?? '';
				// 						$chatId 	= $sugar_config['telegram']['zalo']['chat_id'] ?? '';
				// 						Telegram::sendMessage($content, $botToken, $chatId);
				// 					}
				// 					else {
				// 						// $content = Mattermost::$line_separation;
				// 						// $content .= Mattermost::markdownHeading("[WARNING] Failed to send point-accumulation ZNS message");
				// 						// $content .= "\nBooking: **$record_name**";
				// 						// $content .= "\nPhone: **$con_phone**";
				// 						// $content .= "\nExtra points: **$point**";
				// 						// $content .= "\nTotal points: **$total_point**";
				// 						// $content .= "\n\n$json";
				// 						// Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $content);

				// 						$content = "<b>[WARNING] Failed to send point-accumulation ZNS message</b>";
				// 						$content .= "\nBooking: <b>$record_name</b>";
				// 						$content .= "\nPhone: <b>$con_phone</b>";
				// 						$content .= "\nExtra points: <b>$point</b>";
				// 						$content .= "\nTotal points: <b>$total_point</b>";
				// 						$content .= "\n<pre>$json</pre>";
				// 						$botToken   = $sugar_config['telegram']['bot_token'] ?? '';
				// 						$chatId     = $sugar_config['telegram']['chat_id'] ?? '';
				// 						$threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
				// 						Telegram::sendMessage($message, $botToken, $chatId, $threadId);
				// 					}
				// 				}
				// 			}
				// 		}
				// 	}
				// }
				// catch(Exception $e) {
				// 	if (!empty($txtWorkingProcessNote)) $note->save();
				// 	exit();
				// }
			} 
			else echo 0;
		}

		// Save note
		if (!empty($txtWorkingProcessNote)) $note->save();

		// Send a message when a customer makes a bank transfer
		if ($is_paid === 1 || mb_stripos($txtWorkingProcessNote, "Đã chuyển khoản") !== false) {
			global $sugar_config;
			$channel = $sugar_config['notification_channel'] ?? 'Telegram';

			$m = '';
			if($note->hasMoney($txtWorkingProcessNote)) $m = trim("Booking $record_name, $contact_name, $txtWorkingProcessNote");
			else {
				$total_amount_format = !is_null($total_amount) ? number_format($total_amount, 0) : '';
				$m = trim("Booking $record_name, $contact_name, $txtWorkingProcessNote $total_amount_format ($total_qty vé)");
			}
			
			if($channel == 'Mattermost') {}
			else {
				$botToken = $sugar_config['telegram']['bot_token'] ?? '';
				$chatId   = $sugar_config['telegram']['thongbao']['chat_id'];
				Telegram::sendMessage($m, $botToken, $chatId);
			}
		}

		exit();
	}
}