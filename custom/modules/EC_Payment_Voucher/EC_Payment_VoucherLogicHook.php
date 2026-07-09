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
}
