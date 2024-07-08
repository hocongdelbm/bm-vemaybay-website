<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Request_Flight', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Request_Flight&action=EditView&return_module=EC_Request_Flight&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Request_Flight');
}
if(ACLController::checkAccess('EC_Request_Flight', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Request_Flight&action=index&return_module=EC_Request_Flight&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Request_Flight');
}
