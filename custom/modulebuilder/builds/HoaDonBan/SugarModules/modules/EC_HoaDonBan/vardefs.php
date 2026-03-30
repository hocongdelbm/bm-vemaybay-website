<?php


$dictionary['EC_HoaDonBan'] = array(
    'table' => 'ec_hoadonban',
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
VardefManager::createVardef('EC_HoaDonBan', 'EC_HoaDonBan', array('basic', 'assignable', 'security_groups'));
