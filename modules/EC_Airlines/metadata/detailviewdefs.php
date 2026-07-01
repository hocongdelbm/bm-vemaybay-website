<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airlines';
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
        'default' => array(
            array(
                array('name' => 'iata_code', 'label' => 'LBL_IATA_CODE'),
                array('name' => 'name',      'label' => 'LBL_NAME'),
            ),
            array(
                array('name' => 'icao_code', 'label' => 'LBL_ICAO_CODE'),
                array(''),
            ),
            array(
                array(
                    'name'       => 'logo',
                    'label'      => 'LBL_LOGO',
                    'customCode' => '{$CUS_LOGO}',
                ),
                array('name' => 'country', 'label' => 'LBL_COUNTRY'),
            ),
            array(
                array('name' => 'is_domestic', 'label' => 'LBL_IS_DOMESTIC'),
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
