<?php
$module_name = 'EC_Post';
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
    'panels' => array(
        'LBL_PANEL_DEFAULT' => array(        
            array(
                array('name' => 'id',         'label' => 'LBL_ID'),
                array('name' => 'post_title', 'label' => 'LBL_POST_TITLE'),
            ),
            array(
                array('name' => 'post_status', 'label' => 'LBL_POST_STATUS'),
                array('name' => 'post_type',   'label' => 'LBL_POST_TYPE'),
            ),
            array(
                array('name' => 'slug',         'label' => 'LBL_SLUG'),
                array('name' => 'published_at', 'label' => 'LBL_PUBLISHED_AT'),
            ),
            array(
                array(
                    'name'       => 'thumbnail_url',
                    'label'      => 'LBL_THUMBNAIL_URL',
                    'customCode' => '{$CUSTOM_THUMB}',
                ),
                array('name' => 'author_id',     'label' => 'LBL_AUTHOR'),
            ),
            array(
                array('name' => 'post_parent_id',     'label' => 'LBL_POST_PARENT'),
                array('name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO'),
            ),
            array(
                array(
                    'name'       => 'categories_display',
                    'label'      => 'LBL_CATEGORIES',
                    'customCode' => '{$CATEGORIES_DISPLAY}'
                ),
                array(
                    'name'       => 'tags_display',
                    'label'      => 'LBL_TAGS',
                    'customCode' => '{$TAGS_DISPLAY}'
                ),
            ),
        ),
        'LBL_PANEL_CONTENT' => array(
            array(
                array(
                    'name' => 'post_content',
                    'label' => 'LBL_POST_CONTENT',
                    // use nofilter to render raw HTML from the WYSIWYG editor
                    'customCode' => '{$POST_CONTENT nofilter}'
                ),
            ),
        ),
    ),
);