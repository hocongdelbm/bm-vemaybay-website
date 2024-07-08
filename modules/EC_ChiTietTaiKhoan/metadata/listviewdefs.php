<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_ChiTietTaiKhoan';
$listViewDefs[$module_name] = array (
    'SOTAIKHOAN' => 
    array (
      'type' => 'varchar',
      'label' => 'LBL_SOTAIKHOAN',
      'width' => '10%',
      'default' => true,
    ),
    'NAME' => 
    array (
      'width' => '20%',
      'label' => 'LBL_NAME',
      'default' => true,
      'link' => true,
    ),
    'DUNODAU' => 
    array (
      'type' => 'currency',
      'label' => 'LBL_DUNODAU',
      'currency_format' => true,
      'width' => '10%',
      'default' => true,
    ),
    'DUCODAU' => 
    array (
      'type' => 'currency',
      'label' => 'LBL_DUCODAU',
      'currency_format' => true,
      'width' => '10%',
      'default' => true,
    ),
    'DESCRIPTION' => 
    array (
      'type' => 'text',
      'label' => 'LBL_DESCRIPTION',
      'width' => '25%',
      'default' => true,
    ),
    'COMPANY' => 
  array (
    'type' => 'relate',
    'studio' => 'visible',
    'label' => 'LBL_COMPANY',
    'width' => '15%',
    'default' => true,
  ),
  'LOCATION' => 
  array (
    'type' => 'relate',
    'studio' => 'visible',
    'label' => 'LBL_LOCATION',
    'width' => '15%',
    'default' => true,
  ),
    'ASSIGNED_USER_NAME' => 
    array (
      'width' => '10%',
      'label' => 'LBL_ASSIGNED_TO_NAME',
      'default' => false,
    ),
    'DATE_ENTERED' => 
    array (
      'width' => '10%',
      'label' => 'LBL_DATE_ENTERED',
      'default' => false,
    ),
  );
