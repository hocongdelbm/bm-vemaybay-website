<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


global $app_list_strings;
$configurator = new Configurator();

$searchdefs['Contacts'] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'last_name' => array(
                'name' => 'last_name',
                'default' => true,
                'width' => '10%',
                'type' => 'name',
            ),
            // array('name' => 'search_name', 'label' => 'LBL_NAME', 'type' => 'name'),
            'phone_mobile' => array(
                'name' => 'phone_mobile',
                'label' => 'LBL_PHONE_MOBILE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'email' => array(
                'name' => 'email',
                'label' => 'LBL_ANY_EMAIL',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'is_uncomfortable' => array(
				'name' => 'is_uncomfortable',
				'label' => 'LBL_IS_UNCOMFORTABLE',
				'default' => true,
				'width' => '10%',
			),
            'is_ctv' => array(
				'name' => 'is_ctv',
				'label' => 'LBL_IS_CTV',
				'default' => true,
				'width' => '10%',
			),
            'is_compare_price' => array(
				'name' => 'is_compare_price',
				'label' => 'LBL_IS_COMPARE_PRICE',
				'default' => true,
				'width' => '10%',
			),
            array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
            ),
            // array('name' => 'favorites_only', 'label' => 'LBL_FAVORITES_FILTER', 'type' => 'bool',),
        ),
        'advanced_search' =>
        array(
            // 'first_name' => array(
            //         'name' => 'first_name',
            //         'default' => true,
            //         'width' => '10%',
            //     ),
            'last_name' => array(
                'name' => 'last_name',
                'default' => true,
                'width' => '10%',
                'type' => 'name',
            ),
            // 'name' => array(
            //     'name' => 'search_name',
            //     'label' => 'LBL_NAME', 
            //     'type' => 'name',
            //     'default' => true,
            //     'width' => '10%',
            // ),
            'phone_mobile' => array(
                'name' => 'phone_mobile',
                'label' => 'LBL_PHONE_MOBILE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'email' => array(
                'name' => 'email',
                'label' => 'LBL_ANY_EMAIL',
                'type' => 'name',
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
            'address_state' => array(
                'name' => 'address_state',
                'label' => 'LBL_STATE',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'address_street' => array(
                'name' => 'address_street',
                'label' => 'LBL_ANY_ADDRESS',
                'type' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            // 'address_postalcode' => array(
            //     'name' => 'address_postalcode',
            //     'label' => 'LBL_POSTAL_CODE',
            //     'type' => 'name',
            //     'default' => true,
            //     'width' => '10%',
            // ),
            // 'assigned_user_id' =>
            //     array(
            //         'name' => 'assigned_user_id',
            //         'type' => 'enum',
            //         'label' => 'LBL_ASSIGNED_TO',
            //         'function' =>
            //             array(
            //                 'name' => 'get_user_array',
            //                 'params' =>
            //                     array(
            //                         0 => false,
            //                     ),
            //             ),
            //         'default' => true,
            //         'width' => '10%',
            //     ),
            // 'primary_address_country' => array(
            //     'name' => 'primary_address_country',
            //     'label' => 'LBL_COUNTRY',
            //     'type' => 'name',
            //     'options' => 'countries_dom',
            //     'default' => true,
            //     'width' => '10%',
            // ),
            'is_uncomfortable' => array(
				'name' => 'is_uncomfortable',
				'label' => 'LBL_IS_UNCOMFORTABLE',
				'default' => true,
				'width' => '10%',
			),
            'is_ctv' => array(
				'name' => 'is_ctv',
				'label' => 'LBL_IS_CTV',
				'default' => true,
				'width' => '10%',
			),
            'is_compare_price' => array(
				'name' => 'is_compare_price',
				'label' => 'LBL_IS_COMPARE_PRICE',
				'default' => true,
				'width' => '10%',
			),
            'lead_source' => array(
                'name' => 'lead_source',
                'default' => true,
                'width' => '10%',
            ),
        ),
    )
);


if ($configurator->isConfirmOptInEnabled() || $configurator->isOptInEnabled()) {
    $searchdefs['Contacts']['layout']['advanced_search']['optinprimary'] = array(
        'name' => 'optinprimary',
        'label' => 'LBL_OPT_IN_FLAG_PRIMARY',
        'type' => 'enum',
        'options' => $app_list_strings['email_confirmed_opt_in_dom'],
        'default' => true,
        'width' => '10%',
    );
}
