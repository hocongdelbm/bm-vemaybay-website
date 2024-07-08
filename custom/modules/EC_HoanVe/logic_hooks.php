<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "checkBeforeDelete", "custom/modules/EC_HoanVe/EC_HoanVeLogicHook.php", "EC_HoanVeLogicHook", "checkBeforeDelete");

$hook_array["process_record"] = Array();  
$hook_array["process_record"][] = Array(1, "coloringStatus", "custom/modules/EC_HoanVe/EC_HoanVeLogicHook.php", "EC_HoanVeLogicHook", "coloringStatus");
?>