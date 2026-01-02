<?php
$dictionary['EC_Booking_Itineraries'] = array(
    'table' => 'ec_booking_itineraries',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'airline_code' => array(
            'name'      => 'airline_code',
            'vname'     => 'LBL_AIRLINE_CODE',
            'type'      => 'varchar',
            'len'       => 20,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'flight_number' => array(
            'name'      => 'flight_number',
            'vname'     => 'LBL_FLIGHT_NUMBER',
            'type'      => 'varchar',
            'len'       => 24,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'ticket_class' => array(
            'name'      => 'ticket_class',
            'vname'     => 'LBL_TICKET_CLASS',
            'type'      => 'varchar',
            'len'       => 50,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'departure' => array(
            'name'      => 'departure',
            'vname'     => 'LBL_DEPARTURE',
            'type'      => 'varchar',
            'len'       => 8,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'arrival' => array(
            'name'      => 'arrival',
            'vname'     => 'LBL_ARRIVAL',
            'type'      => 'varchar',
            'len'       => 8,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'departure_date' => array(
            'name'      => 'departure_date',
            'vname'     => 'LBL_DEPARTURE_DATE',
            'type'      => 'datetimecombo',
            'dbType'    => 'datetime',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            // 'enable_range_search' => true,
            // 'options' => 'date_range_search_dom',
        ),

        'arrival_date' => array(
            'name'      => 'arrival_date',
            'vname'     => 'LBL_ARRIVAL_DATE',
            'type'      => 'datetimecombo',
            'dbType'    => 'datetime',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'base_price' => array(
            'name'      => 'base_price',
            'vname'     => 'LBL_BASE_PRICE',
            'type'      => 'currency',
            'len'       => 26,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'currency_id' => array(
            'name'      => 'currency_id',
            'vname'     => 'LBL_CURRENCY',
            'type'      => 'id',
            'len'       => 36,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 0,
            'studio' => 'visible',
            'function' =>
            array(
                'name' => 'getCurrencyDropDown',
                'returns' => 'html',
            ),
        ),

        // 'is_booked' => array(
        //     'name'      => 'is_booked',
        //     'vname'     => 'LBL_IS_BOOKED',
        //     'type'      => 'bool',
        //     'default'   => 0,

        //     'importable' => 'true',
        //     'duplicate_merge' => 'disabled',
        //     'duplicate_merge_dom_value' => ' ',
        //     'audited' => 0,
        //     'reportable' => 0,
        // ),

        'booking_id' => array(
            'required'  => true,
            'name'      => 'booking_id',
            'vname'     => '',
            'type'      => 'id',
            'len'       => 36,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 0,
        ),
        'booking' => array(
            'source' => 'non-db',
            'name'   => 'booking',
            'vname'  => 'LBL_BOOKING',
            'type'   => 'relate',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'id_name' => 'booking_id',
            'ext2' => 'EC_Flight_Bookings',
            'module' => 'EC_Flight_Bookings',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
        ),

        'direction' => array(
            'name'      => 'direction',
            'vname'     => 'LBL_DIRECTION',
            'type'      => 'enum',
            'dbtype'    => 'char',
            'options'   => 'bk_direction_list',
            'len'       => 1,
            'default'   => '0',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'studio' => 'visible',
            'dependency' => false,
        ),

        'time_limit' => array(
            'name'      => 'time_limit',
            'vname'     => 'LBL_TIME_LIMIT',
            'type'      => 'datetimecombo',
            'dbType'    => 'datetime',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        // 'code_sign_in' => array(
        //     'name'      => 'code_sign_in',
        //     'vname'     => 'LBL_CODE_SIGN_IN',
        //     'type'      => 'varchar',
        //     'len'       => 50,
        //     'default'   => '',

        //     'importable' => 'true',
        //     'duplicate_merge' => 'disabled',
        //     'duplicate_merge_dom_value' => ' ',
        //     'audited' => 1,
        //     'reportable' => 0,
        // ),

        // Số lần đổi hành trình.
        'sabre_logs' => array(
            'name'      => 'sabre_logs',
            'vname'     => 'LBL_SABRE_LOGS',
            'type'      => 'char',
            'len'       => 2,
            'default'   => '0',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'studio' => 'visible',
        ),

        'is_layover' => array(
            'name'      => 'is_layover',
            'vname'     => 'LBL_IS_LAYOVER',
            'type'      => 'bool',
            'default'   => 0,

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 0,
            'reportable' => 0,
        ),

        'duration' => array(
            'name'      => 'duration',
            'vname'     => 'LBL_FLIGHT_DURATION',
            'type'      => 'varchar',
            'len'       => 15,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        ),

        'stops' => array(
            'name'      => 'stops',
            'vname'     => 'LBL_STOPS',
            'type'      => 'int',
            'default'   => 0,

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'disable_num_format' => '',
        ),

        // Phân biệt dòng chi tiết được booker đổi ngày h bay
        'add_type' => array(
            'required'  => false,
            'name'      => 'add_type',
            'vname'     => 'LBL_ADD_TYPE',
            'type'      => 'int',
            'default'   => 0,
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
            'disable_num_format' => '',
        ),

        // Phân biệt các dòng chi tiết mới được thay đổi thông tin
        'parent_detail_id' => array(
            'name'      => 'parent_detail_id',
            'vname'     => 'LBL_PARENT_DETAIL_ID',
            'type'      => 'char',
            'len'       => 36,
            'default'   => '',

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 0,
            'reportable' => 0,
        ),

        // Phân biệt thứ tự in ra hành trình khi là chuyến transit
        'transit_order' => array(
            'name'      => 'transit_order',
            'vname'     => 'LBL_TRANSIT_ORDER',
            'type'      => 'int',
            'default'   => 0,

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'studio' => 'visible',
        ),

        // Kiểm tra nhắc nhở khách hàng trước ngày bay
        'is_remind' => array(
            'name'      => 'is_remind',
            'vname'     => 'LBL_IS_REMIND',
            'type'      => 'bool',
            'len'       => 1,
            'default'   => 0,

            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => ' ',
            'audited' => 1,
            'reportable' => 0,
        )

    ),
    'indices' => array(
        array('name' => 'idx_iti_name', 'type' => 'index', 'fields' => array('name')),
        array('name' => 'idx_iti_booking', 'type' => 'index', 'fields' => array('booking_id')),
        array('name' => 'idx_iti_aircode', 'type' => 'index', 'fields' => array('airline_code')),
        array('name' => 'idx_iti_flgnum', 'type' => 'index', 'fields' => array('flight_number')),
        array('name' => 'idx_iti_departure', 'type' => 'index', 'fields' => array('departure')),
        array('name' => 'idx_iti_arrival', 'type' => 'index', 'fields' => array('arrival')),
        // array('name' => 'idx_iti_del', 'type' => 'index', 'fields' => array('deleted')),
        // array('name' => 'idx_iti_direct', 'type' => 'index', 'fields' => array('direction')),
        // array('name' => 'idx_iti_booked', 'type' => 'index', 'fields' => array('is_booked')),
        // array('name' => 'idx_iti_layover', 'type' => 'index', 'fields' => array('is_layover')),
        array(
            'name' => 'idx_itinerary_booking_date', 'type' => 'index', 'fields' => array('booking_id', 'departure_date', 'deleted')
        ),
    ),
    'relationships' => array(),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Booking_Itineraries', 'EC_Booking_Itineraries', array('basic', 'assignable', 'security_groups'));
