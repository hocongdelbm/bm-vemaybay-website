<?php

$module_name = 'EC_WorkingOverTimes';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            // array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            'name',
            array('name' => 'register_employee', 'label' => 'LBL_REGISTER_EMPLOYEE', 'type' => 'varchar'),
            'assigned_user_id',
            'date_modified',
            array('name' => 'register_date', 'label' => 'LBL_REGISTER_DATE', 'type' => 'date'),
            'date_entered',
            'status',
            'type',
        ),
    ),
);
