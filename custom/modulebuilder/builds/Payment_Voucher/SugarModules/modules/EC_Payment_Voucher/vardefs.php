<?php


$dictionary['EC_Payment_Voucher'] = array(
    'table' => 'ec_payment_voucher',
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
VardefManager::createVardef('EC_Payment_Voucher', 'EC_Payment_Voucher', array('basic', 'assignable', 'security_groups'));
