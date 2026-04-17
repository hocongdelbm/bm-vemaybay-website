<?php


$dictionary['EC_Employee_Salary'] = array(
    'table' => 'ec_employee_salary',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Employee_Salary', 'EC_Employee_Salary', array('basic', 'assignable', 'security_groups'));
