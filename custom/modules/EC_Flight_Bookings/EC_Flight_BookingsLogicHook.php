<?php
class EC_Flight_BookingsLogicHook
{
	public $is_new_bk = 0;

	public function customDisplay(SugarBean $focus, $event, $arguments)
	{
		global $app_list_strings, $current_user;
		$text_color = $app_list_strings['booking_status_color_list'][$focus->booking_status];
		$text 		= $app_list_strings['booking_status_list'][$focus->booking_status];
		$focus->booking_status = '<label style="color:' . $text_color . '">' . $text . '</label>';

		// ip address - only panda view
		if ($current_user->id != '4f4d7a13-4171-9b7d-251c-64dd8f9885e4') {
			$focus->ip_address = '';
		}
	}

	function checkBeforeDelete($focus, $event, $arguments)
	{
		if ($focus->booking_status == '7' || $focus->booking_status == '8') {
			header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền xóa booking này'));
			exit();
		}
		if (trim($focus->id) != '') {
			$booking_id = trim($focus->id);

			// delete itineraries
			$update1 = "UPDATE ec_booking_itineraries SET deleted = 1 WHERE booking_id = '" . $booking_id . "' AND deleted = 0";
			$focus->db->query($update1);

			// delete booking details
			$update2 = "UPDATE ec_booking_details SET deleted = 1 WHERE booking_id = '" . $booking_id . "' AND deleted = 0";
			$focus->db->query($update2);

			// delete booking passengers
			$update3 = "UPDATE ec_booking_passengers SET deleted = 1 WHERE booking_id = '" . $booking_id . "' AND deleted = 0";
			$focus->db->query($update3);

			// delete booking notes
			$update4 = "UPDATE notes SET deleted = 1 WHERE parent_id = '" . $booking_id . "' AND parent_type = 'EC_Flight_Bookings' AND deleted = 0";
			$focus->db->query($update4);

			// delete working process
			myRemoveWorkingProcess($focus->module_dir, $focus->id);
		}

		// Xoá khỏi bảng completed booking
		$this->clearCompletedBK($focus->id);
	}

