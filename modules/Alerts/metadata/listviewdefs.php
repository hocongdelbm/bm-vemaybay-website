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
    'CREATED_BY_NAME' =>
    array(
      'width' => '10%',
      'label' => 'LBL_CREATED',
      'default' => true,
    ),
    'DATE_ENTERED' =>
    array(
      'width' => '5%',
      'label' => 'LBL_DATE_ENTERED',
      'default' => true,
    ),
    'ASSIGNED_USER_NAME' =>
    array(
      'width' => '10%',
      'label' => 'LBL_ASSIGNED_TO_NAME',
      'default' => true,
    ),
    'VIEWED_AT' =>
    array(
      'width' => '5%',
      'label' => 'LBL_VIEWED_AT',
      'default' => true,
    ),
  );
