<?php

$module_name = 'EC_LoginAudit';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'modified_by_name' =>
            array(
                'width' => '10%',
                'label' => 'LBL_MODIFIED',
                'default' => true,
                'name' => 'modified_by_name',
            ),
            'ip_address' =>
            array(
                'width' => '10%',
                'label' => 'LBL_IP_ADDRESS',
                'default' => true,
                'name' => 'ip_address',
            ),
            'result' =>
            array(
                'width' => '10%',
                'label' => 'LBL_RESULT',
                'default' => true,
                'name' => 'result',
            ),
        ),
        'advanced_search' =>
        array(
            'modified_by_name' =>
            array(
                'width' => '10%',
                'label' => 'LBL_MODIFIED',
                'default' => true,
                'name' => 'modified_by_name',
            ),
            'date_entered' =>
            array(
                'width' => '10%',
                'label' => 'LBL_DATE_ENTERED',
                'default' => true,
                'name' => 'date_entered',
            ),
            'is_admin' =>
            array(
                'width' => '10%',
                'label' => 'LBL_IS_ADMIN',
                'default' => true,
                'name' => 'is_admin',
            ),
            'result' =>
            array(
                'width' => '10%',
                'label' => 'LBL_RESULT',
                'default' => true,
                'name' => 'result',
            ),
            'platform' =>
            array(
                'width' => '10%',
                'label' => 'LBL_PLATFORM',
                'default' => true,
                'name' => 'platform',
            ),
            'ip_address' =>
            array(
                'width' => '10%',
                'label' => 'LBL_IP_ADDRESS',
                'default' => true,
                'name' => 'ip_address',
            ),
            // 'assigned_user_id' =>
            // array(
            //     'name' => 'assigned_user_id',
            //     'type' => 'enum',
            //     'label' => 'LBL_ASSIGNED_TO',
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
