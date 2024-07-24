<?php
$viewdefs['Accounts'] = array(
    'EditView' => array(
        'templateMeta' => array(
            'form' => array(
                'buttons' => array(
                    'SAVE',
                    'CANCEL',
                ),
            ),
            'maxColumns' => '2',
            'widths' => array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'includes' => array(
                array(
                    'file' => 'custom/modules/Accounts/js/Accounts.js',
                ),
            ),
        ),
        'panels' => array(
            'lbl_account_information' => array(
                array(
                    array(
                        'name' => 'name',
                        'label' => 'LBL_NAME',
                        'displayParams' => array(
                            'required' => true,
                        ),
                    ),
                    array(
                        'name' => 'ownership',
                        'label' => 'LBL_OWNERSHIP',
                    ),
                ),

                array(
                    array(
                        'name' => 'ticker_symbol',
                        'label' => 'LBL_TICKER_SYMBOL',
                    ),
                    array(
                        'name' => 'sic_code',
                        'label' => 'LBL_SIC_CODE',
                    ),
                ),

                array(
                    array(
                        'name' => 'phone_office',
                        'label' => 'LBL_PHONE_OFFICE',
                    ),
                    array(
                        'name' => 'phone_fax',
                        'label' => 'LBL_PHONE_FAX',
                    ),
                ),

                array(
                    array(
                        'name' => 'phone_alternate',
                        'label' => 'LBL_OTHER_PHONE',
                    ),
                    array(
                        'name' => 'account_type',
                    ),
                ),

                array(
                    array(
                        'name' => 'is_stop_tracking',
                        'label' => 'LBL_IS_STOP_TRACKING',
                    ),
                    array(
                        'name' => 'is_margin',
                        'label' => 'LBL_IS_MARGIN',
                    ),
                ),

                array(
                    'balance_observe',
                    array(
                        'name' => 'is_reported',
                        'label' => 'LBL_IS_REPORTED',
                    ),
                ),

                array(
                    array(
                        'name' => 'description',
                        'displayParams' => array(
                            'cols' => 32,
                            'rows' => 4,
                        ),
                        'label' => 'LBL_DESCRIPTION',
                    ),
                ),
            ),

            'lbl_panel1' => array(
                array(
                    array(
                        'name' => 'shipping_address_street',
                        'hideLabel' => true,
                        'type' => 'address',
                        'displayParams' => array(
                            'key' => 'shipping',
                            'copy' => 'billing',
                            'rows' => 2,
                            'cols' => 30,
                            'maxlength' => 150,
                        ),
                    ),
                ),
            ),

            'lbl_email_addresses' => array(
                array(
                    array(
                        'name' => 'email1',
                        'studio' => 'false',
                        'label' => 'LBL_EMAIL',
                    ),
                ),
            ),
        ),
    ),
);
