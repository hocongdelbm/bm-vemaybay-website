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
	'CAMPAIGN_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_CAMPAIGN_NAME', 
		'default' => true,
    ),
    'BOOKING' => array(
		'name' => 'booking',
		'width' => '10', 
		'label' => 'LBL_BOOKING', 
		'default' => true,
		'link' => true
    ),
	'DATE_ENTERED' => array(
		'width' => '10', 
		'label' => 'LBL_DATE_ENTERED',
        'default' => true
    ),
);
