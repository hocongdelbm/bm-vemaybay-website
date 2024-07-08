<?php

if (!defined('sugarEntry') || !sugarEntry) {
  die('Not A Valid Entry Point');
}

$module_name = 'EC_LoginAudit';
global $current_user;

if (!is_admin($current_user) && !is_admin_for_any_module($current_user)) {
  sugar_die("Unauthorized access to Login Audit.");
}

if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
  $_REQUEST['orderBy'] = 'date_entered';
  $_REQUEST['sortOrder'] = 'desc';
}

$listViewDefs[$module_name] =
  array(
    'MODIFIED_BY_NAME' =>
    array(
      'width' => '10%',
      'label' => 'LBL_MODIFIED',
      'default' => true,
    ),
    'IP_ADDRESS' =>
    array(
      'width' => '10%',
      'label' => 'LBL_IP_ADDRESS',
      'default' => true,
    ),
    // 'TYPED_NAME' => 
    // array (
    //   'width' => '10%',
    //   'label' => 'LBL_TYPED_NAME',
    //   'default' => true,
    // ),
    'DATE_ENTERED' =>
    array(
      'width' => '10%',
      'label' => 'LBL_DATE_ENTERED',
      'default' => true,
    ),
    'IS_ADMIN' =>
    array(
      'width' => '10%',
      'label' => 'LBL_IS_ADMIN',
      'default' => true,
    ),
    'RESULT' =>
    array(
      'width' => '10%',
      'label' => 'LBL_RESULT',
      'default' => true,
    ),
    'PLATFORM' =>
    array(
      'width' => '10%',
      'label' => 'LBL_PLATFORM',
      'default' => true,
    ),
    'BROWSER' =>
    array(
      'width' => '10%',
      'label' => 'LBL_BROWSER',
      'default' => true,
    ),
    // 'USER_AGENT' => 
    // array (
    //   'width' => '10%',
    //   'label' => 'LBL_USER_AGENT',
    //   'default' => true,
    // ),
  );
$this->lv->showMassupdateFields = 0;
