<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_module_name = '<_module_name>';
$searchFields[$module_name] = array(
    'name' => array('query_type' => 'default'),
    /*'account_name'=> array('query_type'=>'default','db_field'=>array('accounts.name')),*/
    'amount' => array('query_type' => 'default'),
    'next_step' => array('query_type' => 'default'),
    'probability' => array('query_type' => 'default'),
    'lead_source' => array(
        'query_type' => 'default',
        'operator' => '=',
        'options' => 'lead_source_dom',
        'template_var' => 'LEAD_SOURCE_OPTIONS'
    ),
    $_module_name . '_type' => array(
        'query_type' => 'default',
        'operator' => '=',
        'options' => 'opportunity_type_dom',
        'template_var' => 'TYPE_OPTIONS'
    ),
    'sales_stage' => array(
        'query_type' => 'default',
        'operator' => '=',
        'options' => 'sales_stage_dom',
        'template_var' => 'SALES_STAGE_OPTIONS',
        'options_add_blank' => true
    ),
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
        'db_field' => array('sales_stage'),
        'operator' => 'not in',
        'closed_values' => array('Closed Won', 'Closed Lost'),
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
    'range_date_closed' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    'start_range_date_closed' => array(
        'query_type' => 'default',
        'enable_range_search' => true,
        'is_date_field' => true
    ),
    'end_range_date_closed' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
    //Range Search Support
);
