<?php
$module_name = 'EC_TaiKhoan';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'sotaikhoan' => array(
                'type' => 'varchar',
                'label' => 'LBL_SOTAIKHOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'sotaikhoan',
            ),
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'tentienganh' => array(
                'type' => 'varchar',
                'label' => 'LBL_TENTIENGANH',
                'width' => '10%',
                'default' => true,
                'name' => 'tentienganh',
            ),
            'taikhoantonghop' => array(
                'type' => 'varchar',
                'label' => 'LBL_TAIKHOANTONGHOP',
                'width' => '10%',
                'default' => true,
                'name' => 'taikhoantonghop',
            ),
            'nhomtaikhoan' => array(
                'type' => 'varchar',
                'label' => 'LBL_NHOMTAIKHOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'nhomtaikhoan',
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
            'name',
            array(
                'name' => 'assigned_user_id',
                'label' => 'LBL_ASSIGNED_TO',
                'type' => 'enum',
                'function' => array(
                    'name' => 'get_user_array',
                    'params' => array(
                        0 => false,
                    ),
                ),
            ),
        ),
    ),
);
