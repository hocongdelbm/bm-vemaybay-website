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
            'reduce_amount' =>
            array(
              'name' => 'reduce_amount',
              'label' => 'LBL_REDUCE_AMOUNT',
            ),
            // array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            'name',
            'validate_from_date',
			'validate_to_date',
            'booking' => array(
				'type' => 'relate',
				'studio' => 'visible',
				'label' => 'LBL_BOOKING',
				'width' => '10%',
				'default' => true,
				'name' => 'booking',
			),
            'reduce_amount' =>
            array(
              'name' => 'reduce_amount',
              'label' => 'LBL_REDUCE_AMOUNT',
            ),
            'reduce_percent' =>
            array(
              'name' => 'reduce_percent',
              'label' => 'LBL_REDUCE_PERCENT',
            ),
            array(
                'name'       => 'campaign_name',
                'label'      => 'LBL_CAMPAIGN_NAME',
            ),
            array(
				'name' => 'description',
				'label' => 'LBL_DESCRIPTION',
				'displayParams' => array(
					'cols' => 32,
					'rows' => 6
				),
			),
			'status',
        ),
    ),
);
