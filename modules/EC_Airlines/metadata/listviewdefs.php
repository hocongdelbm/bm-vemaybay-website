<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airlines';
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
        'width'   => '35%',
        'default' => true,
    ),
    'LOGO' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_LOGO',
        'width'   => '15%',
        'default' => true,
    ),
    'COUNTRY' => array(
        'type'    => 'enum',
        'label'   => 'LBL_COUNTRY',
        'width'   => '20%',
        'default' => true,
    ),
    'IS_DOMESTIC' => array(
        'type'    => 'bool',
        'label'   => 'LBL_IS_DOMESTIC',
        'width'   => '10%',
        'default' => true,
    ),
    'IS_ACTIVE' => array(
        'type'    => 'bool',
        'label'   => 'LBL_IS_ACTIVE',
        'width'   => '10%',
        'default' => true,
    ),
);
