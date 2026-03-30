<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['Contacts'] = array(
    // 'NAME' => array(
    //     'width' => '20%',
    //     'label' => 'LBL_LIST_NAME',
    //     'link' => true,
    //     'contextMenu' => array(
    //         'objectType' => 'sugarPerson',
    //         'metaData' => array(
    //             'contact_id' => '{$ID}',
    //             'module' => 'Contacts',
    //             'return_action' => 'ListView',
    //             'contact_name' => '{$FULL_NAME}',
    //             'parent_id' => '{$ACCOUNT_ID}',
    //             'parent_name' => '{$ACCOUNT_NAME}',
    //             'return_module' => 'Contacts',
    //             'return_action' => 'ListView',
    //             'parent_type' => 'Account',
    //             'notes_parent_type' => 'Account'
    //         )
    //     ),
    //     'orderBy' => 'name',
    //     'default' => true,
    //     'related_fields' => array('first_name', 'last_name', 'salutation', 'account_name', 'account_id'),
    // ),
    'LAST_NAME' => array(
        'width' => '20%',
        'label' => 'LBL_LAST_NAME',
        'link' => true,
        'default' => true,
    ),
    // 'TITLE' => array(
    //     'width' => '15%',
    //     'label' => 'LBL_LIST_TITLE',
    //     'default' => true
    // ),
    // 'ACCOUNT_NAME' => array(
    //     'width' => '34%',
    //     'label' => 'LBL_LIST_ACCOUNT_NAME',
    //     'module' => 'Accounts',
    //     'id' => 'ACCOUNT_ID',
    //     'link' => true,
    //     'contextMenu' => array(
    //         'objectType' => 'sugarAccount',
    //         'metaData' => array(
    //             'return_module' => 'Contacts',
    //             'return_action' => 'ListView',
    //             'module' => 'Accounts',
    //             'return_action' => 'ListView',
    //             'parent_id' => '{$ACCOUNT_ID}',
    //             'parent_name' => '{$ACCOUNT_NAME}',
    //             'account_id' => '{$ACCOUNT_ID}',
    //             'account_name' => '{$ACCOUNT_NAME}'
    //         ),
    //     ),
    //     'default' => true,
    //     'sortable' => true,
    //     'ACLTag' => 'ACCOUNT',
    //     'related_fields' => array('account_id')
    // ),
    'PHONE_MOBILE' => array(
        'width' => '15%',
        'label' => 'LBL_PHONE_MOBILE',
        'default' => true,
    ),
    'EMAIL1' => array(
        'width' => '15%',
        'label' => 'LBL_LIST_EMAIL_ADDRESS',
        'sortable' => false,
        'link' => true,
        'customCode' => '{$EMAIL1_LINK}',
        'default' => true
    ),
    'POINTS' => array(
        'width' => '10%',
        'label' => 'LBL_POINTS',
        'default' => true,
    ),
    'PHONE_WORK' => array(
        'width' => '15%',
        'label' => 'LBL_OFFICE_PHONE',
    ),
    'DEPARTMENT' => array(
        'width' => '10',
        'label' => 'LBL_DEPARTMENT'
    ),
    'DO_NOT_CALL' => array(
        'width' => '10',
        'label' => 'LBL_DO_NOT_CALL'
    ),
    'PHONE_HOME' => array(
        'width' => '10',
        'label' => 'LBL_HOME_PHONE'
    ),
    // 'PHONE_MOBILE' => array(
    //     'width' => '10',
    //     'label' => 'LBL_MOBILE_PHONE'
    // ),
    'PHONE_OTHER' => array(
        'width' => '10',
        'label' => 'LBL_OTHER_PHONE'
    ),
    'PHONE_FAX' => array(
        'width' => '10',
        'label' => 'LBL_FAX_PHONE'
    ),
    // 'EMAIL2' => array(
    //     'width' => '15',
    //     'label' => 'LBL_LIST_EMAIL_ADDRESS',
    //     'sortable' => false,
    //     'customCode' => '{$EMAIL2_LINK}{$EMAIL2}</a>',
    //     'default' => true
    // ),
    'EMAIL_OPT_OUT' => array(
        'width' => '10',
        'label' => 'LBL_EMAIL_OPT_OUT'
    ),
    'PRIMARY_ADDRESS_STREET' => array(
        'width' => '10',
        'label' => 'LBL_PRIMARY_ADDRESS_STREET'
    ),
    // 'PRIMARY_ADDRESS_CITY' => array(
    //     'width' => '10',
    //     'label' => 'LBL_PRIMARY_ADDRESS_CITY',
    //     'default' => true
    // ),
    'DESCRIPTION' => array(
        'width' => '10%',
        'label' => 'LBL_DESCRIPTION',
        'default' => true
    ),
    'PRIMARY_ADDRESS_STATE' => array(
        'width' => '10',
        'label' => 'LBL_PRIMARY_ADDRESS_STATE'
    ),
    'PRIMARY_ADDRESS_POSTALCODE' => array(
        'width' => '10',
        'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE'
    ),
    'ALT_ADDRESS_COUNTRY' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_COUNTRY'
    ),
    'ALT_ADDRESS_STREET' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_STREET'
    ),
    'ALT_ADDRESS_CITY' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_CITY'
    ),
    'ALT_ADDRESS_STATE' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_STATE'
    ),
    'ALT_ADDRESS_POSTALCODE' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_POSTALCODE'
    ),
    'ALT_ADDRESS_COUNTRY' => array(
        'width' => '10',
        'label' => 'LBL_ALT_ADDRESS_COUNTRY'
    ),
    'CREATED_BY_NAME' => array(
        'width' => '10',
        'label' => 'LBL_CREATED'
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '10',
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
    'MODIFIED_BY_NAME' => array(
        'width' => '10',
        'label' => 'LBL_MODIFIED'
    ),
    'SYNC_CONTACT' => array(
        'type' => 'bool',
        'label' => 'LBL_SYNC_CONTACT',
        'width' => '10%',
        'default' => false,
        'sortable' => false,
    ),
    'IS_UNCOMFORTABLE' => array(
        'type' => 'bool',
        'label' => 'LBL_IS_UNCOMFORTABLE',
        'width' => '10%',
        'default' => true,
    ),
    'IS_COMPARE_PRICE' => array(
        'type' => 'bool',
        'label' => 'LBL_IS_COMPARE_PRICE',
        'width' => '10%',
        'default' => true,
    ),
    'IS_CTV' => array(
        'type' => 'bool',
        'label' => 'LBL_IS_CTV',
        'width' => '10%',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'width' => '10',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true
    )
);
