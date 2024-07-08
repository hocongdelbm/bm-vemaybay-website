<?php
$module_name = 'EC_NhomTaiKhoan';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'manhom' => array(
                'type' => 'varchar',
                'label' => 'LBL_MANHOM',
                'width' => '10%',
                'default' => true,
                'name' => 'manhom',
            ),
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'tinhchat' => array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_TINHCHAT',
                'width' => '10%',
                'name' => 'tinhchat',
            ),
            'current_user_only' => array(
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ),
        ),

        'advanced_search' => array(
            'manhom' => array(
                'type' => 'varchar',
                'label' => 'LBL_MANHOM',
                'width' => '10%',
                'default' => true,
                'name' => 'manhom',
            ),
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'tinhchat' => array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_TINHCHAT',
                'width' => '10%',
                'name' => 'tinhchat',
            ),
            'chitiettheo' => array(
                'type' => 'bool',
                'label' => 'LBL_CHITIETTHEO',
                'width' => '10%',
                'default' => true,
                'name' => 'chitiettheo',
            ),
            'ds_chitiettheo' => array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_DS_CHITIETTHEO',
                'width' => '10%',
                'default' => true,
                'name' => 'ds_chitiettheo',
            ),
            'loaidoituong' => array(
                'type' => 'enum',
                'default' => true,
                'studio' => 'visible',
                'label' => 'LBL_LOAIDOITUONG',
                'width' => '10%',
                'name' => 'loaidoituong',
            ),
            'assigned_user_id' => array(
                'name' => 'assigned_user_id',
                'label' => 'LBL_ASSIGNED_TO',
                'type' => 'enum',
                'function' => array(
                    'name' => 'get_user_array',
                    'params' => array(
                        0 => false,
                    ),
                ),
                'default' => true,
                'width' => '10%',
            ),
        ),
    ),
);
