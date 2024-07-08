<?php

$dictionary['EC_ChiTietTaiKhoan'] = array(
    'table' => 'ec_chitiettaikhoan',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
		'sotaikhoan' => array(
			'required' => '1',
			'name' => 'sotaikhoan',
			'vname' => 'LBL_SOTAIKHOAN',
			'type' => 'varchar',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => ' ',
			'audited' => 1,
			'reportable' => 0,
			'len' => '10'
		),
		'dunodau' => array(
			'required' => false,
			'name' => 'dunodau',
			'vname' => 'LBL_DUNODAU',
			'type' => 'currency',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => ' ',
			'audited' => 1,
			'reportable' => 0,
			'len' => 26
		),
		'ducodau' => array(
			'required' => false,
			'name' => 'ducodau',
			'vname' => 'LBL_DUCODAU',
			'type' => 'currency',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => ' ',
			'audited' => 1,
			'reportable' => 0,
			'len' => 26
		),
		'parent_name' => array(
			'required' => false,
			'source' => 'non-db',
			'name' => 'parent_name',
			'vname' => 'LBL_FLEX_RELATE',
			'type' => 'parent',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => '',
			'audited' => 1,
			'reportable' => 0,
			'len' => 25,
			'options' => 'chitiettaikhoan_relate_list',
			'studio' => 'visible',
			'type_name' => 'parent_type',
			'id_name' => 'parent_id',
			'parent_type' => 'record_type_display'
		),
		'parent_type' => array(
			'required' => false,
			'name' => 'parent_type',
			'vname' => 'LBL_PARENT_TYPE',
			'type' => 'parent_type',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => 0,
			'audited' => 0,
			'reportable' => 0,
			'len' => 100,
			'dbType' => 'varchar',
			'studio' => 'hidden'
		),
		'parent_id' => array(
			'required' => false,
			'name' => 'parent_id',
			'vname' => 'LBL_PARENT_ID',
			'type' => 'id',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => 0,
			'audited' => 0,
			'reportable' => 0,
			'len' => 36
		),
		'company_id' => array(
			'required' => false,
			'name' => 'company_id',
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
			'len' => 36
		),
		'company' => array(
			'required' => false,
			'source' => 'non-db',
			'name' => 'company',
			'vname' => 'LBL_COMPANY',
			'type' => 'relate',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => ' ',
			'audited' => 1,
			'reportable' => 0,
			'len' => '255',
			'id_name' => 'company_id',
			'ext2' => 'SecurityGroups',
			'module' => 'SecurityGroups',
			'rname' => 'name',
			'quicksearch' => 'enabled',
			'studio' => 'visible'
		),
		'location_id' => array(
			'required' => false,
			'name' => 'location_id',
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
			'len' => 36
		),
		'location' => array(
			'required' => false,
			'source' => 'non-db',
			'name' => 'location',
			'vname' => 'LBL_LOCATION',
			'type' => 'relate',
			'massupdate' => 0,
			'comments' => '',
			'help' => '',
			'importable' => 'true',
			'duplicate_merge' => 'disabled',
			'duplicate_merge_dom_value' => ' ',
			'audited' => 1,
			'reportable' => 0,
			'len' => '255',
			'id_name' => 'location_id',
			'ext2' => 'EC_Location',
			'module' => 'EC_Location',
			'rname' => 'name',
			'quicksearch' => 'enabled',
			'studio' => 'visible'
		)
	),
    'indices' => array(
		0 => array(
			'name' => 'idx_cttk_name',
			'type' => 'index',
			'fields' => array(
				'name'
			)
		),
		1 => array(
			'name' => 'idx_cttk_del',
			'type' => 'index',
			'fields' => array(
				'deleted'
			)
		),
		2 => array(
			'name' => 'idx_cttk_assign',
			'type' => 'index',
			'fields' => array(
				'assigned_user_id'
			)
		),
		3 => array(
			'name' => 'idx_cttk_prtype',
			'type' => 'index',
			'fields' => array(
				'parent_type'
			)
		),
		4 => array(
			'name' => 'idx_cttk_prid',
			'type' => 'index',
			'fields' => array(
				'parent_id'
			)
		),
		5 => array(
			'name' => 'idx_cttk_sotk',
			'type' => 'index',
			'fields' => array(
				'sotaikhoan'
			)
		),
		6 => array(
			'name' => 'idx_cttk_company',
			'type' => 'index',
			'fields' => array(
				'company_id'
			)
		),
		7 => array(
			'name' => 'idx_cttk_location',
			'type' => 'index',
			'fields' => array(
				'location_id'
			)
		)
	),
    'relationships' => array (
),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_ChiTietTaiKhoan', 'EC_ChiTietTaiKhoan', array('basic','assignable','security_groups'));
