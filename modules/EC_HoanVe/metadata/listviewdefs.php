<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_HoanVe';
$listViewDefs[$module_name] = array(
    'NGAYCHUNGTU' =>
    array(
        'type' => 'date',
        'label' => 'LBL_NGAYCHUNGTU',
        'width' => '8%',
        'default' => true,
    ),
    'NAME' =>
    array(
        'width' => '8%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'TINHTRANG' =>
    array(
        'type' => 'enum',
        'label' => 'LBL_TINHTRANG',
        'width' => '8%',
        'default' => true,
    ),
    'BOOKING' =>
    array(
        'type' => 'relate',
        'label' => 'LBL_BOOKING',
        'width' => '8%',
        'default' => true,
    ),
    'NHACC_LIST' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_NHACC_LIST',
        'width' => '10%',
        'default' => true,
        'sortable' => false,
    ),
    'DESCRIPTION' =>
    array(
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'width' => '25%',
        'default' => true,
    ),
    'TICKET_VALIDITY' =>
    array(
        'type' => 'date',
        'label' => 'LBL_TICKET_VALIDITY',
        'width' => '8%',
        'default' => true,
    ),
    'TONGTIENHANG' =>
    array(
        'type' => 'currency',
        'label' => 'LBL_TONGTIENHANG',
        'currency_format' => true,
        'width' => '10%',
        'default' => true,
        'align' => 'center',
    ),
    'TONGTIENKHACH' =>
    array(
        'type' => 'currency',
        'label' => 'LBL_TONGTIENKHACH',
        'currency_format' => true,
        'width' => '10%',
        'default' => true,
        'align' => 'center',
    ),
    'TONGTIENDV' =>
    array(
        'type' => 'currency',
        'label' => 'LBL_TONGTIENDV',
        'currency_format' => true,
        'width' => '10%',
        'default' => true,
        'align' => 'center',
    ),
    'THONGBAO' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_THONGBAO',
        'width' => '10%',
        'default' => true,
        'sortable' => false,
        'align' => 'center',
    ),
    'ASSIGNED_USER_NAME' =>
    array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ),
    'DATE_ENTERED' =>
    array(
        'type' => 'datetime',
        'label' => 'LBL_DATE_ENTERED',
        'width' => '13%',
        'default' => true,
    ),
);
