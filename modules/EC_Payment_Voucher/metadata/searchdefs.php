<?php

$module_name = 'EC_Payment_Voucher';
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
            'receipent_name' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_RECEIPENT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'receipent_name',
            ),
            'amount' =>
            array(
                'type' => 'currency',
                'label' => 'LBL_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'amount',
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
            'receipent_name' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_RECEIPENT_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'receipent_name',
            ),
            'amount' =>
            array(
                'type' => 'currency',
                'label' => 'LBL_AMOUNT',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'amount',
            ),
            'ngaychungtu' =>
            array(
                'type' => 'datetime',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
           
            ),
            // 'ngayhachtoan' =>
            // array(
            //     'type' => 'datetimecombo',
            //     'label' => 'LBL_NGAYHACHTOAN',
            //     'width' => '10%',
            //     'default' => true,
            //     'name' => 'ngayhachtoan',
            // ),
            'date_entered' => array(
                'type' => 'datetime',
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
            'payment_type' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_PAYMENT_TYPE',
                'width' => '10%',
                'default' => true,
                'name' => 'payment_type',
            ),
            'tknganhang' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_TKNGANHANG',
                'width' => '10%',
                'default' => true,
                'name' => 'tknganhang',
            ),
            'hoanve' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_HOANVE',
                'width' => '10%',
                'default' => true,
                'name' => 'hoanve',
            ),
            'phieuthu' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_PHIEUTHU',
                'width' => '10%',
                'default' => true,
                'name' => 'phieuthu',
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
            'com_location' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_COM_LOCATION',
                'width' => '10%',
                'default' => true,
                'name' => 'com_location',
            ),
            '',
            'pv_status' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_PV_STATUS',
                'width' => '10%',
                'name' => 'pv_status',
            ),
            'hinhthucchi' =>
            array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_HINHTHUCCHI',
                'width' => '10%',
                'name' => 'hinhthucchi',
            ),
            'description',
        ),
    ),
);
