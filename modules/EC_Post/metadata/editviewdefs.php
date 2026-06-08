<?php

$module_name = 'EC_Post';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(

        'LBL_PANEL_DEFAULT' => array(

            array(
                array(
                    'name' => 'post_title',
                    'label' => 'LBL_POST_TITLE'
                ),
                array(
                    'name' => 'post_status',
                    'label' => 'LBL_POST_STATUS'
                ),
            ),
            array(
                array('name' => 'post_type', 'label' => 'LBL_POST_TYPE'),
                array('name' => 'slug', 'label' => 'LBL_SLUG'),
            ),

            array(
                'published_at',
                'thumbnail_url',
            ),

            array(
                array(
                    'name' => 'author_id',
                    'label' => 'LBL_AUTHOR',
                    'customCode' => '{$AUTHOR_FIELD}',
                ),
                array(
                    'name' => 'post_parent_id',
                    'label' => 'LBL_POST_PARENT',
                ),
            ),

            array(
                array(
                    'name' => 'categories_selector',
                    'label' => 'LBL_CATEGORIES_SELECTOR',
                    'customCode' => '{$CATEGORIES_SELECTOR}'
                ),
                array(
                    'name' => 'tags_selector',
                    'label' => 'LBL_TAGS_SELECTOR',
                    'customCode' => '{$TAGS_SELECTOR}',
                ),
            ),

        ),

        'LBL_PANEL_CONTENT' => array(

            array(
                array(
                    'name' => 'post_content',
                    'displayParams' => array(
                        'cols' => 80,
                        'rows' => 15,
                    ),
                ),
            ),

        ),

    ),

);
