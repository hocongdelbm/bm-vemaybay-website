<?php

$module_name = 'EC_Location';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'company' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_COMPANY',
                'width' => '10%',
                'default' => true,
                'name' => 'company',
            ),
            // 'current_user_only' =>
            // array(
            //     'name' => 'current_user_only',
            //     'label' => 'LBL_CURRENT_USER_FILTER',
            //     'type' => 'bool',
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
        'advanced_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'company' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_COMPANY',
                'width' => '10%',
                'default' => true,
                'name' => 'company',
            ),
            // 'assigned_user_id' =>
            // array(
            //     'name' => 'assigned_user_id',
            //     'label' => 'LBL_ASSIGNED_TO',
            //     'type' => 'enum',
            //     'function' =>
            //     array(
            //         'name' => 'get_user_array',
            //         'params' =>
            //         array(
            //             0 => false,
            //         ),
            //     ),
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
    ),
);
