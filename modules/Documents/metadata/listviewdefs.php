<?php
if (!defined('sugarEntry') || !sugarEntry) {
  die('Not A Valid Entry Point');
}

$listViewDefs['Documents'] = array(
  'DOCUMENT_NAME' =>
  array(
    'width' => '20%',
    'label' => 'LBL_NAME',
    'link' => true,
    'default' => true,
    'bold' => true,
  ),
  'FILENAME' =>
  array(
    'width' => '20%',
    'label' => 'LBL_FILENAME',
    'link' => true,
    'default' => true,
    'bold' => false,
    'displayParams' => array('module' => 'Documents',),
    'sortable' => false,
    'related_fields' =>
    array(
      0 => 'document_revision_id',
      1 => 'doc_id',
      2 => 'doc_type',
      3 => 'doc_url',
    ),
  ),
  'TEMPLATE_TYPE' =>
  array(
    'width' => '10%',
    'label' => 'LBL_TEMPLATE_TYPE',
    'default' => true,
  ),
  'CATEGORY_ID' =>
  array(
    'width' => '10%',
    'label' => 'LBL_LIST_CATEGORY',
    'default' => true,
  ),

  'BOOKING_NAME' =>
  array(
    'width' => '10%',
    'label' => 'LBL_BOOKING_NAME',
    'default' => true,
    'id' => 'BOOKING_ID',
    'link' => true,
    'module' => 'EC_Flight_Bookings',
    'related_fields' => array(
        0 => 'booking_id' // Đảm bảo query luôn lấy booking_id kèm theo
    ),
  ),
  'SUBCATEGORY_ID' =>
  array(
    'width' => '15%',
    'label' => 'LBL_LIST_SUBCATEGORY',
    'default' => false,
  ),
  'LAST_REV_CREATE_DATE' =>
  array(
    'width' => '10%',
    'label' => 'LBL_LIST_LAST_REV_DATE',
    'default' => false,
    'sortable' => false,
    'related_fields' =>
    array(
      0 => 'document_revision_id',
    ),
  ),
  'EXP_DATE' =>
  array(
    'width' => '10%',
    'label' => 'LBL_LIST_EXP_DATE',
    'default' => false,
  ),
  'ASSIGNED_USER_NAME' =>
  array(
    'width' => '10',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'module' => 'Employees',
    'id' => 'ASSIGNED_USER_ID',
    'default' => false
  ),
  'CREATED_BY_NAME' =>
  array(
    'width' => '10%',
    'label' => 'LBL_CREATED_BY',
    'module' => 'Users',
    'id' => 'USERS_ID',
    'default' => true,
    'sortable' => false,
    'related_fields' =>
    array(
      0 => 'created_by',
    ),
  ),
  'MODIFIED_BY_NAME' =>
  array(
    'width' => '10%',
    'label' => 'LBL_MODIFIED_USER',
    'module' => 'Users',
    'id' => 'USERS_ID',
    'default' => true,
    'sortable' => false,
    'related_fields' =>
    array(
      0 => 'modified_user_id',
    ),
  ),
  'DATE_ENTERED' => array(
    'width' => '10%',
    'label' => 'LBL_DATE_ENTERED',
    'default' => true,
  )
);
