<?php


$dictionary['EC_Debts'] = array(
    'table' => 'ec_debts',
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
VardefManager::createVardef('EC_Debts', 'EC_Debts', array('basic', 'assignable', 'security_groups'));
