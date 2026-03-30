<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $current_user;

if ($current_user->id == '1' && ACLController::checkAccess('EC_ChiTietTaiKhoan', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_ChiTietTaiKhoan&action=EditView&return_module=EC_ChiTietTaiKhoan&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_ChiTietTaiKhoan');
}

if (ACLController::checkAccess('EC_ChiTietTaiKhoan', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_ChiTietTaiKhoan&action=index&return_module=EC_ChiTietTaiKhoan&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_ChiTietTaiKhoan');
}
