<?php

$dictionary['EC_Airports'] = array(
    'table' => 'ec_airports',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'iata_code' => array(
            'name'      => 'iata_code',
            'vname'     => 'LBL_IATA_CODE',
            'type'      => 'varchar',
            'comment'   => 'Mã IATA sân bay: HAN, SGN, DXB, BKK, ...',
            'len'       => 3,
            'required'  => true,
            'importable' => 'true',
            'audited'   => 1,
            'reportable' => 1,
            'unified_search' => true,
        ),
        'city_name' => array(
            'name'      => 'city_name',
            'vname'     => 'LBL_CITY_NAME',
            'type'      => 'varchar',
            'comment'   => 'Tên thành phố: "Hà Nội", "Hồ Chí Minh", "Dubai"',
            'len'       => 100,
            'default'   => '',
            'importable' => 'true',
            'audited'   => 0,
            'reportable' => 1,
        ),
        'country_code' => array(
            'required'  => false,
            'name'      => 'country_code',
            'vname'     => 'LBL_COUNTRY_CODE',
            'type'      => 'varchar',
            'comment'   => 'Mã quốc gia ISO 2 ký tự: VN, AE, TH, SG, ...',
            'len'       => 2,
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
        array('name' => 'idx_airports_iata_code',      'type' => 'unique', 'fields' => array('iata_code')),
        array('name' => 'idx_airports_name',           'type' => 'index',  'fields' => array('name')),
        array('name' => 'idx_airports_city_name',      'type' => 'index',  'fields' => array('city_name')),
        array('name' => 'idx_airports_is_domestic', 'type' => 'index', 'fields' => array('is_domestic')),
        array('name' => 'idx_airports_is_active',      'type' => 'index',  'fields' => array('is_active')),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Airports', 'EC_Airports', array('basic', 'assignable', 'security_groups'));
