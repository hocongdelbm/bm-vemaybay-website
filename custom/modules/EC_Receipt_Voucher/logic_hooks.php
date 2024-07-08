<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["process_record"] = Array();  
$hook_array["process_record"][] = Array(1, "markColorForRVStatus", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "markColorForRVStatus");

$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(2, "checkBeforeDelete", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "checkBeforeDelete");

$hook_array["before_save"] = array();
$hook_array["before_save"][] = array(1, "checkAmountConverted", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "checkAmountConverted");

$hook_array["after_save"] = array();
$hook_array["after_save"][] = array(1, "checkSupplierDebt", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "checkSupplierDebt");
?>