<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$searchFields['Notes'] =
    array(
        'name' => array( 'query_type'=>'default'),
        'contact_name' => array( 'query_type'=>'default','db_field'=>array('contacts.first_name','contacts.last_name')),
        'favorites_only' => array(
            'query_type'=>'format',
            'operator' => 'subquery',
            'checked_only' => true,
            'subquery' => "SELECT favorites.parent_id FROM favorites
			                    WHERE favorites.deleted = 0
			                        and favorites.parent_type = 'Notes'
			                        and favorites.assigned_user_id = '{1}'",
            'db_field'=>array('id')),
        
       //Range Search Support
       'range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'start_range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'end_range_date_entered' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'start_range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'end_range_date_modified' => array('query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true),
       'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
       //Range Search Support
    );
