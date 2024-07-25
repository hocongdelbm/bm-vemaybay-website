<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_LoaiChungTu', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_LoaiChungTu&action=EditView&return_module=EC_LoaiChungTu&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_LoaiChungTu');
}
if(ACLController::checkAccess('EC_LoaiChungTu', 'list', true)){
    $module_menu[]=array('index.php?module=EC_LoaiChungTu&action=index&return_module=EC_LoaiChungTu&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_LoaiChungTu');
}