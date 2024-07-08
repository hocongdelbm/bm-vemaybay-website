<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Vouchers';
$listViewDefs[$module_name] = array(
	'ORDER_BY_NO' => array(
		'width' => '10', 
		'label' => 'LBL_ORDER_BY_NO',
        	'default' => true
    ),
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
    ),     
	'VALIDATE_FROM_DATE' => array(
		'width' => '20', 
		'label' => 'LBL_DURATION', 
		'default' => true,
    ),
	'CAMPAIGN_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_CAMPAIGN_NAME', 
		'default' => true,
    ),
    'BOOKING_RECEIVE_ID' => array(
		'name' => 'booking_receive_id',
		'width' => '10', 
		'label' => 'LBL_BOOKING', 
		'default' => true,
    ),
	'DATE_ENTERED' => array(
		'width' => '10', 
		'label' => 'LBL_DATE_ENTERED',
        	'default' => true
    ),
);
