<?php

$module_name = 'EC_Receipt_Voucher';
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
            'guest_name' =>
            array(
                // 'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_GUEST_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'guest_name',
            ),
            'booking_name' =>
            array(
                // 'type' => 'relate',
                'type' => 'varchar',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'booking_name',
            ),
            'amount_converted' => 
                array (
                    'type' => 'currency',
                    'label' => 'LBL_AMOUNT_CONVERTED',
                    'currency_format' => true,
                    'width' => '10%',
                    'default' => true,
                    'name' => 'amount_converted',
                ),
            'ngaychungtu' =>
            array(
                'type' => 'date',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
                'enable_range_search' => true,

            ),
            'date_entered' =>
            array(
                'type' => 'datetime',
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
            // 'current_user_only' =>
            // array(
            //     'name' => 'current_user_only',
            //     'label' => 'LBL_CURRENT_USER_FILTER',
            //     'type' => 'bool',
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
        'advanced_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'guest_name' =>
            array(
                // 'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_GUEST_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'guest_name',
            ),
            'amount' => 
            array (
                'type' => 'currency',
                'label' => 'LBL_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'amount',
            ),
            'ngaychungtu' =>
            array(
                'type' => 'date',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
            ),
            'date_entered' =>
            array(
                'type' => 'datetime',
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
            'booking_name' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'booking_name',
            ),
            'rv_status' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_RV_STATUS',
                'width' => '10%',
                'name' => 'rv_status',
            ),
            'loai_thu' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_LOAI_THU',
                'width' => '10%',
                'name' => 'loai_thu',
            ),
            'receipt_type' => 
            array (
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_RECEIPT_TYPE',
                'width' => '10%',
                'name' => 'receipt_type',
            ),
            'description' 
        ),
    ),
);
