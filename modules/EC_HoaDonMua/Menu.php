<?php
 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_HoaDonMua', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_HoaDonMua&action=EditView&return_module=EC_HoaDonMua&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_HoaDonMua');
}
if(ACLController::checkAccess('EC_HoaDonMua', 'list', true)){
    $module_menu[]=array('index.php?module=EC_HoaDonMua&action=index&return_module=EC_HoaDonMua&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_HoaDonMua');
}
if(ACLController::checkAccess('EC_HoaDonMua', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_HoaDonMua&return_module=EC_HoaDonMua&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_HoaDonMua');
}