<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Banks';
$listViewDefs[$module_name] = array(
    'SHORT_NAME' => array(
        'type' => 'varchar',
        'label' => 'LBL_SHORT_NAME',
        'width' => '10%',
        'default' => true,
    ),
    'NAME' => array(
        'width' => '20%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'ENGLISH_NAME' => array(
        'type' => 'varchar',
        'label' => 'LBL_ENGLISH_NAME',
        'width' => '20%',
        'default' => true,
    ),
    'HEADQUARTERS' => array(
        'type' => 'text',
        'studio' => 'visible',
        'label' => 'LBL_HEADQUARTERS',
        'width' => '10%',
        'default' => true,
    ),
    'UNFOLLOW' => array(
        'type' => 'bool',
        'label' => 'LBL_UNFOLLOW',
        'width' => '10%',
        'default' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => false,
    ),
);
