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
				if (strtotime($focus->date_entered) >= strtotime('2022-09-19')) {
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
		}
		else {
			// Đối với những booking tạo từ ngày 19-09-2022
			// Khi mở ra thì trừ lại ds
			if (strtotime($focus->date_entered) >= strtotime('2022-09-19') && $focus->fetched_row['status'] == 8) {
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
		if(isset($_POST['booking_status']) && $_POST['booking_status'] == '8') {
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

	function updateKPI($focus, $event, $arguments)
	{
		// Cập nhật KPI COM khi hoàn tất booking - Tính KPI cho người được giao booking
		if (isset($_POST['btnCompleted']) || $focus->booking_status == 8 && $focus->fetched_row['assigned_user_id'] != $focus->assigned_user_id) {
			myRemoveWorkingProcess($focus->object_name, $focus->id, 'completed');
			myCreateWorkingProcess($focus->object_name, $focus->id, $focus->name, 'Hoàn tất booking', $focus->assigned_user_id, 'completed');
		} 
	}

	// Cập nhật thông tin voucher, khi lưu từ web
	function updateVoucher($focus, $event, $arguments)
	{
		if (!empty($focus->voucher) && empty($focus->voucher_id)) {
			// Cập nhật thông tin voucher vào booking
			$sql1 = 'UPDATE ec_flight_bookings 
					 SET voucher_id = (
					 	SELECT id FROM ec_vouchers 
						WHERE deleted = 0
						AND name = "' . strtoupper($focus->voucher) . '"
						LIMIT 1
					 )
					 WHERE deleted = 0 
					 AND id = "' . $focus->id . '"';
			$focus->db->query($sql1);
		}

		$booking = new EC_Flight_Bookings;
		$booking->retrieve($focus->id);
		$sql2 = 'UPDATE ec_vouchers
				SET status = 3
				  , account_name = "' . $focus->contact_name . '"
				  , account_phone = "' . $focus->phone . '"
				  , account_address = "' . $focus->address . '"
				  , account_email = "' . $focus->email . '"
				  , booking_id = "' . $booking->id . '"
				WHERE id = "' . $booking->voucher_id . '"';
		$focus->db->query($sql2);
	}

	// Đối với booking đc tặng voucher
	function getVoucher($focus, $event, $arguments)
	{
		$booking = new EC_Flight_Bookings;
		$booking->retrieve($focus->id);
		// nếu là booking đc phát voucher và ở tình trạng xác nhận
		if ($booking->has_voucher) {
			if (in_array($booking->booking_status, array(1, 6, 2))) {
				// kiểm tra booking đã có voucher chưa
				$sql = 'SELECT COUNT(id)
						FROM ec_vouchers
						WHERE deleted = 0
						AND booking_receive_id="' . $booking->id . '"';
				$has_voucher = $focus->db->getOne($sql);

				if (!$has_voucher) {
					// kiểm tra booking có bao nhiêu vé
					$sql1 = 'SELECT SUM(quantity) 
							FROM ec_booking_details 
							WHERE deleted = 0 
							AND booking_id = "' . $booking->id . '"
							GROUP BY booking_id';
					$ticket_qty = $focus->db->getOne($sql1);
					/*
						lựa chọn voucher phù hợp theo tiêu chí
						mệnh giá voucher tuỳ thuộc vào sl vé
					*/
					$bk_createdate = date('Y-m-d', strtotime($booking->date_entered));
					$sql2 = 'SELECT reduce_amount
								  , MAX(IF(booking_receive_id IS NULL OR TRIM(booking_receive_id) = "", 1, 0)) AS is_voucher_new
								  , SUBSTR(MIN(CONCAT(date_entered, booking_receive_id)), 11) AS oldest_bk
							 FROM ec_vouchers
							 WHERE deleted = 0
							 AND status = 0 
							 AND validate_from_date <= "' . $bk_createdate . '"
							 AND validate_to_date >= "' . $bk_createdate . '"
							 GROUP BY reduce_amount
							 ORDER BY reduce_amount';
					$res2 = $focus->db->query($sql2);
					$i = $max_amt = 0;
					while ($row2 = $focus->db->fetchByAssoc($res2)) {
						if ($ticket_qty >= ($i + 1)) {
							$max_amt = $row2['reduce_amount'];
						}

						$old_bk = $row2['oldest_bk'];
						$i++;
					}

					// nếu còn voucher new
					if ($max_amt > 0) {
						// random chọn 1 voucher ra tặng
						$sql3 = 'UPDATE ec_vouchers 
								SET booking_receive_id="' . $booking->id . '"
								WHERE deleted = 0
									AND status = 0 
									AND validate_from_date <= "' . $bk_createdate . '"
									AND validate_to_date >= "' . $bk_createdate . '"
									AND reduce_amount <= ' . $max_amt . '
								ORDER BY reduce_amount DESC
								LIMIT 1';
						$focus->db->query($sql3);

						// nếu hết voucher new, lấy voucher từ những voucher đã gắn cho booking mà chưa đc kích hoạt
						// lấy của booking cũ nhất để sử dụng lại
					} else if (!empty($old_bk)) {
						// random chọn 1 voucher ra tặng
						$sql3 = 'UPDATE ec_vouchers 
								SET booking_receive_id = "' . $booking->id . '"
								WHERE booking_receive_id = "' . $old_bk . '"
									AND status = 0
									AND deleted = 0
								ORDER BY reduce_amount DESC
								LIMIT 1';
						$focus->db->query($sql3);
					}
				}
			} else if ($booking->booking_status == 3) { //Xác nhận
				$sql = 'UPDATE ec_vouchers 
						SET status = 1
					 	WHERE booking_receive_id = "' . $booking->id . '"
							AND status = 0 
							AND deleted = 0';
				$focus->db->query($sql);
			}
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
			$list_name_test = array('DEMO', 'IT', 'CUONG NGUYEN');
			$list_name_help = array('PANDA PO', 'BAO GIA KHACH');

			if (in_array(strtoupper($focus->contact_name), $list_name_help)) {
				$this->reSendTele('Booking báo giá: Báo giá khách - ' . $focus->name . ' - ' . $focus->phone, $focus->id, $focus->name);
			} else if (in_array(strtoupper($focus->contact_name), $list_name_test)) {
				$this->reSendTele('Booking TEST: Demo . . . Anh em bỏ qua!', $focus->id, $focus->name);
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
		global $app_list_strings, $sugar_config;
		myTelegramSendMessage(
			json_encode(array(
				'text' => $content,
				'reply_markup' => array(
					'inline_keyboard' => array(
						array(
							array(
								'text' => 'Mở booking',
								'url' => $sugar_config['site_url'] . '/index.php?module=EC_Flight_Bookings&record=' . $booking_id . '&action=DetailView&dothis=true',
							),
						),
					),
				)
			)),
			$app_list_strings['system_config_list']['telegram_token_id'],
			$app_list_strings['system_config_list']['telegram_chat_id'],
		);
	}

	// Show column recall
	function getRecallValue($bean, $event, $arguments){
		// Get access to custom fields from $bean
		$bean->custom_fields->retrieve();
  
		// Get access to name property using DBManager because $bean->name return null
		$sql 	= "SELECT COALESCE(SUM(IFNULL(recall, 0)), 0) AS recall FROM ec_working_process WHERE parent_id = '{$bean->id}' AND parent_type = 'EC_Flight_Bookings' AND deleted = 0";
		$rc_val 	= $bean->db->getOne($sql);

		// if($GLOBALS['current_user']->user_name == 'hungnh'){
		//     pr($sql);
		//     pr($rc_val);
		// }

		$bean->recall_c = $rc_val;

    }
}
