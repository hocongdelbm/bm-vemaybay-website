<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_object_name = '<_object_name>';
$searchFields[$module_name] = array(
    'name' => array('query_type' => 'default'),
    'status' => array(
        'query_type' => 'default',
        'options' => $_object_name . '_status_dom',
        'template_var' => 'STATUS_OPTIONS'
    ),
    'priority' => array(
        'query_type' => 'default',
        'options' => $_object_name . '_priority_dom',
        'template_var' => 'PRIORITY_OPTIONS'
    ),
    'resolution' => array(
        'query_type' => 'default',
        'options' => $_object_name . '_resolution_dom',
        'template_var' => 'RESOLUTION_OPTIONS'
    ),
    $_object_name . '_number' => array('query_type' => 'default', 'operator' => 'in'),
    'current_user_only' => array(
        'query_type' => 'default',
        'db_field' => array('assigned_user_id'),
        'my_items' => true,
        'vname' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool'
    ),
    'assigned_user_id' => array('query_type' => 'default'),
    'open_only' => array(
        'query_type' => 'default',
        'db_field' => array('status'),
        'operator' => 'not in',
        'closed_values' => array('Closed', 'Rejected', 'Duplicate'),
        'type' => 'bool',
    ),
    //Range Search Support
    'range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    'start_range_date_entered' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'end_range_date_entered' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    'start_range_date_modified' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'end_range_date_modified' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    //Range Search Support
);
