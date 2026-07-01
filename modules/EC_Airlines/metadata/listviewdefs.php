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
    'ICAO_CODE' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_ICAO_CODE',
        'width'   => '8%',
        'default' => true,
    ),
    'NAME' => array(
        'type'    => 'varchar',
        'label'   => 'LBL_NAME',
        'width'   => '35%',
        'default' => true,
    ),
    'LOGO' => array(
        'type'       => 'varchar',
        'label'      => 'LBL_LOGO',
        'width'      => '15%',
        'default'    => true,
        'sortable'   => false,
        'customCode' => '<img src="{$LOGO}" alt="logo" style="max-height:32px;max-width:70px;object-fit:contain;" onerror="this.style.display=\'none\'">',
    ),
    'COUNTRY' => array(
        'type'    => 'enum',
        'label'   => 'LBL_COUNTRY',
        'width'   => '20%',
        'default' => true,
    ),
    'IS_ACTIVE' => array(
        'type'    => 'bool',
        'label'   => 'LBL_IS_ACTIVE',
        'width'   => '10%',
        'default' => true,
    ),
);
