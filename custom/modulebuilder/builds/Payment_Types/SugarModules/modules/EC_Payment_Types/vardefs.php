<?php


$dictionary['EC_Payment_Types'] = array(
    'table' => 'ec_payment_types',
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
VardefManager::createVardef('EC_Payment_Types', 'EC_Payment_Types', array('basic', 'assignable', 'security_groups'));
