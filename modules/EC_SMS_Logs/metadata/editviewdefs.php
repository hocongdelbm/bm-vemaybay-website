<?php

$module_name = 'EC_SMS_Logs';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'enctype' => 'multipart/form-data',
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array('file' => 'modules/EC_SMS_Logs/js/edit.js'),
        )
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'send_from',
                    'label' => 'LBL_SEND_FROM',
                    'customCode' => '{$CUSTOM_SEND_FROM}'
                ),
                array(
                    'name' => 'type',
                    'label' => 'LBL_TYPE',
                    'customCode' => '{$CUSTOM_TYPE}'
                )
            ),
            array(
                array(
                    'name' => 'message_type',
                    'label' => 'LBL_MESSAGE_TYPE',
                    'customCode' => '{$CUSTOM_MESSAGE_TYPE}'
                ),
                'send_date'
            ),
            array(
                array(
                    'name' => 'file',
                    'label' => 'LBL_FILE',
                ),
                'assigned_user_name'
            ),
            array(
                array(
                    'name' => 'content',
                    'label' => 'LBL_CONTENT',
                    'customCode' => '{$CUSTOM_CONTENT}'
                ),
                'description'
            )
        ),
    ),
);
