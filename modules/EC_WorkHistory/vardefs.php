<?php
$dictionary['EC_WorkHistory'] = array(
    'table' => 'ec_workhistory',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
		'date_start' =>
		array(
			'required' => false,
			'name' => 'date_start',
			'vname' => 'LBL_DATE_START',
			'type' => 'date',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 1,
			'reportable' => 0,
		),
		'date_end' =>
		array(
			'required' => false,
			'name' => 'date_end',
			'vname' => 'LBL_DATE_END',
			'type' => 'date',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 1,
			'reportable' => 0,
		),
		'status' =>
		array(
			'required' => false,
			'name' => 'status',
			'vname' => 'LBL_STATUS',
			'type' => 'enum',
			'massupdate' => '0',
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 1,
			'reportable' => 0,
			'len' => 10,
			'options' => 'work_history_status_list',
			'studio' => 'visible',
			'dependency' => false,
		),
		'with_salary' =>
		array(
			'required' => false,
			'name' => 'with_salary',
			'vname' => 'LBL_WITH_SALARY',
			'type' => 'bool',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 1,
			'reportable' => 0,
			'len' => '1',
		),
	),
    'relationships' => array (
),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_WorkHistory', 'EC_WorkHistory', array('basic','assignable','security_groups'));
