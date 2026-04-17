<?php


$dictionary['EC_ChuyenTienNoiBo'] = array(
    'table' => 'ec_chuyentiennoibo',
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
VardefManager::createVardef('EC_ChuyenTienNoiBo', 'EC_ChuyenTienNoiBo', array('basic', 'assignable', 'security_groups'));
