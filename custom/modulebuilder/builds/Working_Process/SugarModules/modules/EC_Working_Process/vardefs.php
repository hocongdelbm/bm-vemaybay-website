<?php


$dictionary['EC_Working_Process'] = array(
    'table' => 'ec_working_process',
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
VardefManager::createVardef('EC_Working_Process', 'EC_Working_Process', array('basic', 'assignable', 'security_groups'));
