<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Customer';
$listViewDefs[$module_name] = array(
	'NAME' => array(
		'width' => '10%',
		'label' => 'LBL_NAME',
		'default' => true,
		'link' => true,
	 ),
    'TYPE' => array(
		'width' => '15%',
		'label' => 'LBL_TYPE',
		'default' => true,
	),
	'PHONE' => array(
		'label' => 'LBL_PHONE',
		'width' => '15%',
		'default' => true,
	 ),
	 'EMAIL' => array(
		'label' => 'LBL_EMAIL',
		'width' => '15%',
		'default' => true,
	 ),
	'DATE_ENTERED' => array(
		'label' => 'LBL_DATE_ENTERED',
		'width' => '15%',
		'default' => true,
	 )
);
