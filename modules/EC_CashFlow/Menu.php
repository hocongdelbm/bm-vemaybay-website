<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_CashFlow', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_CashFlow&action=EditView&return_module=EC_CashFlow&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_CashFlow');
}
if(ACLController::checkAccess('EC_CashFlow', 'list', true)){
    $module_menu[]=array('index.php?module=EC_CashFlow&action=index&return_module=EC_CashFlow&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_CashFlow');
}
