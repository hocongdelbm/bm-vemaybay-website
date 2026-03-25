<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Notes', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Notes&action=EditView&return_module=EC_Notes&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Notes');
}
if (ACLController::checkAccess('EC_Notes', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Notes&action=index&return_module=EC_Notes&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Notes');
}
