<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
$deparment_info = myGetDepartmentInfo($current_user->department_id);

$title_info = $current_user->title;
 
if(ACLController::checkAccess('EC_Flight_Bookings', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=EditView&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"CreateEC_Flight_Bookings", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=index&return_module=EC_Flight_Bookings&return_action=DetailView", $mod_strings['LNK_LIST'],"EC_Flight_Bookings", 'EC_Flight_Bookings');

// user tuananh, ngandtk
// if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true) && ($title_info == 'Admin' || $title_info == 'Administrator' || $title_info == 'QuanLy' || $current_user->id == '9f381038-99c2-7515-938f-558939fee19a' || $current_user->id == '37cd4853-721c-9808-af64-5600c8835d03')) $module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=bookingqtyreport&return_module=EC_Flight_Bookings&return_action=bookingqtyreport", "Doanh số Booking", "qtyreport_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=airportstatistics&return_module=EC_Flight_Bookings&return_action=airportstatistics", "Phân tích hành trình","airplane_16", 'EC_Flight_Bookings');

// if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=ticketreport&return_module=EC_Flight_Bookings&return_action=ticketreport", "Doanh thu trong ngày","coinicon_16x16", 'EC_Flight_Bookings');

// if(ACLController::checkAccess('EC_Flight_Bookings', 'view', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=currentsales&return_module=EC_Flight_Bookings&return_action=currentsales", $mod_strings['LNK_CURRENT_SALES'],"goldcup_16x16", 'EC_Flight_Bookings'); //Doanh số xuất vé

// if(ACLController::checkAccess('Bugs', 'view', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=yearlyreport&return_module=EC_Flight_Bookings&return_action=yearlyreport", "Báo cáo tổng hợp","linechart_16x16", 'EC_Flight_Bookings');

// if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=employeekpi&return_module=EC_Flight_Bookings&return_action=employeekpi", 'KPI nhân viên',"kpi_icon_16", 'EC_Flight_Bookings');

// if(is_admin($current_user))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=addbonus&return_module=EC_Flight_Bookings&return_action=employeekpi", 'Bonus add thêm',"bonus_16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=debtopay&return_module=EC_Flight_Bookings&return_action=debtopay", "Công nợ phải trả","debt_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=agentreport&return_module=EC_Flight_Bookings&return_action=agentreport", "Công nợ phải thu","debt_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=checkflydate&return_module=EC_Flight_Bookings&return_action=checkflydate", "Kiểm tra ngày bay","calendar_16x16", 'EC_Flight_Bookings');

// if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=smstool&return_module=EC_Flight_Bookings&return_action=smstool", "Quản lý SIM","sim_icon_16x16", 'EC_Flight_Bookings');

// if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true) && $deparment_info['use_mail_confirm'])$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=checksms&return_module=EC_Flight_Bookings&return_action=checksms", "Kiểm tra SMS","sms_16x16", 'EC_Flight_Bookings');

if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) $module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=recoveryorder&return_module=EC_Flight_Bookings&return_action=recoveryorder", "Phục hồi booking","recovery-order-16", 'EC_Flight_Bookings');

// if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=cashflow&return_module=EC_Flight_Bookings&return_action=cashflow", "Báo cáo dòng tiền","finance-16", 'EC_Flight_Bookings');

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] = array("index.php?module=EC_Flight_Bookings&action=recheckbk&return_module=EC_Flight_Bookings&return_action=recheckbk", "Recheck xuất vé", "double-check", 'EC_Flight_Bookings');

if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=assignbk&return_module=EC_Flight_Bookings&return_action=assignbk", "Danh sách online", "justice-scale", 'EC_Flight_Bookings');

// Doanh số booker
if(isAllowedUser()) {
	// if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=profitreport&return_module=EC_Flight_Bookings&return_action=profitreport", "Báo cáo lãi lỗ","profit_16x16", 'EC_Flight_Bookings');
	if (ACLController::checkAccess('EC_Flight_Bookings', 'view', true)) $module_menu[]	= array("index.php?module=EC_Flight_Bookings&action=bksalereport&return_module=EC_Flight_Bookings&return_action=bksalereport", $mod_strings['LNK_SALE_REPORT'],"growth", 'EC_Flight_Bookings');
	// if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=iplist&return_module=EC_Flight_Bookings&return_action=iplist", "Danh sách IP", "ip-location", 'EC_Flight_Bookings');
	if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=bkagent&return_module=EC_Flight_Bookings&return_action=bkagent", "Thống kê vé", "bkagent", 'EC_Flight_Bookings');
	
	// XEM THỜI GIAN THAO TÁC GẦN NHẤT
	// if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true) && $current_user->user_name == 'hungnh') $module_menu[] = Array("index.php?module=EC_Flight_Bookings&action=test&return_module=EC_Flight_Bookings&return_action=test", "TEST", "coinicon_16x16", 'EC_Flight_Bookings');

	// DC CÔNG NỢ
	// if(ACLController::checkAccess('Bugs', 'edit', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=comparedebt&return_module=EC_Flight_Bookings&return_action=comparedebt", "Đối chiếu công nợ","debt_16x16", 'EC_Flight_Bookings');

	// DOANH SỐ NHÓM
	// if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true))$module_menu[]=Array("index.php?module=EC_Flight_Bookings&action=teamreport&return_module=EC_Flight_Bookings&return_action=teamreport", 'Doanh số nhóm',"employee_16x16", 'EC_Flight_Bookings');

	if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) $module_menu[] 	= array("index.php?module=EC_Flight_Bookings&action=issueticket&return_module=EC_Flight_Bookings&return_action=issueticket", "Xuất vé VJ", "justice-scale", 'EC_Flight_Bookings');
}

