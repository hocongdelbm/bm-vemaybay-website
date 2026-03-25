<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Banks', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Banks&action=EditView&return_module=EC_Banks&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Banks');
}
if (ACLController::checkAccess('EC_Banks', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Banks&action=index&return_module=EC_Banks&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Banks');
}
if (ACLController::checkAccess('EC_Banks', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_Banks&return_module=EC_Banks&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Banks');
}
