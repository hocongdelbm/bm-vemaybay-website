<?php

$dictionary['EC_Online_Report'] = array(
    'table' => 'ec_online_report',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' =>  array(
        'start_online' =>
        array(
            'required' => false,
            'name' => 'start_online',
            'vname' => 'LBL_START_ONLINE',
            'type' => 'datetime',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
        ),
        // thời điểm Online gần nhất
        'last_online' =>
        array(
            'required' => false,
            'name' => 'last_online',
            'vname' => 'LBL_LAST_OFFLINE',
            'type' => 'datetime',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
        ),
        'status' =>
        array(
            'required' => false,
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 1,
            'reportable' => 0,
            'len' => 100,
            'options' => 'online_stt_list',
            'studio' => 'visible',
            'dependency' => false,
        ),
        // xếp hạng
        'ranking' =>
        array(
            'required' => false,
            'name' => 'ranking',
            'vname' => 'LBL_RANKING',
            'type' => 'int',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
            'len' => '11',
            'disable_num_format' => '',
        ),
        // vòng giao booking
        'round' =>
        array(
            'required' => false,
            'name' => 'round',
            'vname' => 'LBL_ROUND',
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
        // chức danh
        'title' =>
        array(
            'required' => '1',
            'name' => 'title',
            'vname' => 'LBL_TITLE',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 0,
            'reportable' => 0,
            'len' => '100',
        ),
        // điểm kpi
        'kpi' =>
        array(
            'required' => false,
            'name' => 'kpi',
            'vname' => 'LBL_KPI',
            'type' => 'int',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
            'len' => '11',
            'disable_num_format' => '',
        ),
        // booking được giao
        'booking_id' =>
        array(
            'required' => false,
            'name' => 'booking_id',
            'vname' => '',
            'type' => 'id',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 0,
            'len' => 36,
        ),
        // thời điểm bắt đầu giao booking để kt
        'start_assign' =>
        array(
            'required' => false,
            'name' => 'start_assign',
            'vname' => 'LBL_START_ASSIGN',
            'type' => 'datetime',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
        ),

        'total_calls' =>
        array(
            'required' => false,
            'name' => 'total_calls',
            'vname' => 'LBL_TOTAL_CALLS',
            'type' => 'int',
            'massupdate' => 0,
            'comments' => 'Tổng số cuộc gọi trong ngày',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 0,
            'reportable' => 0,
            'len' => '5',
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

VardefManager::createVardef('EC_Online_Report', 'EC_Online_Report', array('basic', 'assignable', 'security_groups'));
