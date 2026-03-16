<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Zalo_Apps';
$listViewDefs[$module_name] = array(
    'ID' => array(
        'width' => '10',
        'label' => 'LBL_ID',
        'default' => true,
        'link' => true
    ),
    'NAME' => array(
        'width' => '16',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => false,
    ),
    'OA_NAME' => array(
        'width' => '16',
        'label' => 'LBL_OA_NAME',
        'default' => true,
        'link' => true,
    ),
    'DESCRIPTION' => array(
        'label' => 'LBL_DESCRIPTION',
        'default' => true,
    ),
    'CREATE_BY_NAME' => array(
        'width' => '9',
        'label' => 'LBL_CREATED_USER',
        'module' => 'Employees',
        'id' => 'CREATED_BY',
        'default' => true
    ),
);
