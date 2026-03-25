<?php


$module_name = '<module_name>';
$_object_name = '<_object_name>';
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

                array(
                    'name' => $_object_name . '_number',
                    'type' => 'readonly',
                ),
                'assigned_user_name',
            ),

            array(
                'priority',
            ),

            array(
                'resolution',
                'status',
            ),

            array(
                array('name' => 'name', 'displayParams' => array('size' => 60)),
            ),

            array(
                'description',
            ),

            array(
                'work_log',
            ),
        ),

    ),

);
