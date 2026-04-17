<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Debts', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Debts&action=EditView&return_module=EC_Debts&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Debts');
}
if (ACLController::checkAccess('EC_Debts', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Debts&action=index&return_module=EC_Debts&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Debts');
}

if(ACLController::checkAccess('EC_Debts', 'list', true)){
    $module_menu[]= array("index.php?module=EC_Debts&action=debttopay&return_module=EC_Debts&return_action=debttopay", "Chi tiết CN phải trả","debt_16x16", 'EC_Debts');
}

if (ACLController::checkAccess('EC_Debts', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_Debts&return_module=EC_Debts&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Debts');
}
