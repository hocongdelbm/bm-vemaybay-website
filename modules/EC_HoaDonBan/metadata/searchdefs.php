<?php

$module_name = 'EC_HoaDonBan';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'sohoadon' => array(
                'name' => 'sohoadon',
                'default' => true,
                'width' => '10%',
            ),
            'ngayhoadon' => array(
                'type' => 'date',
                'label' => 'LBL_NGAYHOADON',
                'width' => '10%',
                'default' => true,
                'name' => 'ngayhoadon',
            ),
            // 'current_user_only' =>
            // array(
            //     'name' => 'current_user_only',
            //     'label' => 'LBL_CURRENT_USER_FILTER',
            //     'type' => 'bool',
            //     'default' => true,
            //     'width' => '10%',
            // ),
            'masothue' => array(
                'name' => 'masothue',
                'default' => true,
                'width' => '10%',
            ),
            'tencongty' => array(
                'name' => 'tencongty',
                'default' => true,
                'width' => '10%',
            ),
            'lienhe' => array(
                'name' => 'lienhe',
                'default' => true,
                'width' => '10%',
            ),
            'email' => array(
                'name' => 'email',
                'default' => true,
                'width' => '10%',
            ),
            'booking' => array(
                'name' => 'booking',
                'type' => 'varchar',
                'label' => 'LBL_BOOKING',
                'default' => true,
                'width' => '10%',
            ),
            'ticket_number' => array(
                'name' => 'ticket_number',
                'type' => 'varchar',
                'label' => 'LBL_TICKET_NUMBER',
                'default' => true,
                'width' => '10%',
            ),
            'loaikh' => array(
                'name' => 'loaikh',
                'type' => 'enum',
                'label' => 'LBL_LOAIKH',
                'default' => true,
                'width' => '10%',
            ),
            'tinhtrang' => array(
                'name' => 'tinhtrang',
                'type' => 'enum',
                'label' => 'LBL_TINHTRANG',
                'default' => true,
                'width' => '10%',
            ),
            // 'company_unit' => array(
            //     'name' => 'company_unit',
            //     'type' => 'enum',
            //     'label' => 'LBL_COMPANY_UNIT',
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
        'advanced_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'tencongty' => array(
                'name' => 'tencongty',
                'default' => true,
                'width' => '10%',
            ),
            'date_entered' => array(
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
            'sohoadon' => array(
                'name' => 'sohoadon',
                'default' => true,
                'width' => '10%',
            ),
            'masothue' => array(
                'name' => 'masothue',
                'default' => true,
                'width' => '10%',
            ),
            'email' => array(
                'name' => 'email',
                'default' => true,
                'width' => '10%',
            ),
            'ngayhoadon' => array(
                'type' => 'date',
                'label' => 'LBL_NGAYHOADON',
                'width' => '10%',
                'default' => true,
                'name' => 'ngayhoadon',
            ),
            'lienhe' => array(
                'name' => 'lienhe',
                'default' => true,
                'width' => '10%',
            ),
            'assigned_user_name' => array(
                'name' => 'assigned_user_name',
                'default' => true,
                'width' => '10%',
            ),
            'booking' => array(
                'name' => 'booking',
                'type' => 'varchar',
                'label' => 'LBL_BOOKING',
                'default' => true,
                'width' => '10%',
            ),
            'ticket_number' => array(
                'name' => 'ticket_number',
                'type' => 'varchar',
                'label' => 'LBL_TICKET_NUMBER',
                'default' => true,
                'width' => '10%',
            ),
            'date_modified' => array(
                'label' => 'LBL_DATE_MODIFIED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_modified',
            ),
            'loaihoadon' => array(
                'name' => 'loaihoadon',
                'default' => true,
                'width' => '10%',
            ),
            'hinhthuctt' => array(
                'name' => 'hinhthuctt',
                'default' => true,
                'width' => '10%',
            ),
        ),
    ),
);
