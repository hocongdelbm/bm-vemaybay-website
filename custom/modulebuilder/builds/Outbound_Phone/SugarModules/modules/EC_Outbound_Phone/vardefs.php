<?php


$dictionary['EC_Outbound_Phone'] = array(
    'table' => 'ec_outbound_phone',
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
VardefManager::createVardef('EC_Outbound_Phone', 'EC_Outbound_Phone', array('basic', 'assignable', 'security_groups'));
