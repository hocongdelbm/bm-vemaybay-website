<?php


$dictionary['EC_Vouchers'] = array(
    'table' => 'ec_vouchers',
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
VardefManager::createVardef('EC_Vouchers', 'EC_Vouchers', array('basic', 'assignable', 'security_groups'));
