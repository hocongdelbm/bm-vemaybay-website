<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_WorkHistory', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_WorkHistory&action=EditView&return_module=EC_WorkHistory&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_WorkHistory');
}
if (ACLController::checkAccess('EC_WorkHistory', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_WorkHistory&action=index&return_module=EC_WorkHistory&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_WorkHistory');
}
