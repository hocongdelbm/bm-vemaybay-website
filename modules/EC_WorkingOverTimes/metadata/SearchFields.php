<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_WorkingOverTimes';
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
    'register_employee' => array(
        'query_type' => 'format',
        'operator' => 'subquery',
        'subquery' =>  'SELECT dt.ec_workingovertimes_id_c
                        FROM ec_workingovertimedetails dt
                        LEFT JOIN users u ON u.id = dt.assigned_user_id AND u.deleted = 0
                        WHERE dt.deleted = 0 
                        AND CONCAT(u.last_name, " ", u.first_name) LIKE "%"',
        'like_char' => '%',
        'db_field' => array('id'),
    ),
    'register_date' => array(
        'query_type' => 'default',
        'operator' => 'subquery',
        'subquery' =>  'SELECT dt.ec_workingovertimes_id_c
                        FROM ec_workingovertimedetails dt
                        WHERE dt.deleted = 0 
                        AND dt.register_date >= {0} AND dt.register_date <= {0}',
        'like_char' => '%',
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
);
