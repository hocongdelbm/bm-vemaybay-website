<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_SMS_Logs';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true
    ),
    'SEND_FROM' => array(
        'label' => 'LBL_SEND_FROM',
        'default' => true,
    ),
    'SEND_TO' => array(
        'label' => 'LBL_SEND_TO',
        'default' => true,
    ),
    'CONTENT' => array(
        'label' => 'LBL_CONTENT',
        'default' => true,
        'related_fields' => array('type')
    ),
    'SEND_DATE' => array(
        'label' => 'LBL_SEND_DATE',
        'default' => true,
    ),
    'STATUS' => array(
        'label' => 'LBL_STATUS',
        'default' => true
    ),
    'MESSAGE_TYPE' => array(
        'label' => 'LBL_MESSAGE_TYPE',
        'default' => true
    ),
    'ASSIGNED_USER_NAME' => array(
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
);
