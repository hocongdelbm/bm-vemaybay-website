<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_LoaiChungTu';
$listViewDefs[$module_name] =  array(
    'MALOAI' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_MALOAI',
        'width' => '10%',
        'default' => true,
    ),
    'NAME' =>
    array(
        'width' => '32%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'TAIKHOANNO' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_TAIKHOANNO',
        'width' => '10%',
        'default' => true,
    ),
    'TAIKHOANCO' =>
    array(
        'type' => 'varchar',
        'label' => 'LBL_TAIKHOANCO',
        'width' => '10%',
        'default' => true,
    ),
    'DESCRIPTION' =>
    array(
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'width' => '10%',
        'default' => true,
    ),
    'ASSIGNED_USER_NAME' =>
    array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => false,
    ),
);
