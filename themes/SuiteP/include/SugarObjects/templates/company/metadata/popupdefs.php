<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$object_name = '<object_name>';
$_module_name = '<_module_name>';
$popupMeta = array(
    'moduleMain' => $module_name,
    'varName' => $object_name,
    'orderBy' => $_module_name . '.name',
    'whereClauses' => array(
        'name' => $_module_name . '.name',
        'billing_address_city' => $_module_name . '.billing_address_city',
        'phone_office' => $_module_name . '.phone_office'
    ),
    'searchInputs' => array(
        'name',
        'billing_address_city',
        'phone_office',
        'industry'

    ),
);
