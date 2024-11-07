<?php
$module_name = 'EC_Messages';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            array(
                'name' => 'name',
                'label' => 'LBL_NAME'
            ),
            'send_to',
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            array(
                'name' => 'name',
                'label' => 'LBL_NAME'
            ),
            'send_to',
            array(
                'name' => 'send_time',
                'label' => 'LBL_SEND_TIME',
                'type' => 'date',
                'width' => '10%',
                'default' => true,
            ),
            'type',
            'category',
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
