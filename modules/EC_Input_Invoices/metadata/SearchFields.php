<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Input_Invoices';
$searchFields[$module_name] = array(
    'name' => array('query_type' => 'default'),
    'current_user_only' => array(
        'query_type' => 'default',
        'db_field' => array('assigned_user_id'),
        'my_items' => true,
        'vname' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool'
    ),
    'assigned_user_id' => array('query_type' => 'default'),
    'from_value' => array(
        'query_type' => 'default',
        'operator' => '>=',
        'db_field' => array('total'),
        'is_numeric_search' => 1,
        'add_custom_where' => ' AND out_of_stock = 0',
    ),
    'to_value' => array(
        'query_type' => 'default',
        'operator' => '<=',
        'db_field' => array('total'),
        'is_numeric_search' => 1,
        'add_custom_where' => ' AND out_of_stock = 0',
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
