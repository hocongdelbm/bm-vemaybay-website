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
        'default' => array(
            array(
                array('name' => 'iata_code', 'label' => 'LBL_IATA_CODE'),
                array('name' => 'name',      'label' => 'LBL_NAME'),
            ),
            array(
                array('name' => 'city_name',    'label' => 'LBL_CITY_NAME'),
                array('name' => 'country_code', 'label' => 'LBL_COUNTRY_CODE'),
            ),
            array(
                array('name' => 'is_domestic', 'label' => 'LBL_IS_DOMESTIC'),
                array('name' => 'is_active',   'label' => 'LBL_IS_ACTIVE'),
            ),
            array(
                array(
                    'name'          => 'description',
                    'label'         => 'LBL_DESCRIPTION',
                    'displayParams' => array('cols' => 60, 'rows' => 3),
                ),
                array(''),
            ),
        ),
    ),
);
