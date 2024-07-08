<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Flight_Bookings';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '10%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'CONTACT_NAME' => array(
        'label' => 'LBL_CONTACT_NAME',
        'width' => '12%',
        'default' => true,
    ),
    'PHONE' => array(
        'label' => 'LBL_PHONE',
        'width' => '10%',
        'default' => true,
    ),
    'TOTAL_QTY' => array(
        'label' => 'LBL_TOTAL_QTY',
        'width' => '10%',
        'default' => true,
    ),
    'EMAIL' => array(
        'label' => 'LBL_EMAIL',
        'width' => '10%',
        'default' => true,
    ),
    'DESCRIPTION' => array(
        'label' => 'LBL_DESCRIPTION',
        'width' => '35%',
        'default' => true,
    ),
    'TOTAL_AMOUNT' => array(
        'label' => 'LBL_TOTAL_AMOUNT',
        'width' => '10%',
        'default' => true,
    ),
    'BOOKING_STATUS' => array(
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_BOOKING_STATUS',
        'width' => '8%',
    ),
    'RECALL_C' => 
	array (
		'width' => '10%',
		'label' => 'LBL_RECALL_C',
		'default' => true,
	),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ),
    'IP_ADDRESS' => array(
        'width' => '10%',
        'label' => 'LBL_IP_ADDRESS',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'label' => 'LBL_DATE_ENTERED',
        'width' => '15%',
        'default' => true,
    )
);
