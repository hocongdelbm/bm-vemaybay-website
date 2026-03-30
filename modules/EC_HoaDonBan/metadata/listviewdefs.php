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
    'SOHOADON' => array(
        'label' => 'LBL_LISTVIEW_SOHOADON',
        'width' => '12%',
        'default' => true,
        'related_fields' => array('company_unit'),
    ),
    'NGAYHOADON' => array(
        'label' => 'LBL_LISTVIEW_NGAYHOADON',
        'width' => '12%',
        'default' => true,
    ),
    'KYHIEUHD' => array(
        'label' => 'LBL_LISTVIEW_KYHIEUHD',
        'default' => true,
    ),
    'REPRESENT_BOOKING' => array(
        'label'     => 'LBL_REPRESENT_BOOKING',
        'default'   => true,
    ),
    'lienhe' => array(
        'label' => 'LBL_LISTVIEW_GENERAL_INFO',
        'default' => true,
        'related_fields' => array(
            'loaikh', 'masothue', 'tencongty', 'diachi', 'email', 'citizen_id', 'passport_number'
		),
    ),
    'TONGTHANHTOAN' => array(
        'label' => 'LBL_TONGTHANHTOAN',
        'default' => true,
        'type' => 'varchar',
        // 'currency_format' => true,
    ),
    'TINHTRANG' => array(
        'label' => 'LBL_TINHTRANG',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
    ),
);
