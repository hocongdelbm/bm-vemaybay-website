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
    'orderBy' => $_module_name . '.first_name, ' . $_module_name . '.last_name',
    'whereClauses' => array(
        'first_name' => $_module_name . '.first_name',
        'last_name' => $_module_name . '.last_name',
    ),
    'searchInputs' => array('first_name', 'last_name'),
);
