<?php
$module_name = 'EC_ChuyenTienNoiBo';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => 
        array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'tutknganhang' => array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_TUTKNGANHANG',
                'width' => '10%',
                'default' => true,
                'name' => 'tutknganhang',
            ),
            'dentknganhang' => array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_DENTKNGANHANG',
                'width' => '10%',
                'default' => true,
                'name' => 'dentknganhang',
            ),
            'sotien' => array(
                'type' => 'currency',
                'label' => 'LBL_SOTIEN',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'sotien',
            ),
            'current_user_only' => array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ),
        ),

        'advanced_search' => 
        array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'sotien' => array(
                'type' => 'currency',
                'label' => 'LBL_SOTIEN',
                'currency_format' => true,
                'width' => '10%',
                'default' => true,
                'name' => 'sotien',
            ),
            'tutknganhang' => array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_TUTKNGANHANG',
                'width' => '10%',
                'default' => true,
                'name' => 'tutknganhang',
            ),
            'dentknganhang' => array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_DENTKNGANHANG',
                'width' => '10%',
                'default' => true,
                'name' => 'dentknganhang',
            ),
            'ngaychungtu' => array(
                'type' => 'date',
                'label' => 'LBL_NGAYCHUNGTU',
                'width' => '10%',
                'default' => true,
                'name' => 'ngaychungtu',
            ),
            'ngayhachtoan' => array(
                'type' => 'date',
                'label' => 'LBL_NGAYHACHTOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'ngayhachtoan',
            ),
            // 'tudiadiem' => array(
            //     'type' => 'enum',
            //     'default' => true,
            //     'studio' => 'visible',
            //     'label' => 'LBL_TUDIADIEM',
            //     'width' => '10%',
            //     'name' => 'tudiadiem',
            // ),
            // 'dendiadiem' => array(
            //     'type' => 'enum',
            //     'default' => true,
            //     'studio' => 'visible',
            //     'label' => 'LBL_DENDIADIEM',
            //     'width' => '10%',
            //     'name' => 'dendiadiem',
            // ),
            // 'assigned_user_id' => array(
            //     'name' => 'assigned_user_id',
            //     'label' => 'LBL_ASSIGNED_TO',
            //     'type' => 'enum',
            //     'function' => array(
            //         'name' => 'get_user_array',
            //         'params' => array(
            //             0 => false,
            //         ),
            //     ),
            //     'default' => true,
            //     'width' => '10%',
            // ),
        ),
    ),
);
