<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Completed_Bookings', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Completed_Bookings&action=EditView&return_module=EC_Completed_Bookings&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Completed_Bookings');
}
if(ACLController::checkAccess('EC_Completed_Bookings', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Completed_Bookings&action=index&return_module=EC_Completed_Bookings&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Completed_Bookings');
}
if(ACLController::checkAccess('EC_Completed_Bookings', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_Completed_Bookings&return_module=EC_Completed_Bookings&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Completed_Bookings');
}