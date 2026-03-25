<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Outbound_Phone', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Outbound_Phone&action=EditView&return_module=EC_Outbound_Phone&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Outbound_Phone');
}
if (ACLController::checkAccess('EC_Outbound_Phone', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Outbound_Phone&action=index&return_module=EC_Outbound_Phone&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Outbound_Phone');
}
