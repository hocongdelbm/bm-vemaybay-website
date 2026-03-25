<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Contact_Points_Log', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Contact_Points_Log&action=EditView&return_module=EC_Contact_Points_Log&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Contact_Points_Log');
}
if (ACLController::checkAccess('EC_Contact_Points_Log', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Contact_Points_Log&action=index&return_module=EC_Contact_Points_Log&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Contact_Points_Log');
}
