<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
 
if(ACLController::checkAccess('EC_Salary_Details', 'edit', true))$module_menu[]=Array("index.php?module=EC_Salary_Details&action=EditView&return_module=EC_Salary_Details&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Salary_Details", 'EC_Salary_Details');
if(ACLController::checkAccess('EC_Salary_Details', 'list', true))$module_menu[]=Array("index.php?module=EC_Salary_Details&action=index&return_module=EC_Salary_Details&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Salary_Details", 'EC_Salary_Details');
if(ACLController::checkAccess('EC_Salary_Details', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_Salary_Details&return_module=EC_Salary_Details&return_action=index", $app_strings['LBL_IMPORT'],"Import", 'EC_Salary_Details');