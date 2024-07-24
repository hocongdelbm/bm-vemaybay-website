<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Payment_Types';
$listViewDefs[$module_name] =
    array(
        'NAME' =>
        array(
            'width' => '10%',
            'label' => 'LBL_NAME',
            'default' => true,
            'link' => true,
        ),
        'DESCRIPTION' =>
        array(
            'type' => 'text',
            'label' => 'LBL_DESCRIPTION',
            'width' => '20%',
            'default' => true,
        ),
        'IS_PAYMENT_DEBT' =>
        array(
            'type' => 'bool',
            'default' => true,
            'label' => 'LBL_IS_PAYMENT_DEBT',
            'width' => '10%',
        ),
        'IS_RECEIPT_DEBT' =>
        array(
            'type' => 'bool',
            'default' => true,
            'label' => 'LBL_IS_RECEIPT_DEBT',
            'width' => '10%',
        ),
        'IS_OTHER_AMOUNT' =>
        array(
            'type' => 'bool',
            'default' => true,
            'label' => 'LBL_IS_OTHER_AMOUNT',
            'width' => '10%',
        ),
        'IS_REPORT' =>
        array(
            'type' => 'bool',
            'default' => true,
            'label' => 'LBL_IS_REPORT',
            'width' => '10%',
        ),
        'AIRCODE' =>
        array(
            'type' => 'enum',
            'studio' => 'visible',
            'label' => 'LBL_AIRCODE',
            'width' => '10%',
            'default' => true,
        ),
        'ASSIGNED_USER_NAME' =>
        array(
            'width' => '9%',
            'label' => 'LBL_ASSIGNED_TO_NAME',
            'default' => true,
        ),
    );
