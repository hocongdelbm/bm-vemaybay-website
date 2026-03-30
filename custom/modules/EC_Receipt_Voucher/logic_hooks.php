<?php
$hook_version = 1;
$hook_array = array();
$hook_array["process_record"] = array();
$hook_array["process_record"][] = array(1, "markColorForRVStatus", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "markColorForRVStatus");

$hook_array["before_delete"] = array();
$hook_array["before_delete"][] = array(2, "checkBeforeDelete", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "checkBeforeDelete");

$hook_array["before_save"] = array();
$hook_array["before_save"][] = array(1, "checkAmountConverted", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "checkAmountConverted");

$hook_array["after_save"] = array();
$hook_array["after_save"][] = array(1, "Cập nhật doanh số booking", "custom/modules/EC_Receipt_Voucher/RVLogicHook.php", "RVLogicHook", "saveRevenueBookingHookReceipt");
