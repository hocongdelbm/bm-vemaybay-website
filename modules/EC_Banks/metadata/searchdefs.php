<?php
$module_name = 'EC_Banks';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'short_name' => array(
                'name'  => 'short_name',
                'type'  => 'varchar',
                'label' => 'LBL_SHORT_NAME',
                'width' => '10%',
                'default' => true,
            ),
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'english_name' => array(
                'name'  => 'english_name',
                'type'  => 'varchar',
                'label' => 'LBL_ENGLISH_NAME',
                'width' => '10%',
                'default' => true,
            ),
        ),

        'advanced_search' => array(
            'short_name' => array(
                'name'  => 'short_name',
                'type'  => 'varchar',
                'label' => 'LBL_SHORT_NAME',
                'width' => '10%',
                'default' => true,
            ),
            'name' => array(
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ),
            'english_name' => array(
                'name'  => 'english_name',
                'type'  => 'varchar',
                'label' => 'LBL_ENGLISH_NAME',
                'width' => '10%',
                'default' => true,
            ),
            'headquarters' => array(
                'name'  => 'headquarters',
                'type'  => 'text',
                'label' => 'LBL_HEADQUARTERS',
                'studio' => 'visible',
                'width' => '10%',
                'default' => true,
            ),
            'unfollow' => array(
                'name'  => 'unfollow',
                'type'  => 'bool',
                'label' => 'LBL_UNFOLLOW',
                'width' => '10%',
                'default' => true,
            ),
        ),
    ),
);
