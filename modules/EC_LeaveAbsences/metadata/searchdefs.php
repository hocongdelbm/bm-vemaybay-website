<?php
$module_name = 'EC_LeaveAbsences';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            'date_entered',
            // array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            'name',
            'date_entered',
            'from_date',
            'to_date',
            'absence_type', 
            'assigned_user_name',
            'status',
            // array(
            //     'name' => 'assigned_user_id',
            //     'label' => 'LBL_ASSIGNED_TO',
            //     'type' => 'enum',
            //     'function' => array('name' => 'get_user_array', 'params' => array(false))
            // ),
        ),
    ),
);
