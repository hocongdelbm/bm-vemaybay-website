<?php


$module_name = 'EC_Post_Categories';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                'FIND_DUPLICATES',
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' =>
    array(
        'default' =>
        array(
            array(
                array('name' => 'name', 'label' => 'LBL_NAME'),
                array('name' => 'slug', 'label' => 'LBL_SLUG'),
            ),
            array(
                array('name' => 'parent_category', 'label' => 'LBL_PARENT_CATEGORY'),
                array('name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO_NAME'),
            ),
            array(
                array('name' => 'description', 'label' => 'LBL_DESCRIPTION'),
            ),
        )
    )
);

$viewdefs[$module_name]['DetailView']['panels']['LBL_RELATED_POSTS'] = array(
    array(
        array(
            'name' => 'posts_panel',
            'customCode' => '{$POSTS_PANEL}',
        ),
    ),
);
