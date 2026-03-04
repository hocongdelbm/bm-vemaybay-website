<?php
$dictionary['EC_Zalo_Apps'] = array(
    'table' => 'ec_zalo_apps',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'access_token' => array(
            'name' => 'access_token',
            'vname' => 'LBL_ACCESS_TOKEN',
            'type' => 'varchar',
            'len' => 512,
            'required' => 0,
            'audited' => 1,
            'massupdate' => 0,
        ),

        'refresh_token' => array(
            'name' => 'refresh_token',
            'vname' => 'LBL_REFRESH_TOKEN',
            'type' => 'varchar',
            'len' => 512,
            'required' => 0,
            'audited' => 1,
            'massupdate' => 0,
        ),

        'expires_at' => array(
            'name' => 'expires_at',
            'vname' => 'LBL_EXPIRES_AT',
            'type' => 'datetime',
            'required' => 0,
            'audited' => 1,
            'massupdate' => 0,
        ),

        'oa_id' => array(
            'name'       => 'oa_id',
            'vname'      => 'LBL_OA_ID',
            'type'       => 'varchar',
            'len'        => 24,
            'default'    => '',
            'importable' => 0,
            'audited'    => 1,
            'massupdate' => 0,
        ),
        'oa_name' => array(
            'name' => 'oa_name',
            'vname' => 'LBL_OA_NAME',
            'type' => 'relate',
			'source' => 'non-db',
			'id_name' => 'oa_id',
			'ext2' => 'EC_Zalo',
			'module' => 'EC_Zalo',
			'rname' => 'name',
			'quicksearch' => 'enabled',
			'studio' => 'visible',
        ),

        'secret_key' => array(
            'name'       => 'secret_key',
            'vname'      => 'LBL_SECRET_KEY',
            'type'       => 'varchar',
            'len'        => 32,
            'default'    => '',
            'importable' => 0,
            'audited'    => 1,
            'massupdate' => 0,
        ),

        'code_verifier' => array(
            'name'       => 'code_verifier',
            'vname'      => 'LBL_CODE_VERIFIER',
            'type'       => 'varchar',
            'len'        => 43,
            'default'    => '',
            'importable' => 0,
            'audited'    => 1,
            'massupdate' => 0,
        ),

        'code_challenge' => array(
            'name'       => 'code_challenge',
            'vname'      => 'LBL_CODE_CHALLENGE',
            'type'       => 'varchar',
            'len'        => 43,
            'default'    => '',
            'importable' => 0,
            'audited'    => 1,
            'massupdate' => 0,
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Zalo_Apps', 'EC_Zalo_Apps', array('basic', 'assignable', 'security_groups'));
