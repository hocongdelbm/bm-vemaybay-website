<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_WorkingOverTimeDetails', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_WorkingOverTimeDetails&action=EditView&return_module=EC_WorkingOverTimeDetails&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_WorkingOverTimeDetails');
}
if (ACLController::checkAccess('EC_WorkingOverTimeDetails', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_WorkingOverTimeDetails&action=index&return_module=EC_WorkingOverTimeDetails&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_WorkingOverTimeDetails');
}
if (ACLController::checkAccess('EC_WorkingOverTimeDetails', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_WorkingOverTimeDetails&return_module=EC_WorkingOverTimeDetails&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_WorkingOverTimeDetails');
}
