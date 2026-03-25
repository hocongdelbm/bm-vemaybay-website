<?php


$dictionary['EC_WorkHistory'] = array(
    'table' => 'ec_workhistory',
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
VardefManager::createVardef('EC_WorkHistory', 'EC_WorkHistory', array('basic', 'assignable', 'security_groups'));
