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
	'STATUS' => array(
		'width' => '10',
		'label' => 'LBL_STATUS',
		'default' => true,
	),
	'REDUCE_AMOUNT' => array(
		'width' => '10',
		'label' => 'LBL_REDUCE_AMOUNT',
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
	'CAMPAIGN_NAME' => array(
		'width' => '10',
		'label' => 'LBL_CAMPAIGN_NAME',
		'default' => true,
	),
	'WEBSITE' => array(
		'width' => '10',
		'label' => 'LBL_WEBSITE',
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
