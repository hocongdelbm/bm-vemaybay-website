<?php

/*
 * Created on May 29, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$searchdefs['Users'] = array(
  'templateMeta' => array(
    'maxColumns' => '3',
    'maxColumnsBasic' => '4',
    'widths' => array('label' => '10', 'field' => '30'),
  ),
  'layout' => array(
    'basic_search' => array(
      array(
        'name' => 'search_name',
        'label' => 'LBL_NAME',
        'type' => 'name'
      ),
      // array(
      //   'name' => 'last_name_basic', 
      //   'label' => 'LBL_LAST_NAME', 
      //   'type' => 'name'
      // ),
      // array(
      //   'name' => 'department_basic', 
      //   'label' => 'LBL_DEPARTMENT', 
      //   'type' => 'varchar'
      // ),
      // array(
      //   'name' => 'status_basic', 
      //   'label' => 'LBL_EMPLOYEE_STATUS', 
      //   'type' => 'varchar'
      // ),

    ),
    'advanced_search' => array(
      'last_name',
      'first_name',
      'user_name',
      'status',
      'is_admin',
      'is_group',
      'title',
      'phone' => array(
        'name' => 'phone',
        'label' => 'LBL_ANY_PHONE',
        'type' => 'name',
        'default' => true,
        'width' => '10%',
      ),
      // 'department',
      // 'address_street' =>
      // array(
      //   'name' => 'address_street',
      //   'label' => 'LBL_ANY_ADDRESS',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
      // 'email' =>
      // array(
      //   'name' => 'email',
      //   'label' => 'LBL_ANY_EMAIL',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
      // 'address_city' =>
      // array(
      //   'name' => 'address_city',
      //   'label' => 'LBL_CITY',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
      // 'address_state' =>
      // array(
      //   'name' => 'address_state',
      //   'label' => 'LBL_STATE',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
      // 'address_postalcode' =>
      // array(
      //   'name' => 'address_postalcode',
      //   'label' => 'LBL_POSTAL_CODE',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
      // 'address_country' =>
      // array(
      //   'name' => 'address_country',
      //   'label' => 'LBL_COUNTRY',
      //   'type' => 'name',
      //   'default' => true,
      //   'width' => '10%',
      // ),
    ),
  ),
);
