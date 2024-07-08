<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Input_Invoices', 'edit', true))$module_menu[]=Array("index.php?module=EC_Input_Invoices&action=EditView&return_module=EC_Input_Invoices&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Input_Invoices", 'EC_Input_Invoices');
if(ACLController::checkAccess('EC_Input_Invoices', 'list', true))$module_menu[]=Array("index.php?module=EC_Input_Invoices&action=index&return_module=EC_Input_Invoices&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Input_Invoices", 'EC_Input_Invoices');
if(ACLController::checkAccess('EC_Input_Invoices', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_Input_Invoices&return_module=EC_Input_Invoices&return_action=index", $app_strings['LBL_IMPORT'],"Import", 'EC_Input_Invoices');