<?php


$dictionary['EC_ChiTietHoanVe'] = array(
    'table' => 'ec_chitiethoanve',
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
VardefManager::createVardef('EC_ChiTietHoanVe', 'EC_ChiTietHoanVe', array('basic', 'assignable', 'security_groups'));
