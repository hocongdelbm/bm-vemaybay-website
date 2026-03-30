<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Zalo_Contacts', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Zalo_Contacts&action=EditView&return_module=EC_Zalo_Contacts&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Zalo_Contacts');
}
if (ACLController::checkAccess('EC_Zalo_Contacts', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Zalo_Contacts&action=index&return_module=EC_Zalo_Contacts&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Zalo_Contacts');
}
