<?php

$module_name = 'Emails';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'form' => array(
            'headerTpl' => 'modules/Emails/include/ComposeView/ComposeViewBlank.tpl',
            'footerTpl' => 'modules/Emails/include/ComposeView/ComposeViewBlank.tpl',
            'buttons' => array(),
            'includes' => array(
                array(
                    'file' => 'modules/Emails/include/DetailView/ImportView.js'
                ),
                array(
                    'file' => 'modules/Emails/include/DetailView/import.js'
                )
            ),
        ),
    ),
    'panels' => array(
        'LBL_EMAIL_INFORMATION' => array(
            array(
                'parent_name' => array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                )
            ),
            array(
                'parent_name'
            ),

        )
    )

);
