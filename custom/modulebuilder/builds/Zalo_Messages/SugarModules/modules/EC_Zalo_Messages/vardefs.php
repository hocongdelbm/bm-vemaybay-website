<?php


$dictionary['EC_Zalo_Messages'] = array(
    'table' => 'ec_zalo_messages',
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
VardefManager::createVardef('EC_Zalo_Messages', 'EC_Zalo_Messages', array('basic', 'assignable', 'security_groups'));
