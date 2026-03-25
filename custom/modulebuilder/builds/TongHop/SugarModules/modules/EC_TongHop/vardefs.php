<?php


$dictionary['EC_TongHop'] = array(
    'table' => 'ec_tonghop',
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
VardefManager::createVardef('EC_TongHop', 'EC_TongHop', array('basic', 'assignable', 'security_groups'));
