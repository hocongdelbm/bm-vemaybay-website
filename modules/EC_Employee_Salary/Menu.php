<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
if (ACLController::checkAccess('EC_Employee_Salary', 'view', true)) {

    if(in_array($current_user->id, ['72ece22c-cb25-8e30-9dea-56f2201cd359', 'b5523dbd-b9a7-67c0-77b5-533e6ece89b1', '9ba5c5a0-a402-02f4-76d3-53ba0481ce45']))
        $module_menu[] = array("index.php?module=EC_Employee_Salary&action=employeesalary&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng tính lương", "EC_Employee_Salary", 'EC_Employee_Salary');
    
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=timesheets&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng chấm công", "EC_Employee_Salary", 'EC_Employee_Salary');
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=usedleaveday&return_module=EC_Employee_Salary&return_action=DetailView", "Số phép đã sử dụng", "EC_Employee_Salary", 'EC_Employee_Salary');
   
    $module_menu[] = array("index.php?module=EC_Employee_Salary&action=advance&return_module=EC_Employee_Salary&return_action=DetailView", "Báo cáo hoàn ứng", "EC_Employee_Salary", 'EC_Employee_Salary');
  
    if (is_admin($current_user)) {
        $module_menu[] = array("index.php?module=EC_Employee_Salary&action=extramoney&return_module=EC_Employee_Salary&return_action=DetailView", "Bảng thu nhập", "EC_Employee_Salary", 'EC_Employee_Salary');

        if (is_admin($current_user) && $current_user->user_name == 'hungnh') {
            $module_menu[] = array("index.php?module=EC_Employee_Salary&action=test&return_module=EC_Employee_Salary&return_action=DetailView", "Test", "EC_Employee_Salary", 'EC_Employee_Salary');
        }
    }
}
