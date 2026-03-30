<?php
 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_ChuyenTienNoiBo', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_ChuyenTienNoiBo&action=EditView&return_module=EC_ChuyenTienNoiBo&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_ChuyenTienNoiBo');
}
if(ACLController::checkAccess('EC_ChuyenTienNoiBo', 'list', true)){
    $module_menu[]=array('index.php?module=EC_ChuyenTienNoiBo&action=index&return_module=EC_ChuyenTienNoiBo&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_ChuyenTienNoiBo');
}
// if(ACLController::checkAccess('EC_ChuyenTienNoiBo', 'import', true)){
//     $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_ChuyenTienNoiBo&return_module=EC_ChuyenTienNoiBo&return_action=index', $mod_strings['LNK_IMPORT_EC_CTNB'], 'Import', 'EC_ChuyenTienNoiBo');
// }