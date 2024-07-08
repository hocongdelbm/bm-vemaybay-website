<?php
$hook_version = 1;  
$hook_array = Array(); 

$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "checkBeforeDelete", "custom/modules/EC_LeaveAbsences/EC_LeaveAbsencesLogicHook.php", "EC_LeaveAbsencesLogicHook", "checkBeforeDelete");

$hook_array["before_save"] = Array();  
$hook_array["before_save"][] = Array(1, "remark", "custom/modules/EC_LeaveAbsences/EC_LeaveAbsencesLogicHook.php", "EC_LeaveAbsencesLogicHook", "remark");

$hook_array['process_record'] = Array(); 
$hook_array['process_record'][] = Array(1, 'custom_column', 'custom/modules/EC_LeaveAbsences/EC_LeaveAbsencesLogicHook.php','EC_LeaveAbsencesLogicHook', 'custom_column'); 
