<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_NhomTaiKhoan', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_NhomTaiKhoan&action=EditView&return_module=EC_NhomTaiKhoan&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_NhomTaiKhoan');
}
if (ACLController::checkAccess('EC_NhomTaiKhoan', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_NhomTaiKhoan&action=index&return_module=EC_NhomTaiKhoan&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_NhomTaiKhoan');
}
if (ACLController::checkAccess('EC_NhomTaiKhoan', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_NhomTaiKhoan&return_module=EC_NhomTaiKhoan&return_action=index', $mod_strings['LNK_IMPORT_EC_NHOMTAIKHOAN'], 'Import', 'EC_NhomTaiKhoan');
}
