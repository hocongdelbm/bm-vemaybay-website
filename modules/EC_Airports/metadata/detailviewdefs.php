<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airports';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array('EDIT', 'DUPLICATE', 'DELETE'),
        ),
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
                array('name' => 'name',      'label' => 'LBL_NAME'),
            ),
            array(
                array('name' => 'city_name',   'label' => 'LBL_CITY_NAME'),
                array('name' => 'region_code', 'label' => 'LBL_REGION_CODE'),
            ),
            array(
                array('name' => 'region_name', 'label' => 'LBL_REGION_NAME'),
                array('name' => 'prefix',      'label' => 'LBL_PREFIX'),
            ),
            array(
                array('name' => 'geo_country', 'label' => 'LBL_GEO_COUNTRY'),
                array('name' => 'is_active',   'label' => 'LBL_IS_ACTIVE'),
            ),
            array(
                array('name' => 'description', 'label' => 'LBL_DESCRIPTION'),
                array(''),
            ),
            array(
                array(
                    'name'       => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label'      => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name'       => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label'      => 'LBL_DATE_MODIFIED',
                ),
            ),
        ),
    ),
);
