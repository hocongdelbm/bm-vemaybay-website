<?php
$dictionary['EC_Zalo'] = array(
    'table' => 'ec_zalo',
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
VardefManager::createVardef('EC_Zalo', 'EC_Zalo', array('basic', 'assignable', 'security_groups'));
