<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_LeaveAbsenceTypes';
$listViewDefs[$module_name] = array(
    'NAME' => array(
		'width' => '10', 
		'label' => 'LBL_NAME', 
		'default' => true,
        'link' => true
    ),
    'DAY_OFF' => array(
		'width' => '10', 
		'label' => 'LBL_DAY_OFF',
        'default' => true
    ), 
    'DESCRIPTION' => array(
		'width' => '15', 
		'label' => 'LBL_DESCRIPTION',
        'default' => true
    ),         
	'DATE_ENTERED' => array(
		'width' => '10', 
		'label' => 'LBL_DATE_ENTERED',
        'default' => true
    ),
);
