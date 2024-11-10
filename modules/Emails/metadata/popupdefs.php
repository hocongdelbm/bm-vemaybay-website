<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$popupMeta = array(
    'moduleMain' => 'Email',
    'varName' => 'EMAIL',
    'orderBy' => 'name',
    'whereClauses' => array(
        'name' => 'emails.name',
        'contact_name' => 'contacts.last_name'
    ),
    'searchInputs' => array(
        'name',
        'contact_name',
        'request_data'
    ),
);
