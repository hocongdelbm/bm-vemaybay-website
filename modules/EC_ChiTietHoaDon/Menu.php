<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_ChiTietHoaDon', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_ChiTietHoaDon&action=EditView&return_module=EC_ChiTietHoaDon&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_ChiTietHoaDon');
}
if(ACLController::checkAccess('EC_ChiTietHoaDon', 'list', true)){
    $module_menu[]=array('index.php?module=EC_ChiTietHoaDon&action=index&return_module=EC_ChiTietHoaDon&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_ChiTietHoaDon');
}
if(ACLController::checkAccess('EC_ChiTietHoaDon', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_ChiTietHoaDon&return_module=EC_ChiTietHoaDon&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_ChiTietHoaDon');
}