<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Outbound_Phone';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '32',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true
    ),
    'NETWORK_PROVIDER' => array(
        'width' => '32',
        'label' => 'LBL_NETWORK_PROVIDER',
        'default' => true,
        'link' => true
    ),
    'BRAND_NAME' => array(
        'width' => '32',
        'label' => 'LBL_BRAND_NAME',
        'default' => true,
        'link' => true
    ),
    'WEBSITE' => array(
        'width' => '32',
        'label' => 'LBL_WEBSITE',
        'default' => true,
        'link' => true
    ),
    'STATUS' => array(
        'width' => '32',
        'label' => 'LBL_STATUS',
        'default' => true,
        'link' => true
    ),
    'ONLY_INBOUND' => array(
        'width' => '32',
        'label' => 'LBL_ONLY_INBOUND',
        'default' => true,
        'link' => true
    ),
    'DESCRIPTION' => array(
        'width' => '32',
        'label' => 'LBL_DESCRIPTION',
        'default' => true,
        'link' => true
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),

);
