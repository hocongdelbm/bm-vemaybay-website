<?php

$dictionary['EC_Bank_Account'] = array(
    'table' => 'ec_bank_account',
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
VardefManager::createVardef('EC_Bank_Account', 'EC_Bank_Account', array('basic', 'assignable', 'security_groups'));
