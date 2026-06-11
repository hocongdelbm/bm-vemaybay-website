<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Post_Categories';
$listViewDefs[$module_name] = array(
    'ID' => array(
        'width' => '5',
        'label' => 'LBL_ID',
        'default' => false,
    ),
    'NAME' => array(
        'width' => '30',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true
    ),
    'SLUG' => array(
        'width' => '15',
        'label' => 'LBL_SLUG',
        'default' => true,
    ),
    'PARENT_CATEGORY' => array(
        'width' => '20',
        'label' => 'LBL_PARENT_CATEGORY',
        'default' => true,
        'link' => true,
        'module' => 'EC_Post_Categories',
        'id' => 'PARENT_ID',
        'related_fields' => array('parent_id'),
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '15',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
    'POST_COUNT' => array(
        'width' => '10',
        'label' => 'LBL_POST_COUNT',
        'default' => true,
    ),
);
