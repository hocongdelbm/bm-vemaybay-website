<?php
$dictionary['EC_Zalo_Contacts'] = array(
    'table' => 'ec_zalo_contacts',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'zalo_id' => array(
            'name'       => 'zalo_id',
            'vname'      => 'LBL_ZALO_ID',
            'type'       => 'varchar',
            'len'        => 24,
            'default'    => '',
            'importable' => 1,
            'audited'    => 1,
        ),

        'oa_id' => array(
            'name'       => 'oa_id',
            'vname'      => 'LBL_OA_ID',
            'type'       => 'varchar',
            'len'        => 24,
            'default'    => '',
            'importable' => 1,
            'audited'    => 1,
        ),

        'contact_id' => array(
            'name' => 'contact_id',
            'vname' => 'LBL_CONTACT_ID',
            'type' => 'id',
            'length' => 36,
            'default' => '',
            'reportable' => 0,
            'audited' => 1,
            'massupdate' => 0,
        ),
        'contact_name' => array(
			'name' => 'contact_name',
			'vname' => 'LBL_CONTACT',
			'type' => 'relate',
			'source' => 'non-db',
			'id_name' => 'contact_id',
			'ext2' => 'Contacts',
			'module' => 'Contacts',
			'rname' => 'name',
			'quicksearch' => 'enabled',
			'studio' => 'visible',
		),

        'alias' => array(
            'name'       => 'alias',
            'vname'      => 'LBL_ALIAS',
            'type'       => 'varchar',
            'len'        => 150,
            'default'    => '',
            'comment'    => 'Name given by staff',
            'importable' => 1,
            'audited'    => 1,
        ),

        'avatar' => array(
            'name'       => 'avatar',
            'vname'      => 'LBL_AVATAR',
            'type'       => 'varchar',
            'len'        => 150,
            'default'    => '',
            'comment'    => 'Image link',
            'importable' => 1,
            'audited'    => 1,
        ),

        'birth_date' => array(
            'name' => 'birth_date',
            'vname' => 'LBL_BIRTH_DATE',
            'type' => 'date',
            'audited' => 1,
            'massupdate' => 0,
        ),

        'last_interaction' => array(
            'name' => 'last_interaction',
            'vname' => 'LBL_LAST_INTERACTION',
            'type' => 'datetime',
            'audited' => 1,
            'massupdate' => 0,
        ),

        'is_follower' => array(
            'name'       => 'is_follower',
            'vname'      => 'LBL_IS_FOLLOWER',
            'type'       => 'bool',
            'default'    => 0,
            'importable' => 1,
            'audited'    => 1,
        ),

        'tags' => array(
            'name'       => 'tags',
            'vname'      => 'LBL_TAGS',
            'type'       => 'varchar',
            'len'        => 128,
            'default'    => '',
            'comment'    => 'List tags',
            'importable' => 1,
            'audited'    => 1,
        ),

        'province_city' => array(
            'name'       => 'province_city',
            'vname'      => 'LBL_PROVINCE_CITY',
            'type'       => 'varchar',
            'len'        => 64,
            'default'    => '',
            'importable' => 1,
            'audited'    => 1,
        ),

        'ward_commune' => array(
            'name'       => 'ward_commune',
            'vname'      => 'LBL_WARD_COMMUNE',
            'type'       => 'varchar',
            'len'        => 64,
            'default'    => '',
            'importable' => 1,
            'audited'    => 1,
        ),

        'address' => array(
            'name'       => 'address',
            'vname'      => 'LBL_ADDRESS',
            'type'       => 'varchar',
            'len'        => 100,
            'default'    => '',
            'importable' => 1,
            'audited'    => 1,
        ),

        'quota_info' => array(
            'name'       => 'quota_info',
            'vname'      => 'LBL_QUOTA_INFO',
            'type'       => 'varchar',
            'len'        => 300,
            'default'    => '',
            'comment'    => 'JSON string for user quota data',
            'importable' => true,
            'audited'    => true,
        ),
    ),
    'indices' => array(
        array('name' => 'idx_zalocontact_id', 'type' => 'unique', 'fields' => array('zalo_id', 'oa_id')),
        array('name' => 'idx_zalocontact_contact_id', 'type' => 'index', 'fields' => array('contact_id')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Zalo_Contacts', 'EC_Zalo_Contacts', array('basic', 'assignable', 'security_groups'));
