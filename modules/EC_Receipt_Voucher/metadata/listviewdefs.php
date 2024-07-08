<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Receipt_Voucher';
$listViewDefs[$module_name] = array(
    'NAME' =>
    array(
        'width' => '15%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'RV_STATUS' =>
    array(
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_RV_STATUS',
        'width' => '8%',
    ),
    'BOOKING_NAME' =>
    array(
        'type' => 'relate',
        'studio' => 'visible',
        'label' => 'LBL_BOOKING_NAME',
        'width' => '8%',
        'default' => true,
    ),
    'NGAYCHUNGTU' =>
    array(
        'type' => 'date',
        'label' => 'LBL_NGAYCHUNGTU',
        'width' => '10%',
        'default' => true,
    ),
    'AMOUNT_CONVERTED' =>
    array(
        'type' => 'int',
        'label' => 'LBL_AMOUNT_CONVERTED',
        'width' => '11%',
        'default' => true,
        'currency_format' => true,
    ),
    // 'LOAI_THU' =>
    // array(
    //     'type' => 'enum',
    //     'default' => true,
    //     'studio' => 'visible',
    //     'label' => 'LBL_LOAI_THU',
    //     'width' => '10%',
    // ),
    'GUEST_NAME' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_GUEST_NAME',
        'width' => '12%',
        'default' => true,
    ),
    'RECEIPT_TYPE' =>
    array(
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_RECEIPT_TYPE',
        'width' => '10%',
    ),
    'DESCRIPTION' =>
    array(
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'width' => '20%',
        'default' => true,
    ),
    'DATE_ENTERED' =>
    array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'width' => '13%',
        'default' => true,
    ),
    'NGAYHACHTOAN' =>
    array(
        'type' => 'date',
        'label' => 'LBL_NGAYHACHTOAN',
        'width' => '8%',
        'default' => false,
    ),
);
