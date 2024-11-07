<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Messages';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '16',
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
        'width' => '32',
        'label' => 'LBL_CONTENT',
        'default' => true,
    ),
    'SEND_TIME' => array(
        'label' => 'LBL_SEND_TIME',
        'default' => true,
    ),
    'STATUS' => array(
        'label' => 'LBL_STATUS',
        'default' => true,
    ),
    'CATEGORY' => array(
        'label' => 'LBL_CATEGORY',
        'default' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
);
