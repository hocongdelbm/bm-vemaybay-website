<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
 
if(ACLController::checkAccess('EC_Bank_Account', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_Bank_Account&action=EditView&return_module=EC_Bank_Account&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Bank_Account');
}
if(ACLController::checkAccess('EC_Bank_Account', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Bank_Account&action=index&return_module=EC_Bank_Account&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Bank_Account');
}

if(is_admin($current_user)){
    if(ACLController::checkAccess('EC_Bank_Account', 'list', true)){
        $module_menu[] 	= array("index.php?module=EC_Bank_Account&action=get_bank&return_module=EC_Bank_Account&return_action=get_bank", "Quản lý bank", 'EC_Bank_Account');
    }
}