<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Commission', 'edit', true))$module_menu[]=Array("index.php?module=EC_Commission&action=EditView&return_module=EC_Commission&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Commission", 'EC_Commission');
if(ACLController::checkAccess('EC_Commission', 'list', true))$module_menu[]=Array("index.php?module=EC_Commission&action=index&return_module=EC_Commission&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Commission", 'EC_Commission');
if(ACLController::checkAccess('EC_Commission', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_Commission&return_module=EC_Commission&return_action=index", $app_strings['LBL_IMPORT'],"Import", 'EC_Commission');