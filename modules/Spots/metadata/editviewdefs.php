<?php
$module_name = 'Spots';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(

        'form' => array(
            'hidden' => array(
                0 => '<input type="hidden" name="config" value="{$fields.config.value}">',
            ),
        ),

        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
        // 'includes' => array(
        //     0 => array(
        //         'file' => 'modules/Favorites/favorites.js'
        //     )
        // )
    ),

    'panels' => array(
        'default' => array(

            array(
                'name', 'type',
            ),

            array(
                'configurationGUI',
            ),
        ),

    ),

);
