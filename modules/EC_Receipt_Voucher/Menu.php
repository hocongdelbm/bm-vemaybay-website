<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;

// if(ACLController::checkAccess('EC_Receipt_Voucher', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Receipt_Voucher&action=EditView&return_module=EC_Receipt_Voucher&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Receipt_Voucher');
// }
// if(ACLController::checkAccess('EC_Receipt_Voucher', 'list', true)){
//     $module_menu[]=array('index.php?module=EC_Receipt_Voucher&action=index&return_module=EC_Receipt_Voucher&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Receipt_Voucher');
// }
// if(ACLController::checkAccess('EC_Receipt_Voucher', 'import', true)){
//     $module_menu[]=array('index.php?module=Import&action=Step1&import_module=EC_Receipt_Voucher&return_module=EC_Receipt_Voucher&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_Receipt_Voucher');
// }

if (ACLController::checkAccess('EC_Receipt_Voucher', 'edit', true)) $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=EditView&return_module=EC_Receipt_Voucher&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'], "CreateEC_Receipt_Voucher", 'EC_Receipt_Voucher');

if (ACLController::checkAccess('EC_Receipt_Voucher', 'list', true)) $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=index&return_module=EC_Receipt_Voucher&return_action=DetailView", $mod_strings['LNK_LIST'], "EC_Receipt_Voucher", 'EC_Receipt_Voucher');

if (ACLController::checkAccess('Bugs', 'edit', true)) $module_menu[] = array("index.php?module=EC_ChiTietTaiKhoan&action=index&return_module=EC_ChiTietTaiKhoan&return_action=index", 'Nhập số dư ban đầu', "chitiettaikhoan_16x16", 'EC_Receipt_Voucher');

if (ACLController::checkAccess('Bugs', 'edit', true) || ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) || $current_user->id == '7c20e013-b0d6-e1f3-b113-53deed58f0a2')
    $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=soquytienmat&return_module=EC_Receipt_Voucher&return_action=soquytienmat", $mod_strings['LNK_SOQUYTIENMAT'], "bookicon_16x16", 'EC_Receipt_Voucher');

if (ACLController::checkAccess('Bugs', 'view', true)) $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=tienguinganhang&return_module=EC_Receipt_Voucher&return_action=tienguinganhang", $mod_strings['LNK_TIENGUINGANHANG'], "bank_16x16", 'EC_Receipt_Voucher');

// if (ACLController::checkAccess('Bugs', 'edit', true)) $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=cashflow&return_module=EC_Receipt_Voucher&return_action=cashflow", 'Báo cáo dòng tiền', "finance-16", 'EC_Receipt_Voucher');

if (ACLController::checkAccess('Bugs', 'edit', true)) $module_menu[] = array("index.php?module=EC_Receipt_Voucher&action=sokyquy&return_module=EC_Receipt_Voucher&return_action=sokyquy", "Sổ theo dõi ký quỹ", "debt_16x16", 'EC_Receipt_Voucher');
