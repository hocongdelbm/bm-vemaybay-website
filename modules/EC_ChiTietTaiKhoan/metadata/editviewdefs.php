<?php

$module_name = 'EC_ChiTietTaiKhoan';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' =>   array(
            array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ),
                array(
                    'name' => 'sotaikhoan',
                    'label' => 'LBL_SOTAIKHOAN',
                ),
            ),
            array(
                array(
                    'name' => 'dunodau',
                    'label' => 'LBL_DUNODAU',
                ),
                array(
                    'name' => 'ducodau',
                    'label' => 'LBL_DUCODAU',
                ),
            ),
            array(
                array(
                    'name' => 'company',
                    'studio' => 'visible',
                    'label' => 'LBL_COMPANY',
                ),
                array(
                    'name' => 'location',
                    'studio' => 'visible',
                    'label' => 'LBL_LOCATION',
                ),
            ),
            array(
                array(
                    'name' => 'parent_name',
                    'studio' => 'visible',
                    'label' => 'LBL_FLEX_RELATE',
                ),
                array(
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ),
            ),
            array(
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array()
            )
        ),
    ),
);