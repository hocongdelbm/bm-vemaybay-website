<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$GLOBALS['ldapConfig'] = array(
    'users' =>
    array(
        'fields' =>
        array(
            "givenName" => 'first_name',
            "sn" => 'last_name',
            "mail" => 'email1',
            "telephoneNumber" => 'phone_work',
            "facsimileTelephoneNumber" => 'phone_fax',
            "mobile" => 'phone_mobile',
            "street" => 'address_street',
            "l" => 'address_city',
            "st" => 'address_state',
            "postalCode" => 'address_postalcode',
            "c" => 'address_country'
        )
    ),
    'system' =>
    array('overwriteSugarUserInfo' => true,),
);
