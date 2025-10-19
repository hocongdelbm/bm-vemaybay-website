<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_HoaDonBan';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '12%',
        'label' => 'LBL_LISTVIEW_NAME',
        'default' => true,
        'link' => true,
        'related_fields' => array(
			'description',
		),
    ),
    'NGAYHOADON' => array(
        'label' => 'LBL_LISTVIEW_NGAYHOADON',
        'width' => '12%',
        'default' => true,
    ),
    'SOHOADON' => array(
        'label' => 'LBL_LISTVIEW_SOHOADON',
        'width' => '12%',
        'default' => true,
        'related_fields' => array(
            'company_unit',
		),
    ),
    'REPRESENT_BOOKING' => array(
        'label'     => 'LBL_REPRESENT_BOOKING',
        'default'   => true,
    ),
    'LOAIKH' => array(
        'label' => 'LBL_LOAIKH',
        'default' => true,
    ),
    // 'TENCONGTY' => array(
    //     'label' => 'LBL_TENCONGTY_KH',
    //     'default' => true,
    //     'related_fields' => array(
	// 		'lienhe',
	// 	),
    // ),
    'MASOTHUE' => array(
        'label' => 'LBL_LISTVIEW_GENERAL_INFO',
        'default' => true,
        'related_fields' => array(
            'tencongty', 'lienhe', 'diachi', 'email', 'citizen_id', 'passport_number'
		),
    ),

    
    'TINHTRANG' => array(
        'label' => 'LBL_TINHTRANG',
        'default' => true,
    ),
    'TONGTHANHTOAN' => array(
        'type' => 'currency',
        'label' => 'LBL_TONGTHANHTOAN',
        'currency_format' => true,
        'default' => true,
    ),
    // 'CREATED_BY_NAME' => array(
    //     'type' => 'varchar',
    //     'label' => 'LBL_CREATED',
    //     'width' => '10%',
    //     'default' => true,
    // ),
    'DATE_ENTERED' => array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
    ),
);
