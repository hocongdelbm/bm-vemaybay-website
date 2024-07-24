<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_HoaDonBan';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '10%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'NGAYHOADON' => array(
        'label' => 'LBL_NGAYHOADON',
        'width' => '10%',
        'default' => true,
    ),
    'SOHOADON' => array(
        'label' => 'LBL_SOHOADON',
        'width' => '12%',
        'default' => true,
        // 'type' => 'int',
    ),
    'COMPANY_UNIT' => array(
        'label' => 'LBL_COMPANY_UNIT',
        'width' => '12%',
        'default' => true,
        // 'type' => 'int',
    ),
    'DESCRIPTION' => array(
        'label' => 'LBL_DESCRIPTION',
        'width' => '10%',
        'default' => true,
    ),
    'TENCONGTY' => array(
        'label' => 'LBL_TENCONGTY_KH',
        'width' => '12%',
        'default' => true,
    ),
    'TINHTRANG' => array(
        'label' => 'LBL_TINHTRANG',
        'width' => '10%',
        'default' => true,
    ),
    'TONGTHANHTOAN' => array(
        'type' => 'currency',
        'label' => 'LBL_TONGTHANHTOAN',
        'currency_format' => true,
        'width' => '10%',
        'default' => true,
    ),
    'CREATED_BY_NAME' => array(
        'type' => 'varchar',
        'label' => 'LBL_CREATED',
        'width' => '10%',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'width' => '10%',
        'default' => true,
    ),
);
