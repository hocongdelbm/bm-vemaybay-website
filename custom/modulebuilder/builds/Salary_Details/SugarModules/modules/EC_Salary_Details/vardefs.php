<?php


$dictionary['EC_Salary_Details'] = array(
    'table' => 'ec_salary_details',
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
VardefManager::createVardef('EC_Salary_Details', 'EC_Salary_Details', array('basic', 'assignable', 'security_groups'));