	function checkBeforeSave($focus, $event, $arguments)
	{
		// Khi ấn nút hoàn tất booking
		// Kiểm tra xem đã nhập đủ nhà cung cấp cho hành trình
		if (isset($_POST['booking_status']) && $_POST['booking_status'] == '8') {
			$sql = 'SELECT COUNT(*)
					FROM ec_booking_details
					WHERE booking_id = "' . $focus->id . '" AND (supplier_id IS NULL OR supplier_id = "") AND deleted = 0';

			$is_empty_supplier = $focus->db->getOne($sql);
			if ($is_empty_supplier > 0) {
				header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Booking không thể hoàn tất vì thiếu thông tin nhà cung cấp trong chi tiết vé. Vui lòng kiểm tra lại."));
				exit;
			} else {
				// Đối với những booking tạo từ ngày 19-09-2022
				if (isset($focus->date_entered) && strtotime($focus->date_entered) >= strtotime('2022-09-19')) {
					// Cập nhật ds vào bảng KPI và danh sách booker
					$total_amt = $focus->calculateBKTotalAmt($focus->id);
					if ($total_amt > 0) {
						$sql_amt1 = '
							UPDATE ec_working_process
							SET total_amount = ' . $total_amt . '
							WHERE parent_id = "' . $focus->id . '"
								AND deleted = 0
								AND paid > 0
						';
						$focus->db->query($sql_amt1);

						$sql_amt2 = '
							UPDATE users 
							SET total_amount += ' . $total_amt . '
							WHERE id = "' . $focus->assigned_user_id . '"
						';
						$focus->db->query($sql_amt2);
					}

					// Cập nhật sl vé vào bảng kpi và bảng booker
					$total_qty = $focus->calculateBookingTicketQty($focus->id);
					if ($total_qty >= 0) {
						$sql_qty1 = '
							UPDATE ec_working_process
							SET total_qty = ' . $total_qty . '
							WHERE parent_id = "' . $focus->id . '"
								AND deleted = 0
								AND paid > 0
						';
						$focus->db->query($sql_qty1);

						$sql_qty2 = '
							UPDATE users 
							SET total_ticket += ' . $total_qty . '
							WHERE id = "' . $focus->assigned_user_id . '"
						';
						$focus->db->query($sql_qty2);
					}
				}
			}
		} else {
			// Đối với những booking tạo từ ngày 19-09-2022
			// Khi mở ra thì trừ lại ds
			if (isset($focus->date_entered) && strtotime($focus->date_entered) >= strtotime('2022-09-19') && isset($focus->fetched_row['status']) && $focus->fetched_row['status'] == 8) {
				$sql_qty = '
					UPDATE users
					SET total_qty -= IFNULL((
						SELECT total_ticket
						FROM ec_working_process
						WHERE parent_id = "' . $focus->id . '" AND deleted = 0 AND completed > 0
					), 0)
					WHERE id = "' . $focus->assigned_user_id . '"
				';
				$focus->db->query($sql_qty);

				$sql_amt = '
					UPDATE users
					SET total_amount -= IFNULL((
						SELECT total_amount
						FROM ec_working_process
						WHERE parent_id = "' . $focus->id . '" AND deleted = 0 AND completed > 0
					), 0)
					WHERE id = "' . $focus->assigned_user_id . '"
				';
				$focus->db->query($sql_amt);
			}
		}

		// Kiểm tra xem nếu khách hàng có hành lý thì đã nhập NCC và giá mua hành lý hay chưa?
		// Có 1 số hành lý có giá là = 1. Nên không thể check p.luggage_price > 0 nên check 200 đồng
		if (isset($_POST['booking_status']) && $_POST['booking_status'] == '8') {
			if (isset($_POST['flight_type']) && $_POST['flight_type'] == '0') {
				$sql_return = 'OR (p.luggage_price_inbound > 500 AND (p.supplier_inbound_id IS NULL OR p.supplier_inbound_id = "" OR p.luggage_purchase_inbound IS NULL))';
				// $sql_return = 'OR (p.supplier_inbound_id IS NULL OR (p.luggage_purchase_inbound IS NULL OR p.luggage_purchase_inbound < 0))';
			} else {
				$sql_return = '';
			}

			$sql = 'SELECT COUNT(*)
					FROM ec_booking_passengers p
					WHERE p.booking_id = "' . $focus->id . '" 
						AND ((p.luggage_price > 500 AND (p.supplier_id IS NULL OR p.supplier_id = "" OR p.luggage_purchase IS NULL))' . $sql_return . ' )
						AND p.deleted = 0';

			$is_error = $focus->db->getOne($sql);
			if ($is_error > 0) {
				header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Vui lòng điền đầy đủ thông tin giá mua và NCC cho hành lý."));
				exit;
			}
		}
	}

	function updateFields($focus, $event, $arguments)
	{
		// Contact ID
		if (!empty($focus->contact_name) && empty($focus->contact_id)) {
			createContactsForBooking($focus->phone, $focus->contact_name);
		}

		// Journey
		if (empty($focus->journey)) {
			fillJourneyForBooking($focus->id);
		}
	}

	function updateKPI($focus, $event, $arguments)
	{
		// Cập nhật KPI COM khi hoàn tất booking - Tính KPI cho người được giao booking
		if (isset($_POST['btnCompleted']) || $focus->booking_status == 8 && $focus->fetched_row['assigned_user_id'] != $focus->assigned_user_id) {
			myRemoveWorkingProcess($focus->object_name, $focus->id, 'completed');
			myCreateWorkingProcess($focus->object_name, $focus->id, $focus->name, 'Hoàn tất booking', $focus->assigned_user_id, 'completed');

			$list_user = [
				'4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //panda
				'72ece22c-cb25-8e30-9dea-56f2201cd359', //trangbtq
				'9ba5c5a0-a402-02f4-76d3-53ba0481ce45', //soinau
				'b5523dbd-b9a7-67c0-77b5-533e6ece89b1', //ngocthu
			];

			$user = BeanFactory::newBean('Users');
			$user->retrieve($focus->assigned_user_id);
			$full_name = $user->last_name . ' ' . $user->first_name;

			$alertData = [
				'name' 			=> $focus->name,
				'parent_type' 	=> 'EC_Flight_Bookings',
				'parent_id' 	=> $focus->id,
				'description' 	=> $focus->description . ' (' . $full_name . ' đã hoàn tất booking).',
				'url_redirect' 	=> 'index.php?module=EC_Flight_Bookings&action=DetailView&record='.$focus->id.'',
				'priority' 		=> 'low',
				'type' 			=> 'readonly',
			];
	
			// $alert 		= new Alert();
			// $alertId 	= $alert->autoCreateAlert('EC_Flight_Bookings', $list_user, $alertData);
		}
	}

