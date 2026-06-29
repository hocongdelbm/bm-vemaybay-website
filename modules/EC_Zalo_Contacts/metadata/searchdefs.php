<?php
$module_name = 'EC_Zalo_Contacts';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            'alias',
            'phone_mobile_search' => array(
                'name' => 'phone_mobile_search',
                'type' => 'varchar',
                'label' => 'LBL_PHONE_MOBILE_SEARCH',
                'default' => true,
            ),

            // array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            'name',
            'alias',
            'phone_mobile_search' => array(
                'name' => 'phone_mobile_search',
                'type' => 'varchar',
                'label' => 'LBL_PHONE_MOBILE_SEARCH',
                'default' => true,
            ),

            // array(
            //     'name' => 'assigned_user_id',
            //     'label' => 'LBL_ASSIGNED_TO',
            //     'type' => 'enum',
            //     'function' => array('name' => 'get_user_array', 'params' => array(false))
            // ),
        ),
    ),
);
