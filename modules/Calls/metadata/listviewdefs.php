<?php
if (!defined('sugarEntry') || !sugarEntry) {
	die('Not A Valid Entry Point');
}

$listViewDefs['Calls'] = array(
	// 'SET_COMPLETE' => array(
	//   'width' => '1%',
	//   'label' => 'LBL_LIST_CLOSE',
	//   'link' => true,
	//   'sortable' => false,
	//   'default' => true,
	//   'related_fields' => array(
	//     'status',
	//   ),
	// ),

	'NAME' => array(
		'width' => '40%',
		'label' => 'LBL_LIST_SUBJECT',
		'link' => true,
		'default' => true,
	),

	'PARENT_NAME' => array(
		'width' => '20%',
		'label' => 'LBL_LIST_RELATED_TO',
		'dynamic_module' => 'PARENT_TYPE',
		'id' => 'PARENT_ID',
		'link' => true,
		'default' => true,
		'sortable' => false,
		'ACLTag' => 'PARENT',
		'related_fields' => array(
			'parent_id',
			'parent_type',
		),
	),

	'CALL_TYPE' => array(
		'width' => '20%',
		'label' => 'LBL_CALL_TYPE',
		'default' => false,
	),

	'CALL_FROM' => array(
		'width' => '10%',
		'label' => 'LBL_LIST_CALL_FROM',
		'default' => true,
	),

	'CALL_TO' => array(
		'width' => '10%',
		'label' => 'LBL_LIST_CALL_TO',
		'default' => true,
	),

	'DESCRIPTION' => array(
		'width' => '10%',
		'label' => 'LBL_DESCRIPTION',
		'default' => true,
	),

	'DIRECTION' => array(
		'width' => '10%',
		'label' => 'LBL_DIRECTION',
		'default' => true,
	),

	'DATE_START' => array(
		'width' => '15%',
		'label' => 'LBL_LIST_DATE',
		'link' => false,
		'default' => true,
		'related_fields' => array(
			'time_start',
		),
	),
	'CALL_TALK' => 
	array (
		'width' => '10%',
		'label' => 'LBL_CALL_TALK',
		'default' => true,
		'type' => 'text',
	),
	'CALL_MOS' => 
	array (
		'width' => '10%',
		'label' => 'LBL_LIST_CALL_MOS',
		'default' => true,
		'type' => 'FLOAT',
		// 'align' => 'center',
	),

	'STATUS' => array(
		'width' => '10%',
		'label' => 'LBL_STATUS',
		'link' => false,
		'default' => true,
	),

	// 'CONTACT_NAME' => array(
	// 	'width' => '20%',
	// 	'label' => 'LBL_LIST_CONTACT',
	// 	'link' => true,
	// 	'id' => 'CONTACT_ID',
	// 	'module' => 'Contacts',
	// 	'default' => true,
	// 	'ACLTag' => 'CONTACT',
	// ),

	'BOOKING' =>
	array(
	    'type' => 'relate',
	    'studio' => 'visible',
	    'label' => 'LBL_BOOKING',
	    'width' => '8%',
		'link' => true,
	    'default' => true,
	),
	'ASSIGNED_USER_NAME' => array(
		'width' => '2%',
		'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
		'module' => 'Employees',
		'id' => 'ASSIGNED_USER_ID',
		'default' => true,
	),
);
