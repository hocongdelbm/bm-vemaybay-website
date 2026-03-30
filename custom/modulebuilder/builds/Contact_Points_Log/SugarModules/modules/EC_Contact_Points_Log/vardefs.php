<?php


$dictionary['EC_Contact_Points_Log'] = array(
    'table' => 'ec_contact_points_log',
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
VardefManager::createVardef('EC_Contact_Points_Log', 'EC_Contact_Points_Log', array('basic', 'assignable', 'security_groups'));
