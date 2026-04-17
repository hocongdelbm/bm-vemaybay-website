<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Employee_Salary', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Employee_Salary&action=EditView&return_module=EC_Employee_Salary&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Employee_Salary');
}
if (ACLController::checkAccess('EC_Employee_Salary', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Employee_Salary&action=index&return_module=EC_Employee_Salary&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Employee_Salary');
}
if (ACLController::checkAccess('EC_Employee_Salary', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_Employee_Salary&return_module=EC_Employee_Salary&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Employee_Salary');
}
