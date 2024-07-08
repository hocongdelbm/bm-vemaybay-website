<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_TaiKhoan';
$listViewDefs[$module_name] = array(
    'SOTAIKHOAN' => array(
        'type' => 'varchar',
        'label' => 'LBL_SOTAIKHOAN',
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
    'TENTIENGANH' => array(
        'type' => 'varchar',
        'label' => 'LBL_TENTIENGANH',
        'width' => '10%',
        'default' => true,
    ),
    'TAIKHOANTONGHOP' => array(
        'type' => 'varchar',
        'label' => 'LBL_TAIKHOANTONGHOP',
        'width' => '10%',
        'default' => true,
    ),
    'NHOMTAIKHOAN' => array(
        'type' => 'varchar',
        'label' => 'LBL_NHOMTAIKHOAN',
        'width' => '10%',
        'default' => true,
    ),
    'TINHCHAT' => array(
        'type' => 'enum',
        'default' => true,
        'studio' => 'visible',
        'label' => 'LBL_TINHCHAT',
        'width' => '10%',
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => false,
    ),
);
