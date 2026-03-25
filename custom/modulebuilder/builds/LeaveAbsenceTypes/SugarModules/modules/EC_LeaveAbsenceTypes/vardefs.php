<?php


$dictionary['EC_LeaveAbsenceTypes'] = array(
    'table' => 'ec_leaveabsencetypes',
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
VardefManager::createVardef('EC_LeaveAbsenceTypes', 'EC_LeaveAbsenceTypes', array('basic', 'assignable', 'security_groups'));
