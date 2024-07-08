<?php

$module_name = 'EC_LoaiChungTu';
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
            'maloai' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_MALOAI',
                'width' => '10%',
                'default' => true,
                'name' => 'maloai',
            ),
        ),
        'advanced_search' =>
        array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'maloai' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_MALOAI',
                'width' => '10%',
                'default' => true,
                'name' => 'maloai',
            ),
            'taikhoanno' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_TAIKHOANNO',
                'width' => '10%',
                'default' => true,
                'name' => 'taikhoanno',
            ),
            'taikhoanco' =>
            array(
                'type' => 'varchar',
                'label' => 'LBL_TAIKHOANCO',
                'width' => '10%',
                'default' => true,
                'name' => 'taikhoanco',
            ),
        ),
    ),
);
