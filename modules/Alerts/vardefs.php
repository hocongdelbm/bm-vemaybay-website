<?php
$dictionary['Alert'] = array(
    'table' => 'alerts',
    'audited' => false,
    'duplicate_merge' => true,
    'fields' => array(
        'is_read' =>
        array(
            'name'        => 'is_read',
            'vname'     => 'LBL_IS_READ',
            'type'        => 'bool',
            'massupdate' => false,
            'studio' => 'false',
        ),
        'target_module' =>
        array(
            'name'        => 'target_module',
            'vname'     => 'LBL_TYPE',
            'type'        => 'varchar',
            'massupdate' => false,
            'studio' => 'false',
        ),
        'type' =>
        array(
            'name'        => 'type',
            'vname'     => 'LBL_TYPE',
            'type'        => 'varchar',
            'massupdate' => false,
            'studio' => 'false',
        ),
        'url_redirect' =>
        array(
            'name'        => 'url_redirect',
            'vname'     => 'LBL_TYPE',
            'type'        => 'varchar',
            'massupdate' => false,
            'studio' => 'false',
        ),
        'reminder_id' =>
        array(
            'name'        => 'reminder_id',
            'type' => 'id',
            'required' => false,
            'reportable' => false,
            'studio' => 'false',
            'comment' => 'The id of the reminder that created this alert',
        )
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => false,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('Alerts', 'Alert', array('basic', 'assignable'));
