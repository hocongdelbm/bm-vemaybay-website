<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Zalo_Contacts';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '20',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
        'related_fields' => array('alias'),
    ),

    'ZALO_ID' => array(
        'label' => 'LBL_ZALO_ID',
        'default' => true,
    ),

    'CONTACT_NAME' => array(
        'width' => '20',
        'label' => 'LBL_CONTACT',
        'default' => true,
        'link' => true
    ),

    'IS_FOLLOWER' => array(
        'label' => 'LBL_IS_FOLLOWER',
        'default' => true,
    ),

    'LAST_INTERACTION' => array(
        'label' => 'LBL_LAST_INTERACTION',
        'default' => true,
    ),

    'DATE_ENTERED' => array(
        'label' => 'LBL_DATE_ENTERED',
        'width' => '15%',
        'default' => true,
    ),

    // 'ASSIGNED_USER_NAME' => array(
    //     'width' => '9',
    //     'label' => 'LBL_ASSIGNED_TO_NAME',
    //     'module' => 'Employees',
    //     'id' => 'ASSIGNED_USER_ID',
    //     'default' => true
    // ),
);
