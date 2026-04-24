<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['bookings_vouchers'] = array(
    'table' => 'bookings_vouchers',
    'fields' => array(
        array('name' => 'id', 'type' => 'varchar', 'len' => 36),
        array('name' => 'booking_id', 'type' => 'varchar', 'len' => 36),
        array('name' => 'voucher_id', 'type' => 'varchar', 'len' => 36),
        array('name' => 'discount_amount', 'type' => 'int', 'default' => 0),
        array('name' => 'date_modified', 'type' => 'datetime'),
        array('name' => 'deleted', 'type' => 'bool', 'len' => 1, 'required' => false, 'default' => 0)
    ),
    'indices' => array(
        array('name' => 'bookings_vouchers_pk', 'type' => 'primary', 'fields' => array('id')),
        array('name' => 'idx_bookings_vouchers_uni', 'type' => 'unique', 'fields' => array('booking_id', 'voucher_id', 'deleted')),
    ),
    'relationships' => array(
        'bookings_vouchers' => array(
            'lhs_module' => 'EC_Flight_Bookings',
            'lhs_table' => 'ec_flight_bookings',
            'lhs_key' => 'id',
            'rhs_module' => 'EC_Vouchers',
            'rhs_table' => 'ec_vouchers',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'bookings_vouchers',
            'join_key_lhs' => 'booking_id',
            'join_key_rhs' => 'voucher_id'
        )
    )
);
