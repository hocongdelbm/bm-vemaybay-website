<?php

$module_name = 'EC_Flight_Bookings';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DELETE',
                'DUPLICATE',
                array(
                    'customCode' => '{$CALLS_BUTTON}',
                ),
                array(
                    'customCode' => '{$SEND_MAIL}',
                ),
                array(
                    'customCode' => '{$CANCELLED}',
                ),
                array(
                    'customCode' => '{$STATUS_BUTTON}',
                ),
                array(
                    'customCode' => '{$CREATE_RV}',
                ),
                array(
                    'customCode' => '{$TICKET_RETURN}',
                ),
                array(
                    'customCode' => '{$PUBLISHED_TO_WEB}',
                ),
                array(
                    'customCode' => '{$CREATE_INVOICE}',
                ),
                // array(
                //     'customCode' => '{$SYNC_PNR}',
                // ),
                // Thay đổi code vé / PNR / nhà cung cấp
                array(
                    'customCode' => '{$EDIT_BKG_DETAIL}',
                ),
                // Thay đổi thông tin giờ bay / tên hành khách
                array(
                    'customCode' => '{$CHANGE_FLIGHT_TIME}',
                ),
                array(
                    'customCode' => '{$BUTTON_AUTO_BOOK}',
                ),
                // Chia doanh số
                // array(
                //     'customCode' => '{$SHARE_PROFIT}',
                // ),
                array(
                    'customCode' => '{$BUTTON_LINE_NOTES}',
                ),
                array(
                    'customCode' => '{$CHANGE_STATUS}',
                ),
                array(
                    'customCode' => '{$UPDATE_REVENUE}',
                ),
                array(
                    'customCode' => '{$VIEWED_BOOKING}',
                ),
                array(
                    'customCode' => '{$PRINT_TICKET}',
                ),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array(
                'file' => 'custom/jqueryui/plugins/formatNumber.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
            array(
                'file' => 'custom/jqueryui/plugins/allowNumberOnly.js',
            ),
            array(
                'file' => 'themes/SuiteP/js/reset.js',
            ),
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'customCode' => '{$CUSTOM_NAME}',
                ),
                array(
                    'name' => 'CUSTOMER_SOURCE',
                    'label' => 'LBL_CUSTOMER_SOURCE',
                    'customCode' => '{$CUSTOM_CUSTOMER_SOURCE}',
                ),
            ),

            array(
                array(
                    'name' => 'ticket_type',
                    'studio' => 'visible',
                    'label' => 'LBL_TICKET_TYPE',
                    'customCode' => '{$CUSTOM_TICKET_TYPE}',
                ),
                array(
                    'name' => 'bookmark',
                    'label' => 'LBL_BOOKMARK',
                    'customCode' => '{$CUSTOM_BOOKMARK}'
                ),
            ),

            array(
                array(
                    'name' => 'bookmark_system',
                    'label' => 'LBL_BOOKMARK_SYSTEM',
                    'customCode' => '{$CUSTOM_BOOKMARK_SYSTEM}'
                ),
                array(
                    'name' => 'is_paid',
                    'label' => 'LBL_IS_PAID',
                    'customCode' => '{$IS_PAID}',
                ),
            ),

            array(
                array(
                    'name' => 'holding_status',
                    'studio' => 'visible',
                    'label' => 'LBL_HOLDING_STATUS',
                ),
                array(
                    'name' => 'is_ticket_exported',
                    'label' => 'LBL_IS_TICKET_EXPORTED',
                    'customCode' => '{$CUSTOM_IS_EXPORTED}',
                ),
            ),

            array(
                array(
                    'name' => 'payment_type',
                    'studio' => 'visible',
                    'label' => 'LBL_PAYMENT_TYPE',
                ),
                array(
                    'name' => 'date_ticket_issue',
                    'label' => 'LBL_DATE_TICKET_ISSUE',
                    'customCode' => '{$CUSTOM_DATE_TICKET_ISSUE}',
                ),
            ),

            array(
                array(
                    'name' => 'airline',
                    'label' => 'LBL_AIRLINE',
                    'customCode' => '{$CUSTOM_AIRLINE}',
                ),
                array(
                    'name' => 'is_invoice_export',
                    'label' => 'LBL_INVOICE',
                    'customCode' => '{$IS_INVOICE_EXPORT}',
                ),
            ),
           
            array(
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
                array(
                    'name' => 'recheck_status',
                    'studio' => 'visible',
                    'label' => 'Thao tác',
                    'customCode' => '{$RECHECK_STATUS}',
                ),
            ),
           
            array(
                array(
                    'name' => 'lydothangthua',
                    'studio' => 'visible',
                    'label' => 'LBL_LYDOTHANGTHUA',
                ),
                array(
                    'name' => 'payment',
                    'label' => 'LBL_PAYMENT',
                    'customCode' => '{$CUSTOM_NGANLUONG_CODE}',
                ),
            ),

            array(
                array(
                    'name' => 'ghichuthangthua',
                    'studio' => 'visible',
                    'label' => 'LBL_GHICHUTHANGTHUA',
                ),
                array(
                    'name' => 'transaction_history',
                    'label' => 'LBL_TRANSACTION_HISTORY',
                    'customCode' => '{$CUSTOM_TRANSACTION_HISTORY}',
                ),
            ),

            array(
                array(
                    'name' => 'ip_address',
                    'label' => 'LBL_IP_ADDRESS',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'customCode' => '{$CUSTOM_ASSIGNED_TO_NAME}',
                ),
            ),

            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
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
                    'customCode' => '{$CONTACT_PHONE}'
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
                    'name' => 'city',
                    'studio' => 'visible',
                    'label' => 'LBL_CITY',
                ),
                array(
                    'name' => 'address',
                    'label' => 'LBL_ADDRESS',
                ),
                // array(
                //     'name' => 'country',
                //     'studio' => 'visible',
                //     'label' => 'LBL_COUNTRY',
                // ),
            ),
        ),

        'lbl_lineitineraries_panel' => array(
            array(
                array(
                    'name' => 'line_itineraries',
                    'label' => false,
                    'customCode' => '{$LINE_ITINERARIES}',
                ),
            ),
        ),

        'lbl_linedetails_panel' => array(
            array(
                array(
                    'name' => 'line_details',
                    'studio' => 'visible',
                    'label' => false,
                    'customCode' => '{$LINE_DETAILS}',
                ),
            ),
        ),

        'lbl_linepassengers_panel' => array(
            array(
                array(
                    'name' => 'line_passengers',
                    'studio' => 'visible',
                    'label' => false,
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
                    'customCode' => '{$CUS_DISCOUNT_AMOUNT}',
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
                    'name' => 'iv_identity_number',
                    'label' => 'LBL_IV_IDENTITY_NUMBER',
                    'customCode' => '{$CUS_IV_IDENTITY_NUMBER}',
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

            // array(
            //     array(),
            //     array(
            //         'name' => 'iv_name_banks',
            //         'label' => 'LBL_IV_NAME_BANKS',
            //         'customCode' => '{$CUS_IV_NAME_BANK}',
            //     ),
            // ),

            // array(
            //     array(),
            //     array(
            //         'name' => 'iv_bank_account',
            //         'label' => 'LBL_IV_BANK_ACCOUNT',
            //         'customCode' => '{$CUS_IV_BANK_ACCOUNT}',
            //     ),
            // ),
        ),
    )
);
