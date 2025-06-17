<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Working_Process', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Working_Process&action=EditView&return_module=EC_Working_Process&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Working_Process');
}
if(ACLController::checkAccess('EC_Working_Process', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Working_Process&action=index&return_module=EC_Working_Process&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Working_Process');
}
