<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airports';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
    ),
    'panels' => array(
        'LBL_PANEL_AIRPORT' => array(
            array(
                array('name' => 'iata_code', 'label' => 'LBL_IATA_CODE'),
                array('name' => 'city_name', 'label' => 'LBL_CITY_NAME'),
            ),
            array(
                array('name' => 'icao_code', 'label' => 'LBL_ICAO_CODE'),
                array(),
            ),
            array(
                array('name' => 'name',      'label' => 'LBL_NAME'),
                array('name' => 'country',   'label' => 'LBL_COUNTRY'),
            ),
            array(
                array('name' => 'geo_country', 'label' => 'LBL_GEO_COUNTRY'),
                array('name' => 'prefix', 'label' => 'LBL_PREFIX'),
            ),
            array(
                array(
                    'name'          => 'description',
                    'label'         => 'LBL_DESCRIPTION',
                    'displayParams' => array('cols' => 60, 'rows' => 3),
                ),
                array('name' => 'is_active',   'label' => 'LBL_IS_ACTIVE'),
            ),
        ),
    ),
);
