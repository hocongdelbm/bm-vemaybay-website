<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
 
if(ACLController::checkAccess('EC_LeaveAbsences', 'edit', true))$module_menu[]=Array("index.php?module=EC_LeaveAbsences&action=EditView&return_module=EC_LeaveAbsences&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_LeaveAbsences", 'EC_LeaveAbsences');
if(ACLController::checkAccess('EC_LeaveAbsences', 'list', true))$module_menu[]=Array("index.php?module=EC_LeaveAbsences&action=index&return_module=EC_LeaveAbsences&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_LeaveAbsences", 'EC_LeaveAbsences');
// if(ACLController::checkAccess('EC_LeaveAbsences', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_LeaveAbsences&return_module=EC_LeaveAbsences&return_action=index", $app_strings['LBL_IMPORT'],"Import", 'EC_LeaveAbsences');
