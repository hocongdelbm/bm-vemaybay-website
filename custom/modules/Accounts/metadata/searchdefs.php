<?php
$searchdefs['Accounts'] = array(
    'layout' => array(
        'basic_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'ticker_symbol' => array(
                'type' => 'varchar',
                'label' => 'LBL_TICKER_SYMBOL',
                'width' => '10%',
                'default' => true,
                'name' => 'ticker_symbol',
            ),
            'phone_office' => array(
                'name' => 'phone_office',
                'default' => true,
                'width' => '10%',
            ),
            'billing_address_city' => array(
                'name' => 'billing_address_city',
                'default' => true,
                'width' => '10%',
            ),
        ),

        'advanced_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'ticker_symbol' => array(
                'name' => 'ticker_symbol',
                'default' => true,
                'width' => '10%',
            ),
            'sic_code' => array(
                'name' => 'sic_code',
                'default' => true,
                'width' => '10%',
            ),
            'ownership' => array(
                'name' => 'ownership',
                'default' => true,
                'width' => '10%',
            ),
            'address_city' => array(
                'name' => 'address_city',
                'label' => 'LBL_CITY',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'phone_office' => array(
                'type' => 'phone',
                'default' => true,
                'width' => '10%',
                'label' => 'LBL_PHONE_OFFICE',
                'name' => 'phone_office',
            ),
            'account_type' => array(
                'name' => 'account_type',
                'default' => true,
                'width' => '10%',
            ),
        ),
    ),
    'templateMeta' => array(
        'maxColumns' => '3',
        'widths' => array(
            'label' => '10',
            'field' => '30',
        ),
    ),
);
