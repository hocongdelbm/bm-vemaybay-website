<?php
$hook_version = 1;
$hook_array = array();
$hook_array["process_record"] = array();
$hook_array["process_record"][] = array(1, "markColorForRVStatus", "custom/modules/EC_Payment_Voucher/EC_Payment_VoucherLogicHook.php", "EC_Payment_VoucherLogicHook", "markColorForRVStatus");

$hook_array["before_delete"] = array();
$hook_array["before_delete"][] = array(1, "checkBeforeDelete", "custom/modules/EC_Payment_Voucher/EC_Payment_VoucherLogicHook.php", "EC_Payment_VoucherLogicHook", "checkBeforeDelete");
