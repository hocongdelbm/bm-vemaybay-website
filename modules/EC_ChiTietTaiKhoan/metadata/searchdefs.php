<?php

$module_name = 'EC_ChiTietTaiKhoan';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'sotaikhoan' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_SOTAIKHOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'sotaikhoan',
            ),
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
        ),
        'advanced_search' => array(
            'sotaikhoan' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_SOTAIKHOAN',
                'width' => '10%',
                'default' => true,
                'name' => 'sotaikhoan',
            ),
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'parent_name' =>
            array(
                'type' => 'parent',
                'studio' => 'visible',
                'label' => 'LBL_FLEX_RELATE',
                'width' => '10%',
                'default' => true,
                'name' => 'parent_name',
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
            'location' =>
            array(
                'type' => 'relate',
                'studio' => 'visible',
                'label' => 'LBL_LOCATION',
                'width' => '10%',
                'default' => true,
                'name' => 'location',
            ),
            'assigned_user_name' =>
            array(
                'link' => 'assigned_user_link',
                'type' => 'relate',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'width' => '10%',
                'default' => true,
                'name' => 'assigned_user_name',
            ),
        ),
    ),
);
