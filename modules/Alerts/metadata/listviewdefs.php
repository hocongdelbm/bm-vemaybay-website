<?php
if (!defined('sugarEntry') || !sugarEntry) {
  die('Not A Valid Entry Point');
}
$listViewDefs['Alerts'] =
  array(
    'NAME' =>
    array(
      'width' => '20%',
      'label' => 'LBL_NAME',
      'link' => true,
      'default' => true,
    ),
    'TYPE' =>
    array(
      'width' => '10%',
      'label' => 'LBL_TYPE',
      'default' => true,
    ),
    'PRIORITY' =>
    array(
      'width' => '10%',
      'label' => 'LBL_PRIORITY',
      'default' => true,
    ),
    'CREATED_BY_NAME' =>
    array(
      'width' => '10%',
      'label' => 'LBL_CREATED',
      'default' => false,
    ),
    'DATE_ENTERED' =>
    array(
      'width' => '5%',
      'label' => 'LBL_LIST_DATE_ENTERED',
      'default' => true,
    ),
    'ASSIGNED_USER_NAME' =>
    array(
      'width' => '10%',
      'label' => 'LBL_ASSIGNED_TO_NAME',
      'default' => true,
    ),
    'IS_READ' =>
    array(
      'width' => '5%',
      'label' => 'LBL_IS_READ',
      'type'        => 'bool',
      'default' => true,
    ),
    'VIEWED_AT' =>
    array(
      'width' => '5%',
      'label' => 'LBL_VIEWED_AT',
      'default' => true,
    ),
  );
