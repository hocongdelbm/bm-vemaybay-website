<?php


$module_name = 'EC_Post_Tags';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(
            array(
                array('name' => 'name', 'label' => 'LBL_NAME'),
                '',
            ),
            array(
                array('name' => 'description', 'label' => 'LBL_DESCRIPTION'),
            ),
        ),

    ),

);
