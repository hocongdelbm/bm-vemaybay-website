<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_Bank_Account', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Bank_Account&action=EditView&return_module=EC_Bank_Account&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Bank_Account');
}
if(ACLController::checkAccess('EC_Bank_Account', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Bank_Account&action=index&return_module=EC_Bank_Account&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Bank_Account');
}
// if(ACLController::checkAccess('EC_Bank_Account', 'import', true)){
//     $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_Bank_Account&return_module=EC_Bank_Account&return_action=index', $mod_strings['LNK_IMPORT_EC_BANK_ACCOUNT'], 'Import', 'EC_Bank_Account');
// }


if(isAllowedUser()){
    if(ACLController::checkAccess('EC_Bank_Account', 'list', true)){
        $module_menu[] 	= array("index.php?module=EC_Bank_Account&action=get_bank&return_module=EC_Bank_Account&return_action=get_bank", "Quản lý bank", 'EC_Bank_Account');
    }
}