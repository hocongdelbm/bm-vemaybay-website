<?php
 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Targets', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Targets&action=EditView&return_module=EC_Targets&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Targets');
}
if(ACLController::checkAccess('EC_Targets', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Targets&action=index&return_module=EC_Targets&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Targets');
}
// if(ACLController::checkAccess('EC_Targets', 'import', true)){
//     $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_Targets&return_module=EC_Targets&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Targets');
// }