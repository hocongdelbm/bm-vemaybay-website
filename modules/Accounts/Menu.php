<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $mod_strings, $app_strings, $sugar_config;

        
if (ACLController::checkAccess('Accounts', 'edit', true)) {
    $module_menu[]=array("index.php?module=Accounts&action=EditView&return_module=Accounts&return_action=index", $mod_strings['LNK_NEW_ACCOUNT'],"Create", 'Accounts');
}

if (ACLController::checkAccess('Accounts', 'list', true)) {
    $module_menu[]=array("index.php?module=Accounts&action=index&return_module=Accounts&return_action=DetailView", $mod_strings['LNK_ACCOUNT_LIST'],"List", 'Accounts');
}
// if (ACLController::checkAccess('Accounts', 'import', true)) {
//     $module_menu[]=array("index.php?module=Import&action=Step1&import_module=Accounts&return_module=Accounts&return_action=index", $mod_strings['LNK_IMPORT_ACCOUNTS'],"Import", 'Accounts');
// }
