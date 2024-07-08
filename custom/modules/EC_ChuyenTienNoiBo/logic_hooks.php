<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "CheckBeforeDelete", "custom/modules/EC_ChuyenTienNoiBo/EC_ChuyenTienNoiBoLogicHook.php", "EC_ChuyenTienNoiBoLogicHook", "CheckBeforeDelete");
?>