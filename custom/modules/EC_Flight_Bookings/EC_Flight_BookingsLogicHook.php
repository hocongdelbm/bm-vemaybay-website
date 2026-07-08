<?php
class EC_Flight_BookingsLogicHook
{
	public $is_new_bk = 0;

	public function customDisplay(SugarBean $focus, $event, $arguments)
	{
		global $app_list_strings;
		$text_color = $app_list_strings['booking_status_color_list'][$focus->booking_status];
		$text 		= $app_list_strings['booking_status_list'][$focus->booking_status];
		$focus->booking_status = '<label style="color:' . $text_color . '">' . $text . '</label>';

		// Tags
		$tagList = '';
		if($focus->is_prior == 1) {
			$tagList .= <<<HTML
				<span class="tag-prior" title="Vé cận" style="cursor:pointer">
					<svg height="18px" width="18px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 309.768 309.768" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path style="fill:#e00000;" d="M308.417,122.685c-2.317-4.607-7.223-7.408-12.292-6.967l-15.409,1.126 c-16.714-60.412-72.04-104.968-137.706-104.968C64.154,11.875,0,76.034,0,154.884c0,78.856,64.154,143.009,143.009,143.009 c45.645,0,88.934-22.083,115.798-59.063c4.123-5.689,2.855-13.63-2.823-17.764c-5.689-4.128-13.636-2.845-17.759,2.817 c-22.099,30.421-57.692,48.587-95.222,48.587c-64.839,0-117.582-52.748-117.582-117.582S78.165,37.308,143.004,37.308 c52.22,0,96.549,34.244,111.838,81.434l-8.023,0.587c-5.124,0.37-9.524,3.807-11.139,8.681 c-1.621,4.884-0.131,10.258,3.753,13.619l23.083,19.934c2.246,3.617,6.217,6.037,10.775,6.037c0.239,0,0.462-0.054,0.696-0.065 c0.076,0,0.136,0.033,0.207,0.033c0.305,0,0.615-0.005,0.93-0.033c3.361-0.25,6.483-1.822,8.692-4.373l22.849-26.456 C310.038,132.818,310.723,127.275,308.417,122.685z"></path> <g> <path style="fill:#e00000;" d="M75.772,199.191v-12.347l11.259-10.176c19.031-17.024,28.278-26.815,28.544-36.997 c0-7.109-4.286-12.733-14.348-12.733c-7.5,0-14.071,3.742-18.629,7.239l-5.765-14.62c6.57-4.944,16.752-8.974,28.55-8.974 c19.706,0,30.562,11.525,30.562,27.342c0,14.609-10.584,26.276-23.187,37.53l-8.044,6.701v0.261h32.841v16.763H75.772V199.191z"></path> <path style="fill:#e00000;" d="M186.261,199.191v-20.783H147.66v-13.26l32.972-53.091h24.933v51.073h10.454v15.278h-10.454 v20.783C205.564,199.191,186.261,199.191,186.261,199.191z M186.261,163.13v-19.298c0-5.232,0.267-10.584,0.669-16.219h-0.533 c-2.823,5.635-5.102,10.726-8.044,16.219l-11.661,19.031v0.267H186.261z"></path></g></g></g></g></svg>
				</span>
			HTML;
		}
		$sql_refund = "SELECT 1 FROM ec_hoanve WHERE booking_id = '{$focus->id}' AND deleted = 0";
		$is_refund = (bool) ($focus->db->fetchByAssoc($focus->db->query($sql_refund)) ?? 0);
		if($is_refund) {
			$tagList .= <<<HTML
				<span class="tag-refund" title="Hoàn vé" style="cursor:pointer">
					<svg width="18px" height="18px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M15 12h-2v-1c0-.551-.449-1-1-1H9.414l.586.586A1 1 0 118.586 12L6.293 9.707a1 1 0 010-1.414L8.586 6A1 1 0 1110 7.414L9.414 8H12c1.654 0 3 1.346 3 3v1zm2-8.5A1.5 1.5 0 0015.5 2h-11A1.5 1.5 0 003 3.5V17a1 1 0 001.3.954c.18-.057.317-.195.439-.338l1.121-1.321 1.349 1.399a1.002 1.002 0 001.415.026l1.364-1.318 1.305 1.305a.997.997 0 001.414 0l1.42-1.42 1.136 1.332c.12.141.257.277.434.334A1 1 0 0017 17V3.5z" fill="#ffae00"></path></g></svg>
				</span>
			HTML;
		}
		if(!empty($tagList)) {
			$focus->is_prior = <<<HTML
				<div class="d-flex justify-content-center gap-1">
					$tagList
				</div>
			HTML;
		}
		else {
			$focus->is_prior = '';
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

	function updateFields($focus, $event, $arguments) {
		global $current_user;

		// Contact ID
		if (!empty($focus->contact_name) && empty($focus->contact_id)) {
			createContactsForBooking($focus->phone, $focus->contact_name);
		}

		// Journey
		if (empty($focus->journey)) {
			fillJourneyForBooking($focus->id);
		}

		/**
		 * Map cuộc gọi và booking cho case booker đặt giùm khách hàng
		 */
		if (isset($_POST['is_telesale_value']) && empty($focus->telesale_call_id)) {
			if (!empty($focus->phone) && !in_array(strtoupper(trim($focus->contact_name)), $focus->contact_name_ignore)) {
				/** @var Call **/
				$call = BeanFactory::newBean("Calls");
				$call_id = $call->getTelesaleCalls($focus->phone, $focus->fetched_row['date_entered']);
				if (!empty($call_id)) {
					$focus->db->query(
						"UPDATE ec_flight_bookings
						SET telesale_call_id = '" . $focus->db->quote($call_id) . "'
							, is_telesale = 1
							, date_modified = NOW()
							, modified_user_id = '" . $focus->db->quote($current_user->id) . "'
						WHERE id = '" . $focus->db->quote($focus->id) . "'"
					);
				} else {
					// Không tìm thấy cuộc gọi telesale khớp -> báo cho user thay vì im lặng (xem Assets.php đọc flash)
					$_SESSION['ec_flight_flash'] = [
						'type' => 2,
						'msg'  => 'Chưa đánh dấu được "BK Telesale": không tìm thấy cuộc gọi telesale khớp (đúng SĐT, loại cuộc gọi telesale/recall, trong vòng 90 ngày trước ngày tạo booking).',
					];
				}
			} else {
				$_SESSION['ec_flight_flash'] = [
					'type' => 2,
					'msg'  => 'Chưa đánh dấu được "BK Telesale": booking thiếu số điện thoại hoặc tên khách nằm trong danh sách loại trừ.',
				];
			}
		}

		// Đánh dấu booking CTV
		if (isset($_POST['is_ctv_value'])) {
			$focus->db->query(
				"UPDATE ec_flight_bookings
				SET is_ctv = " . (int) $_POST['is_ctv_value'] . "
					, date_modified = NOW()
					, modified_user_id = '" . $focus->db->quote($current_user->id) . "'
				WHERE id = '" . $focus->db->quote($focus->id) . "'"
			);
		}

		// Add zalo id info
		$sql = "SELECT zc.zalo_id
			FROM contacts c
				INNER JOIN ec_zalo_contacts zc ON zc.contact_id = c.id
			WHERE c.phone_mobile = '{$focus->phone}'
				AND (zc.status IS NULL OR zc.status <> 'banned')
				AND zc.deleted = 0
				AND c.deleted = 0";
		$zalo_id = $focus->db->getOne($sql) ?? '';
		if((!$focus->zalo_id || empty($focus->zalo_id)) && !empty($zalo_id)) {
			$focus->db->query("UPDATE ec_flight_bookings SET zalo_id = '$zalo_id' WHERE id = '{$focus->id}'");
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
				'url_redirect' 	=> 'index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $focus->id . '',
				'priority' 		=> 'low',
				'type' 			=> 'readonly',
			];

			// $alert 		= new Alert();
			// $alertId 	= $alert->autoCreateAlert('EC_Flight_Bookings', $list_user, $alertData);
		}
	}

	// Booking mới tạo thì tự động giao cho theo công thức
	function autoAssignBooking($focus, $event, $arguments)
	{
		global $sugar_config;
		// Nếu là nhân đôi không tự động giao booking
		if (empty($focus->fetched_row)
			// && in_array($focus->created_by, $allow_site)
		) {
			$onl = new EC_Online_Report;
			$list_name_test = ['DEMO', 'IT', 'CUONG NGUYEN', 'CUONG NG'];
			$list_name_help = ['PANDA PO', 'BAO GIA KHACH'];
			$list_name_reference = ['THAM KHAO'];

			// Send Telegram
			try {
				$messageData = [];
				$link = $sugar_config['site_url'] . "/index.php?module=EC_Flight_Bookings&action=DetailView&record=" . $focus->id;
				if (in_array(strtoupper($focus->contact_name), $list_name_reference)) {
					$messageData = [
						'text' => "Booking tham khảo: $focus->name - $focus->phone",
						'parse_mode' => 'HTML',
						'reply_markup' => [
							'inline_keyboard' => [
								[
									[
										'text' => 'Mở booking',
										'url' => $link,
									],
								],
							],
						]
					];
				} else if (in_array(strtoupper($focus->contact_name), $list_name_help)) {
					$messageData = [
						'text' => "Booking báo giá khách: $focus->name - $focus->phone",
						'parse_mode' => 'HTML',
						'reply_markup' => [
							'inline_keyboard' => [
								[
									[
										'text' => 'Mở booking',
										'url' => $link,
									],
								],
							],
						]
					];
				} else if (in_array(strtoupper($focus->contact_name), $list_name_test)) {
					$messageData = [
						'text' => "Demo booking, test hệ thống: $focus->name",
						'parse_mode' => 'HTML',
						'reply_markup' => [
							'inline_keyboard' => [
								[
									[
										'text' => 'Mở booking',
										'url' => $link,
									],
								],
							],
						]
					];
				} else {
					$focus->assigned_user_id = $onl->assignBooking($focus->id, $focus->total_qty);

					// User admin, ksnb
					if ($focus->assigned_user_id != '1' && $focus->assigned_user_id != 'e3bbb3e5-6660-0bf7-8976-54869c4ee609') {
						// Cập nhật lại người giao cho
						$sql = "UPDATE ec_flight_bookings
							SET assigned_user_id = '$focus->assigned_user_id'
							WHERE id = '$focus->id'";

						$focus->db->query($sql);
						$user = new User;
						$user->retrieve($focus->assigned_user_id);

						$messageData = [
							'text' => "<b>Booking mới: $focus->name</b> - " . strip_tags(htmlspecialchars($focus->contact_name)) . " " . $focus->phone . "\n" . trim("Giao cho: $user->last_name $user->first_name"),
							'parse_mode' => 'HTML',
							'reply_markup' => [
								'inline_keyboard' => [
									[
										[
											'text' => 'Mở booking',
											'url' => $link,
										],
									],
								],
							]
						];
					} else {
						$messageData = [
							'text' => "<b>Booking mới: $focus->name</b> - " . strip_tags(htmlspecialchars($focus->contact_name)) . " " . $focus->phone,
							'parse_mode' => 'HTML',
							'reply_markup' => [
								'inline_keyboard' => [
									[
										[
											'text' => 'Mở booking',
											'url' => $link,
										],
									],
								],
							]
						];
					}
				}
				$botToken = $sugar_config['telegram']['cty']['bot_token'] ?? '';
				$chatId = $sugar_config['telegram']['cty']['chat_id'] ?? '';
				Telegram::sendMessageData(json_encode($messageData), $botToken, $chatId);
			} catch (Exception $e) {
			}
		}
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

	// Lưu thông tin doanh số sau khi Hoàn tất
	function saveRevenueBookingHook($bean, $event, $arguments)
	{
		if ((int)$bean->booking_status !== 8) return;
		saveRevenueBooking($bean->id);
		return true;
	}
}
