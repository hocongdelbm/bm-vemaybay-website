<?php
$searchdefs['Calls'] = array(
	'layout' => array(
		'basic_search' => array(
			'name' => array(
				'name' => 'name',
				'default' => true,
				'width' => '10%',
			),
			'call_from' => array(
				'name' => 'call_from',
				'label' => 'LBL_CALL_FROM',
				'default' => true,
				'width' => '10%',
			),
			'call_to' => array(
				'name' => 'call_to',
				'label' => 'LBL_CALL_TO',
				'default' => true,
				'width' => '10%',
			),
			'booking' => array(
				'type' => 'relate',
				'studio' => 'visible',
				'label' => 'LBL_BOOKING',
				'width' => '10%',
				'default' => true,
				'name' => 'booking',
			),
			'date_start' => array(
				'type' => 'datetime',
				'label' => 'LBL_DATE',
				'width' => '10%',
				'default' => true,
				'name' => 'date_start',
			),
			'date_end' => array(
				'type' => 'datetime',
				'label' => 'LBL_DATE_END',
				'width' => '10%',
				'default' => true,
				'name' => 'date_end',
			),
			'call_type' => array(
				'name' => 'call_type',
				'label' => 'LBL_CALL_TYPE',
				'default' => true,
				'width' => '10%',
			),
			'status' => array(
				'name' => 'status',
				'default' => true,
				'width' => '10%',
			),
			'direction' => array(
				'name' => 'direction',
				'label' => 'LBL_DIRECTION',
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
			'call_from' => array(
				'name' => 'call_from',
				'label' => 'LBL_CALL_FROM',
				'default' => true,
				'width' => '10%',
			),
			'call_to' => array(
				'name' => 'call_to',
				'label' => 'LBL_CALL_TO',
				'default' => true,
				'width' => '10%',
			),
			'booking' => array(
				'type' => 'relate',
				'studio' => 'visible',
				'label' => 'LBL_BOOKING',
				'width' => '10%',
				'default' => true,
				'name' => 'booking',
			),
			'date_start' => array(
				'type' => 'datetime',
				'label' => 'LBL_DATE',
				'width' => '10%',
				'default' => true,
				'name' => 'date_start',
			),
			'date_end' => array(
				'type' => 'datetime',
				'label' => 'LBL_DATE_END',
				'width' => '10%',
				'default' => true,
				'name' => 'date_end',
			),
			'call_id' => array(
				'name' => 'call_id',
				'label' => 'LBL_CALL_ID',
				'type' => 'varchar',
				'default' => true,
				'width' => '10%',
			),
			'parent_name' => array(
				'type' => 'parent',
				'label' => 'LBL_LIST_RELATED_TO',
				'width' => '10%',
				'default' => true,
				'name' => 'parent_name',
			),
			'call_sources' => array(
				'name'       => 'call_sources',
				'vname'      => 'LBL_CALL_SOURCES',
				'width' => '10%',
				'default' => true,
			),
			'call_type' => array(
				'name' => 'call_type',
				'label' => 'LBL_CALL_TYPE',
				'default' => true,
				'width' => '10%',
			),
			'status' => array(
				'name' => 'status',
				'default' => true,
				'width' => '10%',
			),
			'direction' => array(
				'name' => 'direction',
				'label' => 'LBL_DIRECTION',
				'default' => true,
				'width' => '10%',
			),
			'other_caller' => array(
				'name' => 'other_caller',
				'label' => 'LBL_OTHER_CALLER',
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
