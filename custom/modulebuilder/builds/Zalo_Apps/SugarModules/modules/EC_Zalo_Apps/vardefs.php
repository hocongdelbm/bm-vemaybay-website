<?php


$dictionary['EC_Zalo_Apps'] = array(
    'table' => 'ec_zalo_apps',
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
VardefManager::createVardef('EC_Zalo_Apps', 'EC_Zalo_Apps', array('basic', 'assignable', 'security_groups'));
