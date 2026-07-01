<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airports';
$listViewDefs[$module_name] = array(
    'IATA_CODE' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_IATA_CODE',
        'width'   => '8%',
        'default' => true,
        'link'    => true,
    ),
    'NAME' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_NAME',
        'width'   => '25%',
        'default' => true,
    ),
    'CITY_NAME' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_CITY_NAME',
        'width'   => '20%',
        'default' => true,
    ),
    'REGION_CODE' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_REGION_CODE',
        'width'   => '10%',
        'default' => true,
    ),
    'REGION_NAME' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_REGION_NAME',
        'width'   => '15%',
        'default' => true,
    ),
    'PREFIX' => array(
        'type'    => 'enum',
        'label'   => 'LBL_PREFIX',
        'width'   => '10%',
        'default' => false,
    ),
    'GEO_COUNTRY' => array(
        'type'    => 'enum',
        'label'   => 'LBL_GEO_COUNTRY',
        'width'   => '15%',
        'default' => false,
    ),
    'IS_ACTIVE' => array(
        'type'    => 'bool',
        'label'   => 'LBL_IS_ACTIVE',
        'width'   => '10%',
        'default' => true,
    ),
);
