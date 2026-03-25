<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Revenue', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Revenue&action=EditView&return_module=EC_Revenue&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Revenue');
}
if (ACLController::checkAccess('EC_Revenue', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Revenue&action=index&return_module=EC_Revenue&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Revenue');
}
