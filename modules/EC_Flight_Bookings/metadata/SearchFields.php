<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Flight_Bookings';
$searchFields[$module_name] = array(
    'name' => [
        'query_type' => 'default',
        // 'operator' => '=',
    ],
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
    // 'passengers_only' => array(
    //   'query_type' => 'format',
    //   'operator' => 'subquery',
    //   'checked_only' => true,
    //   'subquery' => "SELECT ec_flight_bookings.id FROM ec_flight_bookings WHERE ec_flight_bookings.deleted = 0 and ec_flight_bookings.booking_status = 4 AND ec_flight_bookings.description = 'Lý Thông phá hoại'",
    //   'db_field'=>array('id'),
    // ),
    // 'favorites_only' => array(
    //   'query_type'=>'format',
    //   'operator' => 'subquery',
    //   'checked_only' => true,
    //   'subquery' => "SELECT favorites.parent_id FROM favorites
    //                     WHERE favorites.deleted = 0
    //                         and favorites.parent_type = 'Calls'
    //                         and favorites.assigned_user_id = '{1}'",
    //   'db_field'=>array('id')),
    // SLOW QUERY
    // 'phone' => array(
    //     'query_type' => 'format',
    //     'operator' => 'subquery',
    //     'subquery' => 'SELECT ec_flight_bookings.id FROM ec_flight_bookings WHERE ec_flight_bookings.deleted=0 AND ec_flight_bookings.phone LIKE "%{0}"',
    //     'db_field' => array(
    //         'id',
    //     ),
    // ),
    // 'contact_name' => array(
    //     'query_type' => 'format',
    //     'operator' => 'subquery',
    //     'subquery' => 'SELECT ec_flight_bookings.id FROM ec_flight_bookings WHERE ec_flight_bookings.deleted=0 AND ec_flight_bookings.contact_name LIKE "%{0}"',
    //     'db_field' => array(
    //         'id',
    //     ),
    // ),
    'passenger_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id FROM ec_booking_passengers WHERE deleted = 0 AND name LIKE',
        'db_field' => ['id'],
    ],
    'airline_code_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id FROM ec_booking_itineraries WHERE deleted = 0 AND airline_code LIKE',
        'db_field' => ['id'],
    ],
    'ticket_class_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id FROM ec_booking_itineraries WHERE deleted = 0 AND ticket_class LIKE',
        'db_field' => ['id'],
    ],
    'itinerary_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_itineraries
			WHERE deleted = 0 AND CONCAT(departure,\'-\',arrival) LIKE',
        'db_field' => ['id'],
    ],
    'eticket_outbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
            FROM ec_booking_passengers
            WHERE deleted = 0 AND eticket_outbound LIKE',
        'db_field' => ['id'],
    ],
    'eticket_inbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND eticket_inbound LIKE',
        'db_field' => ['id'],
    ],
    'eluggage_outbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_passengers
			WHERE eluggage_outbound LIKE',
        'db_field' => ['id'],
    ],
    'eluggage_inbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_passengers
			WHERE eluggage_inbound LIKE',
        'db_field' => ['id'],
    ],
    'pnr_outbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND pnr_outbound LIKE',
        'db_field' => ['id'],
    ],
    'pnr_inbound_search' => [
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_passengers
			WHERE deleted = 0 AND pnr_inbound LIKE',
        'db_field' => ['id'],
    ],
    // 'departure_date' => array(
    //   'query_type' => 'format',
    //   'operator' => 'subquery',
    //   'db_field' => array('id'),
    //   'fld_type' => 'date',
    //   'subquery' => 'SELECT booking_id
    // 		FROM ec_booking_itineraries
    // 		WHERE deleted = 0 AND departure_date >= "{0} 00:00:00" AND departure_date <= "{1} 23:59:59"',
    // ),
    'range_date_ticket_issue' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true,
    ),
    'start_range_date_ticket_issue' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true,
    ),
    'end_range_date_ticket_issue' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true,
    ),

    // RANGE DEPARTURE DATE
    'range_departure_date' =>
    array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'operator' => 'subquery',
        'is_date_field' => true,
    ),
    'start_range_departure_date' =>
    array(
        'enable_range_search' => true,
        'is_date_field' => true,
        'query_type' => 'format',
        'operator' => 'subquery',
        'db_field' =>
        array('id'),
        'fld_type' => 'date',
        'subquery' => 'SELECT booking_id
			FROM ec_booking_itineraries
			WHERE deleted = 0 AND departure_date >= "{0} 00:00:00" AND departure_date <= "{1} 23:59:59"',
    ),
    'end_range_departure_date' =>
    array(
        'query_type' => 'default',
        'operator' => 'subquery',
        'enable_range_search' => true,
        'is_date_field' => true,
    ),
    'is_refund_search' => [
        'query_type' => 'format',
        'operator' => 'subquery',
        'subquery' => 'SELECT id FROM ec_flight_bookings WHERE deleted = 0 AND IF("{0}" = "1", id IN (SELECT booking_id FROM ec_hoanve WHERE deleted = 0), id NOT IN (SELECT booking_id FROM ec_hoanve WHERE deleted = 0))',
        'db_field' => ['id'],
    ],
    'booking_status' => [
        'query_type' => 'format',
        'operator' => 'subquery',
        'subquery' =>
        '
            SELECT id
            FROM ec_flight_bookings 
            WHERE deleted = 0
            AND (
                IF(
                    FIND_IN_SET("100", "{0}") > 0,
                    id IN (
                        SELECT parent_id
                        FROM ec_flight_bookings_audit a
                        WHERE field_name = "assigned_user_id" 
                        AND date_created > IFNULL((
                            SELECT date_created
                            FROM ec_flight_bookings_audit 
                            WHERE parent_id = a.parent_id
                            AND field_name = "booking_status"
                            AND after_value_string = "8"
                            ORDER BY date_created
                            LIMIT 1
                        ), "' . date('Y-m-d H:i:s', strtotime('+1 hour')) . '")
                    ),
                    booking_status IN ({0})
                )
            )
        ',
        'db_field' => array('id'),
    ],
    // 'booking_status' =>
    // array(
    //     'query_type' => 'format',
    //     'operator' => 'dividequery',
    //     'subquery' => array(
    //         '
    //             SELECT GROUP_CONCAT(id SEPARATOR ",") AS select_id
    //             FROM ec_flight_bookings 
    //             WHERE deleted = 0
    //             AND (
    //                 CASE WHEN {0} = 100 THEN
    //                 (
    //                     id IN (
    //                     SELECT parent_id
    //                     FROM ec_flight_bookings_audit a
    //                     WHERE field_name = "assigned_user_id" 
    //                     AND date_created > IFNULL((
    //                         SELECT date_created
    //                         FROM ec_flight_bookings_audit 
    //                         WHERE parent_id = a.parent_id
    //                         AND field_name = "booking_status"
    //                         AND after_value_string = "8"
    //                         ORDER BY date_created
    //                         LIMIT 1
    //                     ), "' . date('Y-m-d H:i:s', strtotime('+1 hour')) . '"))
    //                 ) 
    //                 ELSE booking_status IN ({0}) END
    //             )
    //         ',
    //         '
    //             SELECT id
    //             FROM ec_flight_bookings 
    //             WHERE id IN (\'{0}\')
    //         '
    //     ),
    //     'db_field' => array('id'),
    // ),
);
