<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Location';
$listViewDefs[$module_name] = array (
    'NAME' => 
    array (
      'width' => '15%',
      'label' => 'LBL_NAME',
      'default' => true,
      'link' => true,
    ),
    'COMPANY' => 
    array (
      'type' => 'relate',
      'studio' => 'visible',
      'label' => 'LBL_COMPANY',
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
    'IS_DISPLAY' =>
    array(
      'type' => 'text',
      'label' => 'LBL_IS_DISPLAY',
      'width' => '10%',
      'default' => true,
    ),
    'ASSIGNED_USER_NAME' => 
    array (
      'width' => '9%',
      'label' => 'LBL_ASSIGNED_TO_NAME',
      'default' => true,
    ),
    'DATE_ENTERED' => 
    array (
      'type' => 'datetime',
      'label' => 'LBL_DATE_ENTERED',
      'width' => '10%',
      'default' => true,
    ),
  );
