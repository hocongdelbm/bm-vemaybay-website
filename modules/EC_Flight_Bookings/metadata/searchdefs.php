<?php
global $app_list_strings, $current_user;
$module_name = 'EC_Flight_Bookings';
$booking_stt_list = $app_list_strings['booking_status_list'];
if(isManagerUser($current_user->id)) {
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

            // 'passenger_search' => array(
            //     'type' => 'varchar',
            //     'label' => 'LBL_PASSENGER_SEARCH',
            //     'width' => '10%',
            //     'default' => true,
            //     'name' => 'passenger_search',
            // ),
            // 'airline_code_search' => array(
            //     'type' => 'varchar',
            //     'label' => 'LBL_AIRLINE_CODE_SEARCH',
            //     'width' => '10%',
            //     'default' => true,
            //     'name' => 'airline_code_search',
            // ),
            
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
            // 'order_date' =>
            // array(
            //   'type' => 'datetime',
            //   'label' => 'LBL_ORDER_DATE',
            //   'width' => '10%',    
            //   'default' => true,
            //   'name' => 'order_date',
            // ),
            
            'assigned_user_name' => array(
                'link' => 'assigned_user_link',
                'type' => 'relate',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'assigned_user_name',
            ),
            'created_by_name' => array(
                'type' => 'relate',
                'link' => 'created_by_link',
                'label' => 'LBL_CREATED',
                'width' => '10%',
                'default' => true,
                'name' => 'created_by_name',
            ),
            'current_user_only' => array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool'
            ),
            
            'payment_type' => array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_PAYMENT_TYPE',
                'width' => '10%',
                'default' => true,
                'name' => 'payment_type',
            ),
            'booking_status' => array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_BOOKING_STATUS',
                'width' => '10%',
                'name' => 'booking_status',
                'options' => $booking_stt_list,
            ),
            'ticket_type' => array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_TICKET_TYPE',
                'width' => '10%',
                'name' => 'ticket_type',
            ),

            'ip_address' => array(
                'type' => 'varchar',
                'label' => 'LBL_IP_ADDRESS',
                'width' => '10%',
                'default' => true,
                'name' => 'ip_address',
            ),
            
            'email_reservation' => array(
                'type' => 'varchar',
                'label' => 'LBL_EMAIL_RESERVATION',
                'width' => '10%',
                'default' => true,
                'name' => 'email_reservation',
            ),

            'has_voucher' => array(
                'name'       => 'has_voucher',
                'vname'      => 'LBL_HAS_VOUCHER',
                'type'       => 'bool',
                'default'    => true,
            ),
            // 'favorites_only' => 
            // array(
            //   'name' => 'favorites_only', 
            //   'label' => 'LBL_FAVORITES_FILTER', 
            //   'type' => 'bool',
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
);;
