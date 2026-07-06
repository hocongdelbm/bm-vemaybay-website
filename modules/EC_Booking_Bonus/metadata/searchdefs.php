<?php
$module_name = 'EC_Booking_Bonus';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name' => array('name' => 'name', 'default' => true, 'width' => '10%'),
            'current_user_only' => array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool', 'default' => true, 'width' => '10%'),
        ),
        'advanced_search' => array(
            'name' => array('name' => 'name', 'default' => true, 'width' => '10%'),
            'flight_date' => array('name' => 'flight_date', 'default' => true, 'width' => '10%'),
            'assigned_user_id' => array('name' => 'assigned_user_id', 'type' => 'enum', 'label' => 'LBL_ASSIGNED_TO_NAME', 'function' => array('name' => 'get_user_array', 'params' => array(false)), 'default' => true, 'width' => '10%'),
        ),
    ),
);
