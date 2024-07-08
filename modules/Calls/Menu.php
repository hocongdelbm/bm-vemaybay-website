<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings;
// if (ACLController::checkAccess('Calls', 'edit', true)) {
//     $module_menu[]=array("index.php?module=Calls&action=EditView&return_module=Calls&return_action=DetailView", $mod_strings['LNK_NEW_CALL'],"Schedule_Call");
// }

if (ACLController::checkAccess('Calls', 'list', true)) {
    $module_menu[] = array("index.php?module=Calls&action=index&return_module=Calls&return_action=DetailView", $mod_strings['LNK_CALL_LIST'], "List");
}

// if (ACLController::checkAccess('Calls', 'import', true)) {
//     $module_menu[] =array("index.php?module=Import&action=Step1&import_module=Calls&return_module=Calls&return_action=index", $mod_strings['LNK_IMPORT_CALLS'],"Import", 'Calls');
// }

if (ACLController::checkAccess('Calls', 'list', true))
    $module_menu[] = array("index.php?module=Calls&action=statistics&return_module=Calls&return_action=statistics", "Thống kê", "statistics", 'Calls');

if (ACLController::checkAccess('Calls', 'edit', true))
    $module_menu[] = array("index.php?module=Calls&action=manage&return_module=Calls&return_action=manage", "Quản lý", "magnage");
