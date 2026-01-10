<?php

class RVLogicHook
{

	function markColorForRVStatus($focus, $event, $arguments)
	{
		global $app_list_strings;
		$focus->rv_status = '<label style="font-weight:bold;color:' . $app_list_strings['receipt_voucher_status_color_list'][$focus->rv_status] . '">' . $app_list_strings['receipt_voucher_status_list'][$focus->rv_status] . '</label>';

		// booking name
		$booking_id = $focus->db->getOne("SELECT booking_id FROM ec_receipt_voucher WHERE deleted=0 AND id='" . $focus->id . "' LIMIT 1");
		$focus->booking_name = '<a href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $booking_id . '" target="_blank">' . $focus->booking_name . '</a>';
	}

	function checkBeforeDelete($focus, $event, $arguments)
	{
		if ($focus->rv_status == '1') {
			header("Location: index.php?module=EC_Receipt_Voucher&action=Error&error_string=" . urlencode("Chứng từ đã thu tiền. Vui lòng kiểm tra lại."));
			exit();
		}
	}

	// nếu thanh toán bằng tiền VND thì amount_convert phải bằng số tiền
	function checkAmountConverted($focus, $event, $arguments)
	{
		if ($focus->amount_type == 'VND') {
			$focus->amount_converted = $focus->amount;
		}
	}

	// nếu số tiền công nợ phải trả của NCC > -30 thì báo lên group kế toán
	function checkSupplierDebt($focus, $event, $arguments)
	{
		if ($focus->rv_status == 1 && (!empty($focus->supplier_id) || (!empty($focus->supplier2_id)) || (!empty($focus->supplier3_id)))) {
			$supplier_arr = array();
			if (!empty($focus->supplier_id))
				$supplier_arr[$focus->supplier_id] = $focus->supplier;
			if (!empty($focus->supplier2_id))
				$supplier_arr[$focus->supplier2_id] = $focus->supplier2;
			if (!empty($focus->supplier3_id))
				$supplier_arr[$focus->supplier3_id] = $focus->supplier3;
			$supplier_arr = array_unique($supplier_arr);
			$is_send = 0;
			$msg = '';
			foreach ($supplier_arr as $supplier_id => $supplier) {
				$supplier_name = new Account;
				$supplier_name->retrieve($supplier_id);
				if ($supplier_name->balance_observe) {
					$balance = calculateSupplierBalance($supplier_id);
					if ($balance > -20000000 && $balance < 0) {
						$is_send = 1;
						$msg .= $supplier . ': ' . format_number($balance) . "\n";
					}
				}
			}

			// if ($is_send) {
			// 	$post_fields = array(
			// 		'bot_id' => 'bot706494755',
			// 		'api_key' => 'AAHpTyV2fo8Jp_r0gCjrvskLyfed-ISKjb4',
			// 		'chat_id' => '-1001311652274',
			// 		'text' => $msg,
			// 	);
			// 	myTelegramSendMessage(json_encode($post_fields));
			// }
		}
	}

	// ở trạng thái đã thu của loại 4 / 5 
	// Cập nhật thông tin doanh số
	function saveRevenueBookingHookReceipt($bean, $event, $arguments)
	{
		if (!empty($bean->booking_id) && in_array($bean->loai_thu, [4, 5]) && $bean->rv_status == 1) {
			saveRevenueBooking($bean->booking_id);
			return true;
		}
	}
}
