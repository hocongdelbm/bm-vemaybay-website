<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;

$deparment_info = myGetDepartmentInfo($current_user->department_id);
$title_info = $current_user->title;

// PANDA
$arr_user_whitelist = [
    'pandadth',
    'hungnh',
];
$is_panda = (in_array($GLOBALS['current_user']->user_name, $arr_user_whitelist));

if (is_admin($current_user)) {
    if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=businessreport&return_module=EC_TongHop&return_action=businessreport&date_select=this_week", "Báo cáo tuần", "businessreport", 'EC_TongHop');
    if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=bkreport_telesale&return_module=EC_TongHop&return_action=bkreport_telesale", "Doanh số BK Telesale", "goldcup_16x16", 'EC_TongHop');
}

// if (ACLController::checkAccess('EC_TongHop', 'edit', true)) {
//     $module_menu[] = array('index.php?module=EC_TongHop&action=EditView&return_module=EC_TongHop&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_TongHop');
// }
// if (ACLController::checkAccess('EC_TongHop', 'list', true)) {
//     $module_menu[] = array('index.php?module=EC_TongHop&action=index&return_module=EC_TongHop&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_TongHop');
// }

// if (ACLController::checkAccess('EC_TongHop', 'list', true) && ($title_info == 'Admin' || $title_info == 'Administrator' || $title_info == 'QuanLy' || $current_user->id == '9f381038-99c2-7515-938f-558939fee19a' || $current_user->id == '37cd4853-721c-9808-af64-5600c8835d03')) $module_menu[] = array("index.php?module=EC_TongHop&action=bookingqtyreport&return_module=EC_TongHop&return_action=bookingqtyreport", "Doanh số Booking", "qtyreport_16x16", 'EC_TongHop');
if (ACLController::checkAccess('EC_TongHop', 'list', true) && ($title_info == 'Admin' || $title_info == 'Administrator' || $title_info == 'QuanLy' || $current_user->id == '9f381038-99c2-7515-938f-558939fee19a' || $current_user->id == '37cd4853-721c-9808-af64-5600c8835d03')) $module_menu[] = array("index.php?module=EC_TongHop&action=report_sales_create&return_module=EC_TongHop&return_action=report_sales_create", "Doanh số Booking", "qtyreport_16x16", 'EC_TongHop');
// if(ACLController::checkAccess('EC_TongHop', 'view', true))$module_menu[]=Array("index.php?module=EC_TongHop&action=currentsales&return_module=EC_TongHop&return_action=currentsales", "Doanh số xuất vé","goldcup_16x16", 'EC_TongHop'); //Doanh số xuất vé old
if (ACLController::checkAccess('EC_TongHop', 'view', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=report_sales_issue&return_module=EC_TongHop&return_action=report_sales_issue", "Doanh số xuất vé", "goldcup_16x16", 'EC_TongHop'); //Doanh số xuất vé new

// if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=ticketreport&return_module=EC_TongHop&return_action=ticketreport", "Doanh thu trong ngày", "coinicon_16x16", 'EC_TongHop');
if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=report_sales_revenue&return_module=EC_TongHop&return_action=report_sales_revenue", "Doanh thu trong ngày", "coinicon_16x16", 'EC_TongHop');

if (is_admin($current_user)) $module_menu[] = array("index.php?module=EC_TongHop&action=addbonus&return_module=EC_TongHop&return_action=employeekpi", 'Bonus add thêm', "bonus_16", 'EC_TongHop');

if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=employeekpi&return_module=EC_TongHop&return_action=employeekpi", 'KPI nhân viên', "kpi_icon_16", 'EC_TongHop');

if (isAllowedUser()) {
    if (ACLController::checkAccess('EC_TongHop', 'edit', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=cashflow&return_module=EC_TongHop&return_action=cashflow", "Báo cáo dòng tiền", "finance-16", 'EC_TongHop');
    if (ACLController::checkAccess('EC_TongHop', 'view', true)) $module_menu[] = array("index.php?module=EC_TongHop&action=yearlyreport&return_module=EC_TongHop&return_action=yearlyreport", "Báo cáo tổng hợp", "linechart_16x16", 'EC_TongHop');

    if (ACLController::checkAccess('EC_TongHop', 'view', true)) $module_menu[]     = array("index.php?module=EC_TongHop&action=profitreport&return_module=EC_TongHop&return_action=profitreport", "Báo cáo lãi lỗ", "profit_16x16", 'EC_TongHop');
    // if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] 	= array("index.php?module=EC_TongHop&action=iplist&return_module=EC_TongHop&return_action=iplist", "IP Tracking", "ip-location", 'EC_TongHop');
    // if (ACLController::checkAccess('EC_TongHop', 'list', true)) $module_menu[] 	= array("index.php?module=EC_TongHop&action=analytics&return_module=EC_TongHop&return_action=analytics", "Analytics TCB", "analytics", 'EC_TongHop');
}

if (ACLController::checkAccess('EC_Contact_Points_Log', 'list', true)) {
    $module_menu[] = [
        'index.php?module=EC_Contact_Points_Log&action=dashboard',
        $mod_strings['LNK_DASHBOARD'],
        'linechart_16x16',
        'EC_Contact_Points_Log'
    ];
}

if (is_admin($current_user)) {
    if (ACLController::checkAccess('EC_TongHop', 'view', true)) {
        $module_menu[] = ["index.php?module=EC_TongHop&action=summaryview&return_module=EC_TongHop&return_action=summaryview", "Chỉ số website", "EC_TongHop"];
    }

    // if (in_array($GLOBALS['current_user']->user_name, ['hungnh', 'admin'])) {
    //     $module_menu[] = ["index.php?module=EC_TongHop&action=test&return_module=EC_TongHop&return_action=test", "TEST", "EC_TongHop"];
    // }
}
