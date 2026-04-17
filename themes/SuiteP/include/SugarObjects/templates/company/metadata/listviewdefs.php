<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$OBJECT_NAME = '<OBJECT_NAME>';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '40',
        'label' => 'LBL_ACCOUNT_NAME',
        'link' => true,
        'default' => true
    ),
    'BILLING_ADDRESS_CITY' => array(
        'width' => '10',
        'label' => 'LBL_CITY',
        'default' => true
    ),
    'PHONE_OFFICE' => array(
        'width' => '10',
        'label' => 'LBL_PHONE',
        'default' => true
    ),
    $OBJECT_NAME . '_TYPE' => array(
        'width' => '10',
        'label' => 'LBL_TYPE'
    ),
    'INDUSTRY' => array(
        'width' => '10',
        'label' => 'LBL_INDUSTRY'
    ),
    'ANNUAL_REVENUE' => array(
        'width' => '10',
        'label' => 'LBL_ANNUAL_REVENUE'
    ),
    'PHONE_FAX' => array(
        'width' => '10',
        'label' => 'LBL_PHONE_FAX'
    ),
    'BILLING_ADDRESS_STREET' => array(
        'width' => '15',
        'label' => 'LBL_BILLING_ADDRESS_STREET'
    ),
    'BILLING_ADDRESS_STATE' => array(
        'width' => '7',
        'label' => 'LBL_BILLING_ADDRESS_STATE'
    ),
    'BILLING_ADDRESS_POSTALCODE' => array(
        'width' => '10',
        'label' => 'LBL_BILLING_ADDRESS_POSTALCODE'
    ),
    'BILLING_ADDRESS_COUNTRY' => array(
        'width' => '10',
        'label' => 'LBL_BILLING_ADDRESS_COUNTRY'
    ),
    'SHIPPING_ADDRESS_STREET' => array(
        'width' => '15',
        'label' => 'LBL_SHIPPING_ADDRESS_STREET'
    ),
    'SHIPPING_ADDRESS_CITY' => array(
        'width' => '10',
        'label' => 'LBL_SHIPPING_ADDRESS_CITY'
    ),
    'SHIPPING_ADDRESS_STATE' => array(
        'width' => '7',
        'label' => 'LBL_SHIPPING_ADDRESS_STATE'
    ),
    'SHIPPING_ADDRESS_POSTALCODE' => array(
        'width' => '10',
        'label' => 'LBL_SHIPPING_ADDRESS_POSTALCODE'
    ),
    'SHIPPING_ADDRESS_COUNTRY' => array(
        'width' => '10',
        'label' => 'LBL_SHIPPING_ADDRESS_COUNTRY'
    ),
    'PHONE_ALTERNATE' => array(
        'width' => '10',
        'label' => 'LBL_PHONE_ALT'
    ),
    'WEBSITE' => array(
        'width' => '10',
        'label' => 'LBL_WEBSITE'
    ),
    'OWNERSHIP' => array(
        'width' => '10',
        'label' => 'LBL_OWNERSHIP'
    ),
    'EMPLOYEES' => array(
        'width' => '10',
        'label' => 'LBL_EMPLOYEES'
    ),
    'TICKER_SYMBOL' => array(
        'width' => '10',
        'label' => 'LBL_TICKER_SYMBOL'
    ),
    'EMAIL1' => array(
        'width' => '15%',
        'label' => 'LBL_EMAIL_ADDRESS',
        'sortable' => false,
        'link' => true,
        'customCode' => '{$EMAIL1_LINK}',
        'default' => true
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '2',
        'label' => 'LBL_ASSIGNED_USER',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
);
