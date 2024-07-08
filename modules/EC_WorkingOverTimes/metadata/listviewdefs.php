<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_WorkingOverTimes';
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
        'default' => true
	),    
	'DESCRIPTION' => array(
		'width' => '10', 
		'label' => 'LBL_DESCRIPTION',
        'default' => true
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
