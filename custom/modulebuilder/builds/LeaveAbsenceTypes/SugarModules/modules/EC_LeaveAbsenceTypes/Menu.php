<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_LeaveAbsenceTypes', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_LeaveAbsenceTypes&action=EditView&return_module=EC_LeaveAbsenceTypes&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_LeaveAbsenceTypes');
}
if (ACLController::checkAccess('EC_LeaveAbsenceTypes', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_LeaveAbsenceTypes&action=index&return_module=EC_LeaveAbsenceTypes&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_LeaveAbsenceTypes');
}
if (ACLController::checkAccess('EC_LeaveAbsenceTypes', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_LeaveAbsenceTypes&return_module=EC_LeaveAbsenceTypes&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_LeaveAbsenceTypes');
}
