<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_WorkingOverTimes', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_WorkingOverTimes&action=EditView&return_module=EC_WorkingOverTimes&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_WorkingOverTimes');
}
if (ACLController::checkAccess('EC_WorkingOverTimes', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_WorkingOverTimes&action=index&return_module=EC_WorkingOverTimes&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_WorkingOverTimes');
}
if (ACLController::checkAccess('EC_WorkingOverTimes', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_WorkingOverTimes&return_module=EC_WorkingOverTimes&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_WorkingOverTimes');
}
