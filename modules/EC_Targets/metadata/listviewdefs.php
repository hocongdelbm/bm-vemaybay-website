<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Targets';
$listViewDefs[$module_name] = array(
	'NAME' => array(
		'width' => '15', 
		'label' => 'LBL_NAME', 
		'default' => true,
        'link' => true
    ), 
    'STATUS' => array(
		'width' => '10',
		'label' => 'LBL_STATUS',
        'default' => true
	),
	'YEAR' => array(
		'width' => '10',
		'label' => 'LBL_YEAR',
        'default' => true,
        'align' => 'center',
	), 
	'TARGET_YEAR' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_YEAR',
        'default' => true,
        'currency_format' => 1,
        'align' => 'right',
	),
	'TARGET_QUARTER1' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_QUARTER1',
        'default' => true,
        'currency_format' => 1,
        'align' => 'right',
	),
	'TARGET_QUARTER2' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_QUARTER2',
        'default' => true,
        'currency_format' => 1,
        'align' => 'right',
	),
	'TARGET_QUARTER3' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_QUARTER3',
        'default' => true,
        'currency_format' => 1,
        'align' => 'right',
	),
	'TARGET_QUARTER4' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_QUARTER4',
        'default' => true,
        'currency_format' => 1,
        'align' => 'right',
	),       
	'TARGET_TYPE' => array(
		'width' => '10',
		'label' => 'LBL_TARGET_TYPE',
        'default' => true,
        'align' => 'center',
	),
	'ASSIGNED_USER_NAME' => array(
		'width' => '9', 
		'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true
    ),
	'DATE_ENTERED' => array(
		'width' => '10',
		'label' => 'LBL_DATE_ENTERED',
        'default' => true
	),
);