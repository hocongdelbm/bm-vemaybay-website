<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_LoginAudit', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_LoginAudit&action=EditView&return_module=EC_LoginAudit&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_LoginAudit');
}
if(ACLController::checkAccess('EC_LoginAudit', 'list', true)){
    $module_menu[]=array('index.php?module=EC_LoginAudit&action=index&return_module=EC_LoginAudit&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_LoginAudit');
}
// if(ACLController::checkAccess('EC_LoginAudit', 'import', true)){
//     $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_LoginAudit&return_module=EC_LoginAudit&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_LoginAudit');
// }