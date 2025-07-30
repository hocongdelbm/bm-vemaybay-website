<?php
$hook_version = 1;  
$hook_array = Array();
$hook_array["after_save"] = Array(); 
$hook_array["after_save"][] = Array(1, "saveToFileJson", "custom/modules/EC_Bank_Account/EC_Bank_AccountLogicHook.php", "EC_Bank_AccountLogicHook", "saveToFileJson");
?>