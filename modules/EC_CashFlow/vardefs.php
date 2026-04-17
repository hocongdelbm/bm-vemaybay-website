<?php
$dictionary['EC_CashFlow'] = array(
    'table' => 'ec_cashflow',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields'=>
	array (
		'report_date' => 
		array (
			'required' => '1',
			'name' => 'report_date',
			'vname' => 'LBL_REPORT_DATE',
			'type' => 'date',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 0,
			'reportable' => 0,
		),
		'account_number' => 
		array (
			'required' => '1',
			'name' => 'account_number',
			'vname' => 'LBL_ACCOUNT_NUMBER',
			'type' => 'varchar',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 0,
			'reportable' => 0,
			'len' => '255',
		),
		'account_name' => 
		array (
			'required' => '1',
			'name' => 'account_name',
			'vname' => 'LBL_ACCOUNT_NAME',
			'type' => 'varchar',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 0,
			'reportable' => 0,
			'len' => '255',
		),
		'amount' => 
		array (
			'required' => '1',
			'name' => 'amount',
			'vname' => 'LBL_AMOUNT',
			'type' => 'currency',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 0,
			'reportable' => 0,
			'len' => 26,
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
VardefManager::createVardef('EC_CashFlow', 'EC_CashFlow', array('basic','assignable','security_groups'));
