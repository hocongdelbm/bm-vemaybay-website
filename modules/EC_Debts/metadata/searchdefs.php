<?php

$module_name = 'EC_Debts';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'contact_name' => 
            array (
                'type' => 'varchar',
                'label' => 'LBL_CONTACT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'contact_name',
            ),
            'debt_amount' => 
            array (
                'type' => 'currency',
                'label' => 'LBL_DEBT_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'debt_amount',
            ),
            'booking' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING',
                'width' => '10%',
                'default' => true,
                'name' => 'booking',
            ),
            'date_limit' => 
            array (
              'type' => 'date',
              'label' => 'LBL_DATE_LIMIT',
              'width' => '10%',
              'default' => true,
              'name' => 'date_limit',
            ),
            'supplier' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_SUPPLIER',
                'width' => '10%',
                'default' => true,
                'name' => 'supplier',
            ),
            'debt_type' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_DEBT_TYPE',
                'width' => '10%',
                'name' => 'debt_type',
            ),
            'current_user_only' =>
            array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ),
        ),
        'advanced_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'debt_amount' => 
            array (
                'type' => 'currency',
                'label' => 'LBL_DEBT_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'debt_amount',
            ),
            'contact_name' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_CONTACT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'contact_name',
            ),
            'phone_office' =>
            array(
                'type' => 'phone',
                'label' => 'LBL_PHONE_OFFICE',
                'width' => '10%',
                'default' => true,
                'name' => 'phone_office',
            ),
            'phone_mobile' =>
            array(
                'type' => 'phone',
                'label' => 'LBL_PHONE_MOBILE',
                'width' => '10%',
                'default' => true,
                'name' => 'phone_mobile',
            ),
            'booking' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING',
                'width' => '10%',
                'default' => true,
                'name' => 'booking',
            ),
            'supplier' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_SUPPLIER',
                'width' => '10%',
                'default' => true,
                'name' => 'supplier',
            ),
            'debt_type' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_DEBT_TYPE',
                'width' => '10%',
                'name' => 'debt_type',
            ),
            'date_limit' => 
            array (
              'type' => 'date',
              'label' => 'LBL_DATE_LIMIT',
              'width' => '10%',
              'default' => true,
              'name' => 'date_limit',
            ),
            'description' => 
            array (
                'type' => 'text',
                'label' => 'LBL_DESCRIPTION',
                'width' => '10%',
                'default' => true,
                'name' => 'description',
            ),
            'assigned_user_id' =>
            array(
                'name' => 'assigned_user_id',
                'label' => 'LBL_ASSIGNED_TO',
                'type' => 'enum',
                'function' =>
                array(
                    'name' => 'get_user_array',
                    'params' =>
                    array(
                        0 => false,
                    ),
                ),
                'default' => true,
                'width' => '10%',
            ),
        ),
    ),
);
