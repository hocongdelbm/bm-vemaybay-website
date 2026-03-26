<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $mod_strings;

$popupMeta = array(
    'moduleMain' => 'Contact',
    'varName' => 'CONTACT',
    'orderBy' => 'contacts.first_name, contacts.last_name',
    'whereClauses' => array(
        'first_name' => 'contacts.first_name',
        'last_name' => 'contacts.last_name',
        'account_name' => 'accounts.name',
        'account_id' => 'accounts.id'
    ),
    'searchInputs' => array(
        'first_name',
        'last_name',
        'account_name',
        'email'
    ),
    'create' => array(
        'formBase' => 'ContactFormBase.php',
        'formBaseClass' => 'ContactFormBase',
        'getFormBodyParams' => array('', '', 'ContactSave'),
        'createButton' => 'LNK_NEW_CONTACT'
    ),
    'listviewdefs' => array(
        'NAME' => array(
            'width' => '20%',
            'label' => 'LBL_LIST_NAME',
            'link' => true,
            'default' => true,
            'related_fields' => array('first_name', 'last_name', 'salutation', 'account_name', 'account_id')
        ),
        'PHONE_MOBILE' => array(
            'width' => '20%',
            'label' => 'LBL_PHONE_MOBILE',
            'default' => true,
        ),
        'ZALO_ID' => array(
            'width' => '20%',
            'label' => 'LBL_ZALO_ID',
            'default' => true,
        ),
        'TELEGRAM_ID' => array(
            'width' => '20%',
            'label' => 'LBL_TELEGRAM_ID',
            'default' => true,
        ),
        'EMAIL' => array(
            'width' => '20%',
            'label' => 'LBL_EMAIL',
            'default' => true,
        ),
        'ASSIGNED_USER_NAME' => array(
            'width' => '20%',
            'label' => 'LBL_ASSIGNED_USER_NAME',
            'default' => true,
        ),
    ),
    'searchdefs' => array(
        'last_name',
        'phone_mobile',
        'zalo_id',
        'email',
        array('name' => 'assigned_user_id', 'type' => 'enum', 'label' => 'LBL_ASSIGNED_TO', 'function' => array('name' => 'get_user_array', 'params' => array(false))),
    )
);
