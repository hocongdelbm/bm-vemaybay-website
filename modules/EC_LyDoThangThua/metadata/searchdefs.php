<?php

$module_name = 'EC_LyDoThangThua';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
        ),
        'advanced_search' => array(
            'name' =>
            array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'loailydo' =>
            array(
                'type' => 'enum',
                'studio' => 'visible',
                'label' => 'LBL_LOAILYDO',
                'width' => '10%',
                'default' => true,
                'name' => 'loailydo',
            ),
        ),
    ),
);
