<?php

$module_name = 'EC_Targets';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name', 
            'year',
        ),
        'advanced_search' => array(
            'name', 
            'year',
            'assigned_user_name',
            'target_type',
            'status',
        ),
    ),
);
