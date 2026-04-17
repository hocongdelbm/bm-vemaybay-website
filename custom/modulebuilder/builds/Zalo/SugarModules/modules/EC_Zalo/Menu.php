<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Zalo', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Zalo&action=EditView&return_module=EC_Zalo&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Zalo');
}
if (ACLController::checkAccess('EC_Zalo', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Zalo&action=index&return_module=EC_Zalo&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Zalo');
}
