<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["process_record"] = Array();  
$hook_array["process_record"][] = Array(1, "markColorForRVStatus", "custom/modules/EC_Payment_Voucher/EC_Payment_VoucherLogicHook.php", "EC_Payment_VoucherLogicHook", "markColorForRVStatus");

$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "checkBeforeDelete", "custom/modules/EC_Payment_Voucher/EC_Payment_VoucherLogicHook.php", "EC_Payment_VoucherLogicHook", "checkBeforeDelete");

$hook_array["after_save"] = array();
$hook_array["after_save"][] = array(1, "checkSupplierDebt", "custom/modules/EC_Payment_Voucher/EC_Payment_VoucherLogicHook.php", "EC_Payment_VoucherLogicHook", "checkSupplierDebt");
?>