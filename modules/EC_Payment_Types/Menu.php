<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
// if(ACLController::checkAccess('EC_Payment_Types', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Payment_Types&action=EditView&return_module=EC_Payment_Types&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Payment_Types');
// }
// if(ACLController::checkAccess('EC_Payment_Types', 'list', true)){
//     $module_menu[]=array('index.php?module=EC_Payment_Types&action=index&return_module=EC_Payment_Types&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Payment_Types');
// }

if(ACLController::checkAccess('EC_Payment_Types', 'edit', true))$module_menu[]=Array("index.php?module=EC_Payment_Types&action=EditView&return_module=EC_Payment_Types&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Payment_Types", 'EC_Payment_Types');
if(ACLController::checkAccess('EC_Payment_Types', 'list', true))$module_menu[]=Array("index.php?module=EC_Payment_Types&action=index&return_module=EC_Payment_Types&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Payment_Types", 'EC_Payment_Types');
if(ACLController::checkAccess('EC_Payment_Types', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_Payment_Types&return_module=EC_Payment_Types&return_action=index", $mod_strings['LNK_IMPORT_EC_PAYMENT_TYPES'],"Import", 'EC_Payment_Types');