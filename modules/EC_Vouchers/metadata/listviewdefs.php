<?php

if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

$module_name = 'EC_Vouchers';
$listViewDefs[$module_name] = array(
	'NAME' => array(
		'width' => '10',
		'label' => 'LBL_NAME',
		'default' => true,
<<<<<<< HEAD
        'link' => true
    ),
	'REDUCE_AMOUNT' => array(
		'width' => '10', 
		'label' => 'LBL_AMOUNT', 
		'default' => true,
		'related_fields' => array('reduce_percent')
	),
    'STATUS' => array(
		'width' => '10', 
		'label' => 'LBL_STATUS',
		'default' => true,
    ), 
	'VALIDATE_FROM_DATE' => array(
		'width' => '20', 
		'label' => 'LBL_DURATION', 
		'default' => true,
		'related_fields' => array('validate_to_date')
    ),
=======
		'link' => true
	),
	'STATUS' => array(
		'width' => '10',
		'label' => 'LBL_STATUS',
		'default' => true,
	),
	'REDUCE_AMOUNT' => array(
		'name' => 'reduce_amount',
		'label' => 'LBL_DISCOUNT',
		'type' => 'varchar',
		'width' => '10',
		'default' => true,
		'related_fields' => array('reduce_percent'),
	),
	'END_TIME' => array(
		'name' => 'end_time',
		'width' => '20',
		'label' => 'LBL_DURATION',
		'default' => true,
		'related_fields' => array('start_time', 'end_time'),
	),
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
	'CAMPAIGN_NAME' => array(
		'width' => '10',
		'label' => 'LBL_CAMPAIGN_NAME',
		'default' => true,
<<<<<<< HEAD
    ),
    'BOOKING' => array(
		'name' => 'booking',
		'width' => '10', 
		'label' => 'LBL_BOOKING', 
		'default' => true,
		'link' => true
    ),
=======
	),
	'WEBSITE' => array(
		'width' => '10',
		'label' => 'LBL_WEBSITE',
		'default' => true,
	),
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
	'DATE_ENTERED' => array(
		'width' => '10',
		'label' => 'LBL_DATE_ENTERED',
<<<<<<< HEAD
        'default' => true
    ),
=======
		'default' => true
	),
>>>>>>> 8201700091c3488d9a9fb900f7efbbfebbcecfff
);
