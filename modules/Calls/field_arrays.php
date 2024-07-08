<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$fields_array['Call'] = array(
    'column_fields' => array(
        "id", "date_entered", "date_modified", "assigned_user_id", "modified_user_id", "created_by", "description", "status", "direction", "name", "date_start", "time_start", "duration_hours", "duration_minutes", "date_end", "parent_type", "parent_id", 'reminder_time', 'outlook_id'
    ),
    'list_fields' => array(
        'id', 'duration_hours', 'direction', 'status', 'name', 'parent_type', 'parent_name', 'parent_id', 'date_start', 'time_start', 'assigned_user_name', 'assigned_user_id', 'contact_name', 'contact_id', 'first_name', 'last_name', 'required', 'outlook_id', 'accept_status'
    ),
    'required_fields' => array("name" => 1, "date_start" => 2, "time_start" => 3,),
);
