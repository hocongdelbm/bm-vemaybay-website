<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "checkBeforeDelete", "custom/modules/EC_WorkingOverTimes/EC_WorkingOverTimesLogicHook.php", "EC_WorkingOverTimesLogicHook", "checkBeforeDelete");

$hook_array["after_save"] = Array();  
$hook_array["after_save"][] = Array(1, "updateDetail", "custom/modules/EC_WorkingOverTimes/EC_WorkingOverTimesLogicHook.php", "EC_WorkingOverTimesLogicHook", "updateDetail");