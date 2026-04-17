<?php
$hook_version = 1;  
$hook_array = Array(); 
$hook_array["before_delete"] = Array();  
$hook_array["before_delete"][] = Array(1, "checkBeforeDelete", "custom/modules/EC_ChiTietTaiKhoan/EC_ChiTietTaiKhoanLogicHook.php", "EC_ChiTietTaiKhoanLogicHook", "checkBeforeDelete");

?>