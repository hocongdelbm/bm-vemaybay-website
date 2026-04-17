<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $mod_strings, $current_user;
// $deparment_info = myGetDepartmentInfo($current_user->department_id);
 
if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=index&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Flight_Bookings", 'EC_Flight_Bookings');
if(ACLController::checkAccess('EC_Flight_Bookings', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=EditView&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Flight_Bookings", 'EC_Flight_Bookings');
if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=airportstatistics&return_module=EC_Flight_Bookings&return_action=airportstatistics", "Phân tích hành trình","airplane_16", 'EC_Flight_Bookings');

// Doanh số booker
if(isManagerUser($current_user->id)) {
	if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=debtopay&return_module=EC_Flight_Bookings&return_action=debtopay", "Công nợ phải trả","debt_16x16", 'EC_Flight_Bookings');
	if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=agentreport&return_module=EC_Flight_Bookings&return_action=agentreport", "Công nợ phải thu","debt_16x16", 'EC_Flight_Bookings');
}

if(!isTelesaleUser($current_user->id)) {
	// Kiểm tra ngày bay
	if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[] = array("index.php?module=EC_Flight_Bookings&action=checkflydate&return_module=EC_Flight_Bookings&return_action=checkflydate", "Kiểm tra ngày bay","calendar_16x16", 'EC_Flight_Bookings');

	// Recheck xuất vé
	if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] = array("index.php?module=EC_Flight_Bookings&action=recheckbk&return_module=EC_Flight_Bookings&return_action=recheckbk", "Recheck xuất vé", "double-check", 'EC_Flight_Bookings');
}

// if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) $module_menu[] = array("index.php?module=EC_Flight_Bookings&action=recoveryorder&return_module=EC_Flight_Bookings&return_action=recoveryorder", "Phục hồi booking","recovery-order-16", 'EC_Flight_Bookings');


if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=assignbk&return_module=EC_Flight_Bookings&return_action=assignbk", "Danh sách online", "justice-scale", 'EC_Flight_Bookings');

// Doanh số booker
if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) $module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=bksalereport&return_module=EC_Flight_Bookings&return_action=bksalereport", $mod_strings['LNK_SALE_REPORT'],"growth", 'EC_Flight_Bookings');

if(isAllowedUser()) {
	if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=bkagent&return_module=EC_Flight_Bookings&return_action=bkagent", "Thống kê vé", "bkagent", 'EC_Flight_Bookings');
}

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true) && !isTelesaleUser($current_user->id)) {

	$module_menu[] = [
		"index.php?module=EC_Flight_Bookings&action=issueticket&return_module=EC_Flight_Bookings&return_action=issueticket",
		"Xuất vé",
		"justice-scale",
		"EC_Flight_Bookings"
	];

	// $module_menu[] = [
	// 	"index.php?module=EC_Flight_Bookings&action=updateflightfare&return_module=EC_Flight_Bookings&return_action=updateflightfare",
	// 	"Cập nhật giá vé",
	// 	"recovery-order-16",
	// 	"EC_Flight_Bookings",
	// ];

	$module_menu[] = [
		"index.php?module=EC_Flight_Bookings&action=clientphonetcb",
		"Tham khảo - TCB",
		"recovery-order-16",
		"",
	];
}

if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=updateflight&return_module=EC_Flight_Bookings&return_action=updateflight", $mod_strings['LNK_UPDATE_FLIGHT'],"EC_Flight_Bookings", 'EC_Flight_Bookings');

if (is_admin($current_user)) {
    $module_menu[] = array(
        "index.php?module=EC_Flight_Bookings&action=telesaleipmgr",
        "Quản lý Login Telesale",
        "security-shield",
        "EC_Flight_Bookings"
    );
}

