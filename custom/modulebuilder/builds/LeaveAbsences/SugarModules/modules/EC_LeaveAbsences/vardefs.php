<?php


$dictionary['EC_LeaveAbsences'] = array(
    'table' => 'ec_leaveabsences',
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
VardefManager::createVardef('EC_LeaveAbsences', 'EC_LeaveAbsences', array('basic', 'assignable', 'security_groups'));
