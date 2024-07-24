<?php

$module_name = 'EC_Customer';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            'phone'
        ),
        'advanced_search' => array(
            'name',
            'phone',
            'source',
            'birthday',
            'type',
        ),
    ),
);
