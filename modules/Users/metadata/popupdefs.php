<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
$popupMeta = array(
    'moduleMain' => 'User',
    'varName' => 'USER',
    'orderBy' => 'user_name',
    'whereClauses' => array(
        'first_name' => 'users.first_name',
        'last_name' => 'users.last_name',
        'user_name' => 'users.user_name',
        'is_group' => 'users.is_group',
    ),
    'whereStatement'=> " users.status = 'Active' and users.portal_only= '0'",
    'searchInputs' => array(
        'first_name',
        'last_name',
        'user_name',
        'is_group',
    ),
);
