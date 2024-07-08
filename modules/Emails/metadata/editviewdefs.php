<?php

$module_name = 'Emails';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),
    'panels' => array(

        'LBL_EMAIL_INFORMATION' => array(
            array(
                'assigned_user_name' => array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                )
            ),
            array(
                'parent_name'
            ),
            array(
                'category_id',
            ),
        )
    )

);
