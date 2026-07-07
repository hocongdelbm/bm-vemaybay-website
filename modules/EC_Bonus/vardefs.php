<?php
$dictionary['EC_Bonus'] = array(
    'table' => 'ec_bonus',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'source_id' => array(
            'name' => 'source_id',
            'vname' => 'LBL_SOURCE_ID',
            'type' => 'id',
            'len' => 36,
            'default' => '',
            'required' => 1,
            'audited' => 1,
            'importable' => 0,
            'reportable' => 0,
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
        ),
        'source_type' => array(
            'name' => 'source_type',
            'vname' => 'LBL_SOURCE_TYPE',
            'type' => 'parent_type',
            'group' => 'source_name',
            'dbType' => 'varchar',
            'len' => 48,
            'default' => '',
            'required' => 1,
            'audited' => 0,
            'importable' => 0,
            'reportable' => 0,
        ),
        'source_name' => array(
            'source' => 'non-db',
            'name' => 'source_name',
            'vname' => 'LBL_SOURCE_NAME',
            'type' => 'parent',
            'type_name' => 'source_type',
            'id_name' => 'source_id',
            'parent_type' => 'record_type_display',
            'options' => 'parent_type_display',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
        ),

        'bonus_time' => array(
            'name' => 'bonus_time',
            'vname' => 'LBL_BONUS_TIME',
            'type' => 'datetime',
            'comments' => 'UTC timezone',
            'require' => 1,
            'audited' => 1,
            'importable' => 0,
            'reportable' => 0,
        ),

        'kpi' => array(
            'name' => 'kpi',
            'vname' => 'LBL_KPI',
            'comments' => 'KPI count of this user on this parent',
            'type' => 'int',
            'dbtype' => 'smallint',
            'require' => 0,
            'default' => 0,
            'audited' => 1,
            'importable' => 0,
            'reportable' => 0,
        ),

        'direct_bonus' => array(
            'name' => 'direct_bonus',
            'vname' => 'LBL_DIRECT_BONUS',
            'type' => 'currency',
            'len' => '16,3',
            'require' => 0,
            'default' => 0,
            'audited' => 1,
            'importable' => 0,
            'reportable' => 0,
        ),

        'indirect_bonus' => array(
            'name' => 'indirect_bonus',
            'vname' => 'LBL_INDIRECT_BONUS',
            'type' => 'currency',
            'len' => '16,3',
            'require' => 0,
            'default' => 0,
            'audited' => 1,
            'importable' => 0,
            'reportable' => 0,
        ),
    ),
    'indices' => array(
        array('name' => 'idx_bonus_source', 'type' => 'index', 'fields' => array('source_id', 'source_type')),
        array('name' => 'idx_bonus_assign', 'type' => 'index', 'fields' => array('assigned_user_id')),
        array('name' => 'idx_bonus_time', 'type' => 'index', 'fields' => array('bonus_time')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Bonus', 'EC_Bonus', array('basic', 'assignable', 'security_groups'));
