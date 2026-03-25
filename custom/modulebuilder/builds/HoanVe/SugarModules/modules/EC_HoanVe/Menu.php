<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_HoanVe', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_HoanVe&action=EditView&return_module=EC_HoanVe&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_HoanVe');
}
if (ACLController::checkAccess('EC_HoanVe', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_HoanVe&action=index&return_module=EC_HoanVe&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_HoanVe');
}
