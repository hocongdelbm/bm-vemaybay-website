<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_HoaDonBan';
$searchFields[$module_name] = array(
    'name' => array('query_type' => 'equals'),
    'current_user_only' => array(
        'query_type' => 'default',
        'db_field' => array('assigned_user_id'),
        'my_items' => true,
        'vname' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool'
    ),
    'assigned_user_id' => array('query_type' => 'default'),
    'booking' => array(
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => '
            SELECT ct.parent_id 
            FROM ec_chitiethoadon ct
            INNER JOIN ec_flight_bookings b ON b.id = ct.booking_id
            AND b.deleted = 0 
            WHERE ct.deleted = 0 AND b.name LIKE ',
        'db_field' => array('id'),
    ),
    'ticket_number' => array(
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' => '
                SELECT ct.parent_id 
                FROM ec_chitiethoadon ct
                INNER JOIN ec_input_invoices iv ON iv.id = ct.ticket_number_id 
                WHERE ct.deleted = 0 AND iv.name LIKE ',
        'db_field' => array('id'),
    ),
    'sohoadon' => array(
        'query_type' => 'format',
        'operator' => 'subquery',
        'subquery' => 'SELECT ec_hoadonban.id FROM ec_hoadonban WHERE ec_hoadonban.deleted=0 AND ec_hoadonban.sohoadon LIKE "%{0}"',
        'db_field' => array('id',),
    ),

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
);
