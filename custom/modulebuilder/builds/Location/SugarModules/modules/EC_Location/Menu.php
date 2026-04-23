<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Location', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Location&action=EditView&return_module=EC_Location&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Location');
}
if (ACLController::checkAccess('EC_Location', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Location&action=index&return_module=EC_Location&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Location');
}
if (ACLController::checkAccess('EC_Location', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_Location&return_module=EC_Location&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Location');
}
