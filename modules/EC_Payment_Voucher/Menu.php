<?php
 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Payment_Voucher&action=EditView&return_module=EC_Payment_Voucher&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Payment_Voucher');
// }
// if(ACLController::checkAccess('EC_Payment_Voucher', 'list', true)){
//     $module_menu[]=array('index.php?module=EC_Payment_Voucher&action=index&return_module=EC_Payment_Voucher&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Payment_Voucher');
// }

global $mod_strings, $app_strings, $sugar_config, $timedate;
$date_format = $timedate->get_date_format();
 
if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))$module_menu[]=Array("index.php?module=EC_Payment_Voucher&action=EditView&return_module=EC_Payment_Voucher&return_action=DetailView&ngayhachtoan=".date($date_format.' H:i'), $mod_strings['LNK_NEW_RECORD'],"CreateEC_Payment_Voucher", 'EC_Payment_Voucher');

if(ACLController::checkAccess('EC_Payment_Voucher', 'list', true))$module_menu[]=Array("index.php?module=EC_Payment_Voucher&action=index&return_module=EC_Payment_Voucher&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Payment_Voucher", 'EC_Payment_Voucher');

if(ACLController::checkAccess('EC_Payment_Voucher', 'list', true))$module_menu[]=Array("index.php?module=EC_Payment_Voucher&action=paymentreport&return_module=EC_Payment_Voucher&return_action=DetailView", $mod_strings['PAYMENT_REPORT'],"EC_Payment_Voucher", 'EC_Payment_Voucher');
