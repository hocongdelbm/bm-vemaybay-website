<?php

$dictionary['EC_Airlines'] = array(
    'table' => 'ec_airlines',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'iata_code' => array(
            'required'  => true,
            'name'      => 'iata_code',
            'vname'     => 'LBL_IATA_CODE',
            'type'      => 'varchar',
            'comment'   => 'Mã IATA hãng bay: VN, VJ, BL, QH, VU, TG, ...',
            'len'       => 3,
            'importable' => 'true',
            'audited'   => 1,
            'reportable' => 1,
            'unified_search' => true,
        ),
        'logo' => array(
            'required'  => false,
            'name'      => 'logo',
            'vname'     => 'LBL_LOGO',
            'type'      => 'varchar',
            'len'       => 255,
            'comment'   => 'URL logo hãng bay (vnbackup/NextCloud)',
            'default'   => '',
            'importable' => 'true',
            'audited'   => 0,
            'reportable' => 0,
        ),
        'country' => array(
            'required'  => false,
            'name'      => 'country',
            'vname'     => 'LBL_COUNTRY',
            'type'      => 'enum',
            'len'       => 100,
            'options'   => 'countries_dom',
            'default'   => '',
            'importable' => 'true',
            'audited'   => 0,
            'reportable' => 1,
        ),
        'is_domestic' => array(
            'required'  => false,
            'name'      => 'is_domestic',
            'vname'     => 'LBL_IS_DOMESTIC',
            'type'      => 'bool',
            'comment'   => 'true = hãng nội địa Việt Nam (VNA, VJA, VNP, BBA, VTA)',
            'default'   => 0,
            'importable' => 'true',
            'audited'   => 0,
            'reportable' => 1,
        ),
        'is_active' => array(
            'required'  => false,
            'name'      => 'is_active',
            'vname'     => 'LBL_IS_ACTIVE',
            'type'      => 'bool',
            'default'   => 1,
            'importable' => 'true',
            'audited'   => 1,
            'reportable' => 1,
        ),

    ),
    'indices' => array(
        array('name' => 'idx_airlines_name',          'type' => 'index',  'fields' => array('name')),
        array('name' => 'idx_airlines_iata_code',     'type' => 'unique', 'fields' => array('iata_code')),
        array('name' => 'idx_airlines_is_active',     'type' => 'index',  'fields' => array('is_active')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Airlines', 'EC_Airlines', array('basic', 'assignable', 'security_groups'));
