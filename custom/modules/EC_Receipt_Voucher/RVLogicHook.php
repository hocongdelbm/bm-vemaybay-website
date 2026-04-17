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

	// ở trạng thái đã thu của loại 4 / 5 
	// Cập nhật thông tin doanh số
	function saveRevenueBookingHookReceipt($bean, $event, $arguments)
	{
		if (!empty($bean->booking_id) && in_array($bean->loai_thu, [4, 5, 27]) && $bean->rv_status == 1) {
			saveRevenueBooking($bean->booking_id);
			return true;
		}
	}
}
