<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Input_Invoices';
$listViewDefs[$module_name] = array(
    'NAME' => array(
		'width' => '30', 
		'label' => 'LBL_NAME', 
		'default' => true,
        'link' => true
	),
	'TICKET_CODE' => array(
		'width' => '10',
		'label' => 'LBL_TICKET_CODE',
		'default' => true,
		'link' => true
	),
	'TICKET_TYPE' => array(
		'width' => '10',
		'label' => 'LBL_TICKET_TYPE',
		'default' => true,
	),
	'QTY' => array(
		'width' => '15',
		'label' => 'LBL_INIT_QTY',
		'default' => true,
	),
	'LEFT_QTY' => array(
		'width' => '15',
		'label' => 'LBL_LEFT_QTY',
		'default' => true,
	),       
	'TOTAL' => array(
		'width' => '40',
		'label' => 'LBL_TOTAL',
		'default' => true,
	)
);
