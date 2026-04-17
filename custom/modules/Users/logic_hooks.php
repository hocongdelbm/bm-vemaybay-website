<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 

$hook_version  = 1; 
$hook_array    = array(); 

// position, file, function 
$hook_array['after_login']         = array(); 

$hook_array['after_login'][]       = array(1, 'after_login', 'custom/modules/EC_LoginAudit/EC_LoginAuditLogicHook.php','loginActions', 'updateLoginAudit'); 
$hook_array['after_login'][]       = array(2, 'Redirect login', 'custom/modules/Users/UsersLogicHook.php','UsersLogicHook', 'RedirectUser');

$hook_array['login_failed']        = array(); 
$hook_array['login_failed'][]      = array(1, 'login_failed', 'custom/modules/EC_LoginAudit/EC_LoginAuditLogicHook.php','loginActions', 'updateLoginAudit');

$hook_array['before_logout']       = array(); 
$hook_array['before_logout'][]     = array(1, 'before_logout', 'custom/modules/EC_LoginAudit/EC_LoginAuditLogicHook.php','loginActions', 'updateLoginAudit'); 

?>