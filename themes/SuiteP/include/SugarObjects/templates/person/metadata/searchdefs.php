<?php


$module_name = '<module_name>';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            array('name' => 'search_name', 'label' => 'LBL_NAME', 'type' => 'name'),
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
        ),
        'advanced_search' => array(
            'first_name',
            'last_name',
            'address_city',
            'created_by_name',
            'do_not_call',
            'email',
        ),
    ),
);
