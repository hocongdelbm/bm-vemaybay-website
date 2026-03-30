<?php


$dictionary['EC_WorkingOverTimeDetails'] = array(
    'table' => 'ec_workingovertimedetails',
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
VardefManager::createVardef('EC_WorkingOverTimeDetails', 'EC_WorkingOverTimeDetails', array('basic', 'assignable', 'security_groups'));
