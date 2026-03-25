<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_TongHop', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_TongHop&action=EditView&return_module=EC_TongHop&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_TongHop');
}
if (ACLController::checkAccess('EC_TongHop', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_TongHop&action=index&return_module=EC_TongHop&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_TongHop');
}
