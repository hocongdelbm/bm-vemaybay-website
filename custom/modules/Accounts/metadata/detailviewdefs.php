<?php
$viewdefs['Accounts'] = array(
    'DetailView' => array(
        'templateMeta' => array(
            'form' => array(
                'buttons' => array(
                    'EDIT',
                    'DUPLICATE',
                    'DELETE',
                    'FIND_DUPLICATES',
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
                    'file' => 'modules/Accounts/Account.js',
                ),
            ),
        ),

        'panels' => array(
            'default' => array(
                array(
                    array(
                        'name' => 'name',
                        'comment' => 'Name of the Company',
                        'label' => 'LBL_NAME',
                    ),
                    array(
                        'name' => 'ticker_symbol',
                        'comment' => 'The stock trading (ticker) symbol for the company',
                        'label' => 'LBL_TICKER_SYMBOL',
                    ),
                ),

                array(
                    array(
                        'name' => 'phone_office',
                        'comment' => 'The office phone number',
                        'label' => 'LBL_PHONE_OFFICE',
                    ),
                    array(
                        'name' => 'phone_alternate',
                        'label' => 'LBL_OTHER_PHONE',
                    ),
                ),

                array(
                    array(
                        'name' => 'ownership',
                        'comment' => '',
                        'label' => 'LBL_OWNERSHIP',
                    ),
                    array(
                        'name' => 'sic_code',
                        'comment' => 'SIC code of the account',
                        'label' => 'LBL_SIC_CODE',
                    ),
                ),

                array(
                    array(
                        'name' => 'account_type',
                        'comment' => 'The Company is of this type',
                        'label' => 'LBL_TYPE',
                    ),
                    array(
                        'name' => 'assigned_user_name',
                        'label' => 'LBL_ASSIGNED_TO',
                    ),
                ),

                array(
                    array(
                        'name' => 'date_entered',
                        'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    ),
                    array(
                        'name' => 'date_modified',
                        'label' => 'LBL_DATE_MODIFIED',
                        'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    ),
                ),

                array(
                    array(
                        'name' => 'billing_address_street',
                        'label' => 'LBL_BILLING_ADDRESS',
                        'type' => 'address',
                        'displayParams' => array(
                            'key' => 'billing',
                        ),
                    ),
                    array(
                        'name' => 'email1',
                        'studio' => 'false',
                        'label' => 'LBL_EMAIL',
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
                        'comment' => 'Full text of the note',
                        'label' => 'LBL_DESCRIPTION',
                    ),
                ),
            ),
        ),
    ),
);
