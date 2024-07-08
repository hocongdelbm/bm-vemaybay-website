<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_SMS_Logs', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_SMS_Logs&action=EditView&return_module=EC_SMS_Logs&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_SMS_Logs');
}

if(ACLController::checkAccess('EC_SMS_Logs', 'list', true)){
    $module_menu[]=array('index.php?module=EC_SMS_Logs&action=index&return_module=EC_SMS_Logs&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_SMS_Logs');
}