	function clearCompletedBK($booking_id)
	{
		global $db;
		$sql2 = '
			UPDATE ec_completed_bookings
			SET deleted = 1
			WHERE ec_flight_bookings_id_c = "' . $booking_id . '"
		';
		$db->query($sql2);
	}

	// Booking mới tạo thì tự động giao cho theo công thức
	function autoAssignBooking($focus, $event, $arguments)
	{
		global $app_list_strings, $sugar_config;
		// nếu là nhân đôi không tự động giao booking
		if (empty($focus->fetched_row)
			// && in_array($focus->created_by, $allow_site)
		) {
			$onl = new EC_Online_Report;
			$list_name_test = array('DEMO', 'IT', 'CUONG NGUYEN', 'CUONG NG');
			$list_name_help = array('PANDA PO', 'BAO GIA KHACH');

			if (in_array(strtoupper($focus->contact_name), $list_name_help)) {
				$this->reSendTele('Booking báo giá: Báo giá khách - ' . $focus->name . ' - ' . $focus->phone, $focus->id, $focus->name);
			} else if (in_array(strtoupper($focus->contact_name), $list_name_test)) {
				$this->reSendTele('Demo booking, test hệ thống . . .', $focus->id, $focus->name);
			} else {
				$focus->assigned_user_id = $onl->assignBooking($focus->id, $focus->total_qty);

				//user admin, ksnb
				if ($focus->assigned_user_id != '1' || $focus->assigned_user_id != 'e3bbb3e5-6660-0bf7-8976-54869c4ee609') {
					// cập nhật lại người giao cho
					$sql = '
						UPDATE ec_flight_bookings
						SET assigned_user_id = "' . $focus->assigned_user_id . '"
						WHERE id = "' . $focus->id . '"
					';
					$focus->db->query($sql);
					$user = new User;
					$user->retrieve($focus->assigned_user_id);
					$this->reSendTele('Booking mới: ' . $focus->name . ' - ' . strip_tags(htmlspecialchars($focus->contact_name)) . ' - ' . $focus->phone . "\nGiao cho: " . $user->last_name . ' ' . $user->first_name, $focus->id, $focus->name);
				} else {
					$this->reSendTele('Booking mới: ' . $focus->name . ' - ' . strip_tags(htmlspecialchars($focus->contact_name)) . ' - ' . $focus->phone, $focus->id, $focus->name);
				}
			}
		}
	}

	function reSendTele($content, $booking_id, $booking_name, $is_resend = 0, $params = array())
	{
		global $sugar_config;
		$conf = Mattermost::getConfig('channel_cty');
		$link = $sugar_config['site_url'] . "/index.php?module=EC_Flight_Bookings&record=$booking_id&action=DetailView&dothis=true";
		$link = "[$link](url)";
		$message = "`------------------------------`\n";
		$message .= "$content\n\n";
		$message .= "**Mở booking:** $link\n";
		Mattermost::sendMessage($message, $conf['channel_id'], $conf['bot_token']);

		// myTelegramSendMessage(
		// 	json_encode(array(
		// 		'text' => $content,
		// 		'reply_markup' => array(
		// 			'inline_keyboard' => array(
		// 				array(
		// 					array(
		// 						'text' => 'Mở booking',
		// 						'url' => $sugar_config['site_url'] . '/index.php?module=EC_Flight_Bookings&record=' . $booking_id . '&action=DetailView&dothis=true',
		// 					),
		// 				),
		// 			),
		// 		)
		// 	)),
		// 	$app_list_strings['system_config_list']['telegram_token_id'],
		// 	$app_list_strings['system_config_list']['telegram_chat_id'],
		// );
	}

	// Show column recall
	function getRecallValue($bean, $event, $arguments)
	{
		// Get access to custom fields from $bean
		$bean->custom_fields->retrieve();

		// Get access to name property using DBManager because $bean->name return null
		$sql 	= "SELECT COALESCE(SUM(IFNULL(recall, 0)), 0) AS recall FROM ec_working_process WHERE parent_id = '{$bean->id}' AND parent_type = 'EC_Flight_Bookings' AND deleted = 0";
		$rc_val 	= $bean->db->getOne($sql);

		$bean->recall_c = $rc_val;
	}
}
