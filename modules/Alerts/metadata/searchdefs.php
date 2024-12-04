<?php
$searchdefs['Alerts'] = array(
	'layout' => array(
		'basic_search' => array(
			'name' => array(
				'name' => 'name',
				'default' => true,
				'width' => '10%',
			),
		),
		'advanced_search' => array(
			'name' => array(
				'name' => 'name',
				'default' => true,
				'width' => '10%',
			),
			'is_read' => array(
				'name' => 'is_read',
				'label' => 'LBL_IS_READ',
				'default' => true,
				'width' => '10%',
			),
			'current_user_only' => array(
				'name' => 'current_user_only',
				'label' => 'LBL_CURRENT_USER_FILTER',
				'type' => 'bool',
				'default' => true,
				'width' => '10%',
			),
			'priority' => array(
				'name' => 'priority',
				'label' => 'LBL_PRIORITY',
				'default' => true,
				'width' => '10%',
			),
			'type' => array(
				'name' => 'type',
				'label' => 'LBL_TYPE',
				'default' => true,
				'width' => '10%',
			),
			
			// 'assigned_user_id' => array(
			//   	'name' => 'assigned_user_id',
			//   	'type' => 'enum',
			//   	'label' => 'LBL_ASSIGNED_TO',
			// 	'function' =>
			// 	array(
			// 		'name' => 'get_user_array',
			// 		'params' =>
			// 		array(
			// 			0 => false,
			// 		),
			// 	),
			// 	'default' => true,
			// 	'width' => '10%',
			// ),
		),
	),
	'templateMeta' => array(
		'maxColumns' => '3',
		'maxColumnsBasic' => '4',
		'widths' => array(
			'label' => '10',
			'field' => '30',
		),
	),
);
