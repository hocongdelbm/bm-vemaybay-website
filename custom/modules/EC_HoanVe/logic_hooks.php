<?php
$hook_version = 1;
$hook_array = array();
$hook_array["before_delete"] = array();
$hook_array["before_delete"][] = array(1, "checkBeforeDelete", "custom/modules/EC_HoanVe/EC_HoanVeLogicHook.php", "EC_HoanVeLogicHook", "checkBeforeDelete");

$hook_array["process_record"] = array();
$hook_array["process_record"][] = array(1, "coloringStatus", "custom/modules/EC_HoanVe/EC_HoanVeLogicHook.php", "EC_HoanVeLogicHook", "coloringStatus");

$hook_array["after_save"] = array();
$hook_array["after_save"][] = array(1, "Cập nhật doanh số booking", "custom/modules/EC_HoanVe/EC_HoanVeLogicHook.php", "EC_HoanVeLogicHook", "saveRevenueBookingHoanVe");
