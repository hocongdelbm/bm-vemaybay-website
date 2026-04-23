<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_HoaDonBan', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_HoaDonBan&action=EditView&return_module=EC_HoaDonBan&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_HoaDonBan');
}
if (ACLController::checkAccess('EC_HoaDonBan', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_HoaDonBan&action=index&return_module=EC_HoaDonBan&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_HoaDonBan');
}
if (ACLController::checkAccess('EC_HoaDonBan', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_HoaDonBan&return_module=EC_HoaDonBan&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_HoaDonBan');
}
