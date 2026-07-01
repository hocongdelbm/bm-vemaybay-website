<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Airlines';
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
                array(
                    'name'       => 'logo',
                    'label'      => 'LBL_LOGO',
                    'customCode' => '{$CUS_LOGO_EDIT}',
                ),
                array('name' => 'country', 'label' => 'LBL_COUNTRY'),
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
