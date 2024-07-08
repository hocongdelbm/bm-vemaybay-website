<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
if (ACLController::checkAccess('EC_Employee_Salary', 'view', true)) {
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=employeesalary&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng tính lương", "EC_Employee_Salary", 'EC_Employee_Salary');
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=timesheets&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng chấm công", "EC_Employee_Salary", 'EC_Employee_Salary');
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=usedleaveday&return_module=EC_Employee_Salary&return_action=DetailView", "Số phép đã sử dụng", "EC_Employee_Salary", 'EC_Employee_Salary');
   
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=advance&return_module=EC_Employee_Salary&return_action=DetailView", "Báo cáo hoàn ứng", "EC_Employee_Salary", 'EC_Employee_Salary');
    if (isAllowedUser()) {
        $module_menu[] = array("index.php?module=EC_Employee_Salary&action=extramoney&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng thu nhập", "EC_Employee_Salary", 'EC_Employee_Salary');

        if (is_admin($current_user) && $current_user->user_name == 'hungnh') {
            $module_menu[] = array("index.php?module=EC_Employee_Salary&action=test&return_module=EC_Employee_Salary&return_action=DetailView", "Test", "EC_Employee_Salary", 'EC_Employee_Salary');
        }
    }
}
