<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_HoaDonMua';
$listViewDefs[$module_name] = 
array (
    'NAME' => 
    array (
      'width' => '32%',
      'label' => 'LBL_NAME',
      'default' => true,
      'link' => true,
    ),
    'NHACUNGCAP' => 
    array (
      'type' => 'relate',
      'studio' => 'visible',
      'label' => 'LBL_NHACUNGCAP',
      'width' => '10%',
      'default' => true,
    ),
    'NGAYHACHTOAN' => 
    array (
      'type' => 'date',
      'label' => 'LBL_NGAYHACHTOAN',
      'width' => '10%',
      'default' => true,
    ),
    'NGAYCHUNGTU' => 
    array (
      'type' => 'date',
      'label' => 'LBL_NGAYCHUNGTU',
      'width' => '10%',
      'default' => true,
    ),
    'TONGTIEN' => 
    array (
      'type' => 'currency',
      'label' => 'LBL_TONGTIEN',
      'currency_format' => true,
      'width' => '10%',
      'default' => true,
    ),
    'LOAICHUNGTU' => 
    array (
      'type' => 'relate',
      'studio' => 'visible',
      'label' => 'LBL_LOAICHUNGTU',
      'width' => '10%',
      'default' => true,
    ),
    'NHANHOADON' => 
    array (
      'type' => 'bool',
      'label' => 'LBL_NHANHOADON',
      'width' => '10%',
      'default' => true,
    ),
    'ASSIGNED_USER_NAME' => 
    array (
      'width' => '9%',
      'label' => 'LBL_ASSIGNED_TO_NAME',
      'default' => false,
    ),
  );
  