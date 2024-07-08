<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
// if(ACLController::checkAccess('EC_Customer', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Customer&action=EditView&return_module=EC_Customer&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Customer');
// }
if(ACLController::checkAccess('EC_Customer', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Customer&action=index&return_module=EC_Customer&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Customer');
}
