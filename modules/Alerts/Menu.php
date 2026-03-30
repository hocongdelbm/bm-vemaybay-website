<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $mod_strings, $app_strings, $sugar_config;

        
if (ACLController::checkAccess('Alerts', 'edit', true)) {
    $module_menu[]=array("index.php?module=Alerts&action=EditView&return_module=Alerts&return_action=index", $mod_strings['LNK_NEW_RECORD'],"Create", 'Alerts');
}

if (ACLController::checkAccess('Alerts', 'list', true)) {
    $module_menu[]=array("index.php?module=Alerts&action=index&return_module=Alerts&return_action=DetailView", $mod_strings['LNK_LIST'],"List", 'Alerts');
}
