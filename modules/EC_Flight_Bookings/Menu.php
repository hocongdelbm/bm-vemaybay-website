<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
$deparment_info = myGetDepartmentInfo($current_user->department_id);

$title_info = $current_user->title;
 
if(ACLController::checkAccess('EC_Flight_Bookings', 'edit', true))$module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=EditView&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Flight_Bookings", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=index&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Flight_Bookings", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))$module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=airportstatistics&return_module=EC_Flight_Bookings&return_action=airportstatistics", "Phân tích hành trình","airplane_16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=debtopay&return_module=EC_Flight_Bookings&return_action=debtopay", "Công nợ phải trả","debt_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=agentreport&return_module=EC_Flight_Bookings&return_action=agentreport", "Công nợ phải thu","debt_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=checkflydate&return_module=EC_Flight_Bookings&return_action=checkflydate", "Kiểm tra ngày bay","calendar_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) $module_menu[] = array("index.php?module=EC_Flight_Bookings&action=recoveryorder&return_module=EC_Flight_Bookings&return_action=recoveryorder", "Phục hồi booking","recovery-order-16", 'EC_Flight_Bookings');

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] = array("index.php?module=EC_Flight_Bookings&action=recheckbk&return_module=EC_Flight_Bookings&return_action=recheckbk", "Recheck xuất vé", "double-check", 'EC_Flight_Bookings');

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=assignbk&return_module=EC_Flight_Bookings&return_action=assignbk", "Danh sách online", "justice-scale", 'EC_Flight_Bookings');

// Doanh số booker
if(isAllowedUser()) {
	if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) $module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=bksalereport&return_module=EC_Flight_Bookings&return_action=bksalereport", $mod_strings['LNK_SALE_REPORT'],"growth", 'EC_Flight_Bookings');
	if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=bkagent&return_module=EC_Flight_Bookings&return_action=bkagent", "Thống kê vé", "bkagent", 'EC_Flight_Bookings');
}

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=issueticket&return_module=EC_Flight_Bookings&return_action=issueticket", "Xuất vé Vietjet", "justice-scale", 'EC_Flight_Bookings');

