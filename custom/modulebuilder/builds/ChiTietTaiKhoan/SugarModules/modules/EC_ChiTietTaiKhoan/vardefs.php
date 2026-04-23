<?php


$dictionary['EC_ChiTietTaiKhoan'] = array(
    'table' => 'ec_chitiettaikhoan',
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
VardefManager::createVardef('EC_ChiTietTaiKhoan', 'EC_ChiTietTaiKhoan', array('basic', 'assignable', 'security_groups'));
