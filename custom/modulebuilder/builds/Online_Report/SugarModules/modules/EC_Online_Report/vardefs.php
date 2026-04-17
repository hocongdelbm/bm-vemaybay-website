<?php


$dictionary['EC_Online_Report'] = array(
    'table' => 'ec_online_report',
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
VardefManager::createVardef('EC_Online_Report', 'EC_Online_Report', array('basic', 'assignable', 'security_groups'));
