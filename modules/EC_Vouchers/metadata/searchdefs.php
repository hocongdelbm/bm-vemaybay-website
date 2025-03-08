<?php

$module_name = 'EC_Vouchers';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            array(
                'name'  => 'campaign_name',
                'label' => 'LBL_CAMPAIGN_NAME',
            ),
            array(
                'name'  => 'website',
                'label' => 'LBL_WEBSITE',
            ),
        ),
        'advanced_search' => array(
            'name',
            array(
                'name'  => 'campaign_name',
                'label' => 'LBL_CAMPAIGN_NAME',
            ),
            array(
                'name'  => 'website',
                'label' => 'LBL_WEBSITE',
            ),
            array(
              'name' => 'type',
              'label' => 'LBL_TYPE',
            ),
			'status',
        ),
    ),
);
