<?php

$module_name = 'EC_HoanVe';
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
            'booking' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_BOOKING',
                'width' => '10%',
                'default' => true,
                'name' => 'booking',
            ),
            'ngaychungtu' =>
            array(
                'type' => 'date',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
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
            'ngaychungtu' =>
            array(
                'type' => 'date',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
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
            'hoten_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_HOTEN_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'hoten_search',
            ),
            'airline_code_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_AIRLINE_CODE_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'airline_code_search',
            ),
            'noidi_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_NOIDI_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'noidi_search',
            ),
            'noiden_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_NOIDEN_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'noiden_search',
            ),
            'sove_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_SOVE_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'sove_search',
            ),
            'pnr_search' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_PNR_SEARCH',
                'width' => '10%',
                'default' => true,
                'name' => 'pnr_search',
            ),
            'tinhtrang' =>
            array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_TINHTRANG',
                'width' => '10%',
                'default' => true,
                'name' => 'tinhtrang',
            ),
            'assigned_user_name' =>
            array(
                'name' => 'assigned_user_name',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                // 'type' => 'enum',
                // 'function' => 
                // array (
                //   'name' => 'get_user_array',
                //   'params' => 
                //   array (
                //     0 => false,
                //   ),
                // ),
                'default' => true,
                'width' => '10%',
            ),
            'ngayhachtoan' =>
            array(
                'type' => 'date',
                'label' => 'LBL_NGAYHACHTOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'ngayhachtoan',
            ),
            'date_entered' => array(
                'type' => 'datetime',   
                'label' => 'LBL_DATE_ENTERED',
                'width' => '10%',
                'default' => true,
                'name' => 'date_entered',
            ),
        ),
    ),
);
