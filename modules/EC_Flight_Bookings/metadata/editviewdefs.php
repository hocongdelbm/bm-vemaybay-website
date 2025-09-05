<?php

$module_name = 'EC_Flight_Bookings';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/mcautocomplete.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/allowNumberOnly.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/formatNumber.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/fromPopupReturn.js',
            ),
            array(
                'file' => 'themes/SuiteP/js/reset.js',
            ),
            // array(
            //     'file' => 'modules/EC_Flight_Bookings/js/view.edit.js',
            // ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'customCode' => '{$NAME_BOOKING}',
                ),
                array(),
            ),
            array(
                array(
                    'name' => 'flight_type',
                    'studio' => 'visible',
                    'label' => 'LBL_FLIGHT_TYPE',
                ),
                array(
                    'name' => 'ticket_type',
                    'studio' => 'visible',
                    'label' => 'LBL_TICKET_TYPE',
                ),
            ),

            array(
                array(
                    'name' => 'holding_status',
                    'studio' => 'visible',
                    'label' => 'LBL_HOLDING_STATUS',
                ),
                array(
                    'name' => 'payment_type',
                    'studio' => 'visible',
                    'label' => 'LBL_PAYMENT_TYPE',
                ),
            
            ),

            array(
                array(
                    'name' => 'airline',
                    'label' => 'LBL_AIRLINE',
                    'customCode' => '{$AIRLINE}',
                ),
                array(
                    'name' => 'date_ticket_issue',
                    'label' => 'LBL_DATE_TICKET_ISSUE',
                    'customCode' => '{$DATE_TICKET_ISSUE}',
                ),
            ),

            array(
                array(
                    'name' => 'is_hold',
                    'label' => 'LBL_IS_HOLD',
                ),
                array(
                    'name' => 'is_agent',
                    'label' => 'LBL_IS_AGENT',
                    'customCode' => '{$IS_AGENT}',
                ),
            ),

            array(
                array(
                    'name' => 'delivery_man',
                    'studio' => 'visible',
                    'label' => 'LBL_DELIVERY_MAN',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'customCode' => '{$CUS_ASSIGNED_USER_NAME}',
                ),
            ),

            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'cols' => 40,
                        'rows' => 5,
                    ),
                ),
                array(),
            ),
        ),

        'lbl_customer_panel' => array(
            array(
                array(
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT_NAME',
                    'customCode' => '{$CONTACT_NAME}',
                ),
                array(
                    'name' => 'phone',
                    'label' => 'LBL_PHONE',
                ),
            ),

            array(
                array(
                    'name' => 'email',
                    'label' => 'LBL_EMAIL',
                ),
                array(
                    'name' => 'email_reservation',
                    'label' => 'LBL_EMAIL_RESERVATION',
                ),
            
            ),

            array(
                array(
                    'name'          => 'country',
                    'studio'        => 'visible',
                    'label'         => 'LBL_COUNTRY',
                    'customCode'    => '{$LOCATION_BOOKING}',
                ),
                // array(
                //     'name' => 'city',
                //     'studio' => 'visible',
                //     'label' => 'LBL_CITY',
                // ),
                array(
                    'name' => 'address',
                    'label' => 'LBL_ADDRESS',
                ),
            ),
        ),

        'lbl_lineitineraries_panel' => array(
            array(
                array(
                    'name' => 'line_itineraries',
                    'label' => 'LBL_LINE_ITINERARIES',
                    'hideLabel' => true,
                    'customCode' => '{$LINE_ITINERARIES}',
                ),
            ),
        ),

        'lbl_linedetails_panel' => array(
            array(
                array(
                    'name' => 'line_details',
                    'label' => 'LBL_LINE_DETAILS',
                    'hideLabel' => true,
                    'customCode' => '{$LINE_DETAILS}',
                ),
            ),
        ),

        'lbl_linepassengers_panel' => array(
            array(
                array(
                    'name' => 'line_passengers',
                    'label' => 'LBL_LINE_PASSENGERS',
                    'hideLabel' => true,
                    'customCode' => '{$LINE_PASSENGERS}',
                ),
            ),
        ),

        'lbl_amount_panel' => array(
            array(
                array(
                    'name' => 'luggage_fee',
                    'label' => 'LBL_LUGGAGE_FEE',
                ),
                array(
                    'name' => 'iv_account_name',
                    'studio' => 'visible',
                    'label' => 'LBL_IV_ACCOUNT_NAME',
                    'customCode' => '{$CUS_IV_ACCOUNT_NAME}',
                ),
            ),

            array(
                array(
                    'name' => 'other_fee',
                    'label' => 'LBL_OTHER_FEE',
                ),
                array(
                    'name' => 'company_name',
                    'label' => 'LBL_COMPANY_NAME',
                ),
            ),

            array(
                array(
                    'name' => 'discount_amount',
                    'label' => 'LBL_DISCOUNT_AMOUNT',
                    // 'customCode' => '{$DISCOUNT_AMOUNT}',
                ),
                array(
                    'name' => 'tax_code',
                    'label' => 'LBL_TAX_CODE',
                ),
            ),

            array(
                array(
                    'name' => 'total_amount',
                    'label' => 'LBL_TOTAL_AMOUNT',
                ),
                array(
                    'name' => 'iv_email',
                    'label' => 'LBL_IV_EMAIL',
                    'customCode' => '{$CUS_IV_EMAIL}',
                ),
            ),

            array(
                array(),
                array(
                    'name' => 'company_address',
                    'studio' => 'visible',
                    'label' => 'LBL_COMPANY_ADDRESS',
                    'displayParams' => array(
                        'cols' => 32,
                        'rows' => 3,
                    ),
                ),
            ),

            array(
                array(),
                array(
                    'name' => 'iv_payment_method',
                    'label' => 'LBL_IV_PAYMENT_METHOD',
                    'customCode' => '{$CUS_IV_PAYMENT_METHOD}',
                ),
            ),

            array(
                array(),
                array(
                    'name' => 'iv_name_banks',
                    'label' => 'LBL_IV_NAME_BANKS',
                    'customCode' => '{$CUS_IV_NAME_BANK}',
                ),
            ),

            array(
                array(),
                array(
                    'name' => 'iv_bank_account',
                    'label' => 'LBL_IV_BANK_ACCOUNT',
                    'customCode' => '{$CUS_IV_BANK_ACCOUNT}',
                ),
            ),
        ),
    ),
);