<?php

class EC_Payment_VoucherLogicHook
{

	function markColorForRVStatus($focus, $event, $arguments)
	{
		global $app_list_strings;
		$focus->pv_status = '<label style="font-weight:bold;color:' . $app_list_strings['payment_voucher_status_color_list'][$focus->pv_status] . '">' . $app_list_strings['payment_voucher_status_list'][$focus->pv_status] . '</label>';
	}

	function checkBeforeDelete($focus, $event, $arguments)
	{
		if ($focus->pv_status != '0') {
			header("Location: index.php?module=EC_Payment_Voucher&action=Error&error_string=" . urlencode("Chứng từ đã khóa"));
			exit();
		}

		// Remove working process
		myRemoveWorkingProcess($focus->module_dir, $focus->id);
	}

	// kiểm tra lại tên phiếu, nếu bị trùng thì set lại tên mới
	function checkVoucherName($focus, $event, $arguments)
	{
		$sql = 'SELECT COUNT(id) FROM ec_payment_voucher WHERE name = "' . $focus->name . '"';
		if ($focus->db->getOne($sql) > 1) {
			$number = $focus->db->getOne("SELECT COUNT(id) + 1 FROM ec_payment_voucher WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), '%Y-%m-%d') = '" . date('Y-m-d') . "'");
			$new_name = 'PC' . date('ymd') . str_pad($number, 2, 0, STR_PAD_LEFT);
			$focus->db->query('UPDATE ec_payment_voucher SET name = "' . $new_name . '" WHERE id = "' . $focus->id . '"');
		}
	}
}
