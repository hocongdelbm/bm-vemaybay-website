<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_LeaveAbsences';
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
        'align' => 'center',
    ),
    'ABSENCE_DAYS' => array(
        'width' => '10', 
        'label' => 'LBL_ABSENCE_DAYS',
        'default' => true,
        'align' => 'center',
    ),  
    'FROM_DATE' => array(
        'width' => '10', 
        'label' => 'LBL_FROM_DATE',
        'default' => true,
        'align' => 'center',
    ), 
    'TO_DATE' => array(
        'width' => '10', 
        'label' => 'LBL_TO_DATE',
        'default' => true,
        'align' => 'center',
    ), 
    'REASON' => array(
        'width' => '25', 
        'label' => 'LBL_REASON',
        'default' => true,
    ), 
    'ABSENCE_TYPE' => array(
        'width' => '10', 
        'label' => 'LBL_ABSENCE_TYPE',
        'default' => true,
    ),     
	'ASSIGNED_USER_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true
    ),
    'DATE_ENTERED' => array(
    	'width' => '10', 
		'label' => 'LBL_DATE_ENTERED',
        'default' => true
    ),

);
