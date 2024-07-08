<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Flight_Bookings';
$searchFields[$module_name] = array(
    'name' => array('query_type' => 'default'),
    'current_user_only' => array(
        'query_type' => 'default',
        'db_field' => array('assigned_user_id'),
        'my_items' => true,
        'vname' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool'
    ),
    'assigned_user_id' => array('query_type' => 'default'),

    //Range Search Support
    'range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    'start_range_date_entered' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'end_range_date_entered' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    'start_range_date_modified' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'end_range_date_modified' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    //Range Search Support

    'passenger_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT booking_id AS id
								FROM ec_booking_passengers
								WHERE deleted = 0 AND name LIKE ',
		//'like_char'=> '%',
		'db_field' => array('id'),
	),

	'airline_code_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT booking_id AS id
								FROM ec_booking_itineraries
								WHERE deleted = 0 AND airline_code LIKE ',
		//'like_char'=> '%',
		'db_field' => array('id'),
	),

	'ticket_class_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => 'SELECT booking_id AS id
								FROM ec_booking_itineraries
								WHERE deleted = 0 AND ticket_class LIKE ',
		//'like_char'=> '%',
		'db_field' => array('id'),
	),

	'itinerary_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => '
			SELECT booking_id AS id
			FROM ec_booking_itineraries
			WHERE deleted = 0 AND CONCAT(departure,\'-\',arrival) LIKE ',
		//'like_char'=> '%',
		'db_field' => array('id'),
	),

	'eticket_outbound_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => '
			SELECT booking_id AS id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND eticket_outbound LIKE ',
		'db_field' => array('id'),
	),

	'eticket_inbound_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => '
			SELECT booking_id AS id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND eticket_inbound LIKE ',
		'db_field' => array('id'),
	),

	'pnr_outbound_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => '
			SELECT booking_id AS id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND pnr_outbound LIKE ',
		//'like_char'=> '%',
		//'type' => 'varchar',
		'db_field' => array('id'),
	),

	'pnr_inbound_search' => array(
		'query_type' => 'default',
		'operator' => 'subquery',
		'subquery' => '
			SELECT booking_id AS id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND pnr_inbound LIKE',
		//'like_char'=> '%',
		//'type' => 'varchar',
		'db_field' => array('id'),
	),

	// 'departure_date' => array(
	// 	'query_type' => 'format',
	// 	'operator' => 'subquery',
	// 	'db_field' => array('id'),
	// 	'fld_type' => 'date',
	// 	'subquery' => '
	// 		SELECT booking_id AS id
	// 		FROM ec_booking_itineraries
	// 		WHERE deleted = 0 AND departure_date >= "{0} 00:00:00" AND departure_date <= "{1} 23:59:59"',
	// ),
);
