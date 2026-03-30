<?php


$dictionary['EC_SMS_Logs'] = array(
    'table' => 'ec_sms_logs',
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
VardefManager::createVardef('EC_SMS_Logs', 'EC_SMS_Logs', array('basic', 'assignable', 'security_groups'));
