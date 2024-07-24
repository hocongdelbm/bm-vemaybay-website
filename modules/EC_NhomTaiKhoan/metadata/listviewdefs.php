<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_NhomTaiKhoan';
$listViewDefs[$module_name] = array(
    'MANHOM' => array(
        'type' => 'varchar',
        'label' => 'LBL_MANHOM',
        'width' => '10%',
        'default' => true,
        'link' => true,
    ),
    'NAME' => array(
        'width' => '32%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'TINHCHAT' => array(
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_TINHCHAT',
        'width' => '10%',
    ),
    'DS_CHITIETTHEO' => array(
        'type' => 'enum',
        'studio' => 'visible',
        'label' => 'LBL_DS_CHITIETTHEO',
        'width' => '10%',
        'default' => true,
    ),
    'LOAIDOITUONG' => array(
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_LOAIDOITUONG',
        'width' => '10%',
    ),
    'DESCRIPTION' => array(
        'type' => 'text',
        'label' => 'LBL_DESCRIPTION',
        'width' => '10%',
        'default' => true,
    ),
);
