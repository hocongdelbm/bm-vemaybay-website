<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$OBJECT_NAME = '<OBJECT_NAME>';
$listViewDefs[$module_name] = array(
    $OBJECT_NAME . '_NUMBER' => array(
        'width' => '5',
        'label' => 'LBL_NUMBER',
        'link' => true,
        'default' => true
    ),
    'NAME' => array(
        'width' => '32',
        'label' => 'LBL_SUBJECT',
        'default' => true,
        'link' => true
    ),
    'STATUS' => array(
        'width' => '10',
        'label' => 'LBL_STATUS',
        'default' => true
    ),
    'PRIORITY' => array(
        'width' => '10',
        'label' => 'LBL_PRIORITY',
        'default' => true
    ),
    'RESOLUTION' => array(
        'width' => '10',
        'label' => 'LBL_RESOLUTION',
        'default' => true
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9',
        'label' => 'LBL_ASSIGNED_USER',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),

);
