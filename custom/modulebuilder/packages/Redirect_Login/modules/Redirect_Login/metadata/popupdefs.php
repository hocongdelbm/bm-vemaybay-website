<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'LZ_Redirect_Login';
$object_name = 'LZ_Redirect_Login';
$_module_name = 'lz_redirect_login';
$popupMeta = array(
    'moduleMain' => $module_name,
    'varName' => $object_name,
    'orderBy' => $_module_name . '.name',
    'whereClauses' => array(
        'name' => $_module_name . '.name',
    ),
    'searchInputs' => array($_module_name . '_number', 'name', 'priority', 'status'),

);
