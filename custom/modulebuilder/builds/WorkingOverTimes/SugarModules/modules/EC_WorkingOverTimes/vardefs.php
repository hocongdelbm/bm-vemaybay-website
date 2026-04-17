<?php


$dictionary['EC_WorkingOverTimes'] = array(
    'table' => 'ec_workingovertimes',
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
VardefManager::createVardef('EC_WorkingOverTimes', 'EC_WorkingOverTimes', array('basic', 'assignable', 'security_groups'));
