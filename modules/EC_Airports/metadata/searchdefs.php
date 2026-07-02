<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airports';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns'      => '3',
        'maxColumnsBasic' => '4',
        'widths'          => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            array('name' => 'iata_code', 'type' => 'varchar', 'label' => 'LBL_IATA_CODE', 'default' => true),
            array('name' => 'name',      'label' => 'LBL_NAME', 'default' => true),
            array('name' => 'city_name', 'type' => 'varchar', 'label' => 'LBL_CITY_NAME', 'default' => true),
            array('name' => 'is_active', 'type' => 'bool',    'label' => 'LBL_IS_ACTIVE', 'default' => true),
        ),
        'advanced_search' => array(
            array('name' => 'iata_code',    'type' => 'varchar', 'label' => 'LBL_IATA_CODE',    'default' => true),
            array('name' => 'name',         'label' => 'LBL_NAME', 'default' => true),
            array('name' => 'city_name',    'type' => 'varchar', 'label' => 'LBL_CITY_NAME',    'default' => true),
            array('name' => 'country',      'type' => 'enum',    'label' => 'LBL_COUNTRY',      'default' => true),
            array('name' => 'is_active',    'type' => 'bool',    'label' => 'LBL_IS_ACTIVE',    'default' => true),
        ),
    ),
);
