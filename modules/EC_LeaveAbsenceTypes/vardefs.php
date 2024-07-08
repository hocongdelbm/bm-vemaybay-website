<?php

$dictionary['EC_LeaveAbsenceTypes'] = array(
    'table' => 'ec_leaveabsencetypes',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' =>
    array(
        'day_off' =>
        array(
            'required' => false,
            'name' => 'day_off',
            'vname' => 'LBL_DAY_OFF',
            'type' => 'int',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 1,
            'reportable' => 0,
            'len' => '11',
            'disable_num_format' => '',
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_LeaveAbsenceTypes', 'EC_LeaveAbsenceTypes', array('basic', 'assignable', 'security_groups'));
