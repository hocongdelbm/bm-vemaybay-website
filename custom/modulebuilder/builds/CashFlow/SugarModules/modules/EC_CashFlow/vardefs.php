<?php


$dictionary['EC_CashFlow'] = array(
    'table' => 'ec_cashflow',
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
VardefManager::createVardef('EC_CashFlow', 'EC_CashFlow', array('basic', 'assignable', 'security_groups'));
