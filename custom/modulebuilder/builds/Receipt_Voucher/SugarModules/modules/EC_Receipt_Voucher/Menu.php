<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Receipt_Voucher', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Receipt_Voucher&action=EditView&return_module=EC_Receipt_Voucher&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Receipt_Voucher');
}
if (ACLController::checkAccess('EC_Receipt_Voucher', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Receipt_Voucher&action=index&return_module=EC_Receipt_Voucher&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Receipt_Voucher');
}
if (ACLController::checkAccess('EC_Receipt_Voucher', 'import', true)) {
    $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_Receipt_Voucher&return_module=EC_Receipt_Voucher&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Receipt_Voucher');
}
