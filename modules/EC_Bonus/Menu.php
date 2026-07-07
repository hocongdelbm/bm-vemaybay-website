<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Bonus', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Bonus&action=index&return_module=EC_Bonus&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Bonus');
}
if (ACLController::checkAccess('EC_Bonus', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Bonus&action=calculate_bonus', $mod_strings['LNK_CALCULATE_BONUS'], 'Reports', 'EC_Bonus');
}
