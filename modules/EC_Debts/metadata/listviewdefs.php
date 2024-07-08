<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Debts';
$listViewDefs[$module_name] =
    array(
        'NAME' =>
        array(
            'width' => '32%',
            'label' => 'LBL_NAME',
            'default' => true,
            'link' => true,
        ),
        // 'DEBT_AMOUNT' =>
        // array(
        //     'type' => 'currency',
        //     'label' => 'LBL_DEBT_AMOUNT',
        //     'currency_format' => true,
        //     'width' => '10%',
        //     'default' => true,
        // ),
        'DEBT_TYPE' =>
        array(
            'type' => 'enum',
            'default' => true,
            'studio' => 'visible',
            'label' => 'LBL_DEBT_TYPE',
            'width' => '10%',
        ),
        'CONTACT_NAME' =>
        array(
            'type' => 'varchar',
            'label' => 'LBL_CONTACT_NAME',
            'width' => '10%',
            'default' => true,
        ),
        'DESCRIPTION' =>
        array(
            'type' => 'text',
            'label' => 'LBL_DESCRIPTION',
            'width' => '30%',
            'default' => true,
        ),
        'PHONE_OFFICE' =>
        array(
            'type' => 'phone',
            'label' => 'LBL_PHONE_OFFICE',
            'width' => '10%',
            'default' => true,
        ),
        'PHONE_MOBILE' =>
        array(
            'type' => 'phone',
            'label' => 'LBL_PHONE_MOBILE',
            'width' => '10%',
            'default' => true,
        ),
        'EMAIL' =>
        array(
            'type' => 'varchar',
            'label' => 'LBL_EMAIL',
            'width' => '10%',
            'default' => true,
        ),
        'BOOKING' =>
        array(
            'type' => 'relate',
            'studio' => 'visible',
            'label' => 'LBL_BOOKING',
            'width' => '10%',
            'default' => true,
        ),
        'PAID_AMOUNT' =>
        array(
            'type' => 'currency',
            'label' => 'LBL_PAID_AMOUNT',
            'currency_format' => true,
            'width' => '10%',
            'default' => false,
        ),
        'SUPPLIER' =>
        array(
            'type' => 'relate',
            'studio' => 'visible',
            'label' => 'LBL_SUPPLIER',
            'width' => '10%',
            'default' => true,
        ),
        'DATE_ENTERED' =>
        array(
            'type' => 'datetime',
            'label' => 'LBL_DATE_ENTERED',
            'width' => '13%',
            'default' => true,
        ),
        // 'DATE_LIMIT' =>
        // array(
        //     'type' => 'date',
        //     'label' => 'LBL_DATE_LIMIT',
        //     'width' => '10%',
        //     'default' => false,
        // ),
        'DEBT_STATUS' =>
        array(
            'type' => 'enum',
            'default' => false,
            'studio' => 'visible',
            'label' => 'LBL_DEBT_STATUS',
            'width' => '10%',
        ),
        'ASSIGNED_USER_NAME' =>
        array(
            'width' => '9%',
            'label' => 'LBL_ASSIGNED_TO_NAME',
            'default' => true,
        ),
    );
