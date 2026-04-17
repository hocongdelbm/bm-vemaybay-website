<?php


$dictionary['EC_Input_Invoices'] = array(
    'table' => 'ec_input_invoices',
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
VardefManager::createVardef('EC_Input_Invoices', 'EC_Input_Invoices', array('basic', 'assignable', 'security_groups'));
