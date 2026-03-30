<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Messages';
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

    'send_to' => array(
        'query_type' => 'format',
        'operator' => 'subquery',
        'subquery' => 'SELECT ec_messages.id
                    FROM ec_messages
                    WHERE IF(LENGTH("{0}") < 10,
                            ec_messages.send_to LIKE "%{0}",
                            ec_messages.send_to = "{0}")
                        AND ec_messages.deleted = 0',
        'db_field' => array('id'),
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

    'range_send_time' =>
    array(
      'query_type' => 'default',
      'enable_range_search' => true,
      'is_date_field' => true,
    ),
    'start_range_send_time' =>
    array(
      'query_type' => 'default',
      'enable_range_search' => true,
      'is_date_field' => true,
    ),
    'end_range_send_time' =>
    array(
      'query_type' => 'default',
      'enable_range_search' => true,
      'is_date_field' => true,
    ),
);
