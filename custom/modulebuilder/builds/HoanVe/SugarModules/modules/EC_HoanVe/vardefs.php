<?php


$dictionary['EC_HoanVe'] = array(
    'table' => 'ec_hoanve',
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
VardefManager::createVardef('EC_HoanVe', 'EC_HoanVe', array('basic', 'assignable', 'security_groups'));
