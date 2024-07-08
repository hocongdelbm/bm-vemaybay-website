<?php

$module_name = 'EC_Payment_Types';
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
            'is_report' =>
            array(
                'type' => 'bool',
                'default' => true,
                'label' => 'LBL_IS_REPORT',
                'width' => '10%',
                'name' => 'is_report',
            ),
            'current_user_only' =>
            array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'width' => '10%',
                'default' => true,
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
            'is_report' =>
            array(
                'type' => 'bool',
                'default' => true,
                'label' => 'LBL_IS_REPORT',
                'width' => '10%',
                'name' => 'is_report',
            ),
            'assigned_user_name' =>
            array(
                'link' => 'assigned_user_link',
                'type' => 'relate',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'assigned_user_name',
            ),
            'is_other_amount' =>
            array(
                'type' => 'bool',
                'default' => true,
                'label' => 'LBL_IS_OTHER_AMOUNT',
                'width' => '10%',
                'name' => 'is_other_amount',
            ),
            'is_receipt_debt' =>
            array(
                'type' => 'bool',
                'default' => true,
                'label' => 'LBL_IS_RECEIPT_DEBT',
                'width' => '10%',
                'name' => 'is_receipt_debt',
            ),
            'date_entered' =>
            array(
                'type' => 'date',
                'default' => true,
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'name' => 'date_entered',
            ),
            'aircode' =>
            array(
                'type' => 'enum',
                'default' => true,
                'label' => 'LBL_AIRCODE',
                'width' => '10%',
                'name' => 'aircode',
            ),
            'is_payment_debt' =>
            array(
                'type' => 'bool',
                'default' => true,
                'label' => 'LBL_IS_PAYMENT_DEBT',
                'width' => '10%',
                'name' => 'is_payment_debt',
            ),
        ),
    ),
);
