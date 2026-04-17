<?php


$dictionary['EC_LoginAudit'] = array(
    'table' => 'ec_loginaudit',
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
VardefManager::createVardef('EC_LoginAudit', 'EC_LoginAudit', array('basic', 'assignable', 'security_groups'));
