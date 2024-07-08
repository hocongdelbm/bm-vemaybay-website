<?php
 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_TaiKhoan', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_TaiKhoan&action=EditView&return_module=EC_TaiKhoan&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_TaiKhoan');
}
if(ACLController::checkAccess('EC_TaiKhoan', 'list', true)){
    $module_menu[]=array('index.php?module=EC_TaiKhoan&action=index&return_module=EC_TaiKhoan&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_TaiKhoan');
}
if(ACLController::checkAccess('EC_TaiKhoan', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_TaiKhoan&return_module=EC_TaiKhoan&return_action=index', $mod_strings['LNK_IMPORT_EC_TAIKHOAN'], 'Import', 'EC_TaiKhoan');
}