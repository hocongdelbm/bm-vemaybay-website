<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Contact_Points_Log';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '32',
        'label' => 'LBL_NAME_LISTVIEW',
        'default' => true,
        'link' => true
    ),
    'CONTACT' => array(
        'width' => '24',
        'label' => 'LBL_CONTACT',
        'default' => true,
        'link' => true
    ),
    'CONTACT_PHONE' => array(
        'width' => '10',
        'label' => 'LBL_CONTACT_PHONE',
        'default' => true,
    ),

    'UP' => array(
        'width' => '10',
        'label' => 'LBL_UP',
        'default' => true,
    ),
    'DOWN' => array(
        'width' => '10',
        'label' => 'LBL_DOWN',
        'default' => true,
    ),
    'CURRENT_POINT' => array(
        'width' => '10',
        'label' => 'LBL_CURRENT_POINT',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'label' => 'LBL_DATE_ENTERED',
        'width' => '15%',
        'default' => true,
    ),
);
