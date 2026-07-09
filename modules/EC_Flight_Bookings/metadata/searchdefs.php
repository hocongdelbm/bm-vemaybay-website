<?php
global $app_list_strings, $current_user;
$module_name = 'EC_Flight_Bookings';
$booking_stt_list = $app_list_strings['booking_status_list'];
if (isManagerUser()) {
    $booking_stt_list[100] = 'Thay đổi giao cho';
}
$searchdefs[$module_name] = array(
    'layout' => array(
        'basic_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'date_entered' => array(
                'type' => 'datetime',
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
        ),
        'advanced_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'contact_name' => array(
                'type' => 'varchar',
                'label' => 'LBL_CONTACT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'contact_name',
            ),
            'phone' => array(
                'type' => 'phone',
                'label' => 'LBL_PHONE',
                'width' => '10%',
                'default' => true,
                'name' => 'phone',
            ),

            'email' => array(
                'type' => 'varchar',
                'label' => 'LBL_EMAIL',
                'width' => '10%',
                'default' => true,
                'name' => 'email',
            ),
            'total_amount' => array(
                'type' => 'currency',
                'label' => 'LBL_TOTAL_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'total_amount',
            ),
            'itinerary_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_ITINERARY_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'itinerary_search',
            ),

            'passenger_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_PASSENGER_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'passenger_search',
            ),
            'pnr_outbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_PNR_OUTBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'pnr_outbound_search',
            ),
            'pnr_inbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_PNR_INBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'pnr_inbound_search',
            ),

            'airline_code_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_AIRLINE_CODE_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'airline_code_search',
            ),
            'eticket_outbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_ETICKET_OUTBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'eticket_outbound_search',
            ),
            'eticket_inbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_ETICKET_INBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'eticket_inbound_search',
            ),

            'email_reservation' => array(
                'type' => 'varchar',
                'label' => 'LBL_EMAIL_RESERVATION',
                'width' => '10%',
                'default' => true,
                'name' => 'email_reservation',
            ),
            'eluggage_outbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_ELUGGAGE_OUTBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'eluggage_outbound_search',
            ),
            'eluggage_inbound_search' => array(
                'type' => 'varchar',
                'label' => 'LBL_ELUGGAGE_INBOUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'eluggage_inbound_search',
            ),

            'date_entered' => array(
                'type' => 'datetime',
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
            'date_ticket_issue' => array(
                'type' => 'date',
                'label' => 'LBL_DATE_TICKET_ISSUE',
                'width' => '10%',
                'default' => true,
                'name' => 'date_ticket_issue',
            ),
            'departure_date' => array(
                'type' => 'datetime',
                'label' => 'LBL_DEPARTURE_DATE',
                'width' => '10%',
                'default' => true,
                'name' => 'departure_date',
                'enable_range_search' => true,
                'options' => 'date_range_search_dom',
            ),
            // 'assigned_user_name' => array(
            //     'link' => 'assigned_user_link',
            //     'type' => 'relate',
            //     'label' => 'LBL_ASSIGNED_TO_NAME',
            //     'width' => '10%',
            //     'default' => true,
            //     'name' => 'assigned_user_name',
            // ),
            'assigned_user_id' =>
            array(
                'name' => 'assigned_user_id',
                'type' => 'enum',
                'label' => 'LBL_ASSIGNED_TO',
                'function' =>
                array(
                    'name' => 'UsersHelper::get_user_array_search',
                    'params' =>
                    array(
                        0 => false,
                    ),
                ),
                'default' => true,
                'width' => '10%',
            ),
            'created_by' =>
            array(
                'name' => 'created_by',
                'type' => 'enum',
                'label' => 'LBL_CREATED',
                'function' =>
                array(
                    'name' => 'UsersHelper::get_user_array_search',
                    'params' =>
                    array(
                        0 => false,
                    ),
                ),
                'default' => true,
                'width' => '10%',
            ),
            // 'created_by_name' => array(
            //     'type' => 'relate',
            //     'link' => 'created_by_link',
            //     'label' => 'LBL_CREATED',
            //     'width' => '10%',
            //     'default' => true,
            //     'name' => 'created_by_name',
            // ),

            'payment_type' => array(
                'type' => 'enum',
                'label' => 'LBL_PAYMENT_TYPE',
                'width' => '10%',
                'default' => true,
                'name' => 'payment_type',
            ),
            'booking_status' => array(
                'type' => 'enum',
                'default' => true,
                'label' => 'LBL_BOOKING_STATUS',
                'width' => '10%',
                'name' => 'booking_status',
                'options' => $booking_stt_list,
            ),
            'ticket_type' => array(
                'type' => 'enum',
                'default' => true,
                'label' => 'LBL_TICKET_TYPE',
                'width' => '10%',
                'name' => 'ticket_type',
            ),
            'customer_source' => array(
                'type' => 'enum',
                'default' => true,
                'label' => 'LBL_CUSTOMER_SOURCE',
                'width' => '10%',
                'name' => 'customer_source',
            ),

            'ip_address' => array(
                'type' => 'varchar',
                'label' => 'LBL_IP_ADDRESS',
                'width' => '10%',
                'default' => true,
                'name' => 'ip_address',
            ),
            'is_telesale' => array(
                'type' => 'bool',
                'label' => 'LBL_IS_TELESALE',
                'width' => '10%',
                'default' => true,
                'name' => 'is_telesale',
            ),
            'is_ctv' => array(
                'type' => 'bool',
                'label' => 'LBL_IS_CTV',
                'width' => '10%',
                'default' => true,
                'name' => 'is_ctv',
            ),
            'is_prior' => array(
                'type' => 'bool',
                'label' => 'LBL_IS_PRIOR',
                'width' => '10%',
                'default' => true,
                'name' => 'is_prior',
            ),
            'is_reference' => array(
                'type' => 'bool',
                'label' => 'LBL_IS_REFERENCE',
                'width' => '10%',
                'default' => true,
                'name' => 'is_reference',
            ),
            'is_refund_search' => array(
                'type' => 'bool',
                'label' => 'LBL_IS_REFUND_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'is_refund_search',
            ),
            'current_user_only' => array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool'
            ),
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
);;
