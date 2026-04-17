<?php
$hook_version = 1;
$hook_array = array();
$hook_array["before_delete"] = array();
$hook_array["before_delete"][] = array(1, "checkBeforeDelete", "custom/modules/EC_HoaDonBan/EC_HoaDonBanLogicHook.php", "EC_HoaDonBanLogicHook", "checkBeforeDelete");

$hook_array["process_record"] = array();
$hook_array["process_record"][] = array(1, "calculateAmount", "custom/modules/EC_HoaDonBan/EC_HoaDonBanLogicHook.php", "EC_HoaDonBanLogicHook", "calculateAmount");
$hook_array["process_record"][] = array(2, "showCustomFields", "custom/modules/EC_HoaDonBan/EC_HoaDonBanLogicHook.php", "EC_HoaDonBanLogicHook", "showCustomFields");
$hook_array["process_record"][] = array(3, "custom_column_list_view", "custom/modules/EC_HoaDonBan/EC_HoaDonBanLogicHook.php", "EC_HoaDonBanLogicHook", "custom_column_list_view");