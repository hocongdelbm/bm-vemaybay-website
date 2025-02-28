<?php
$module_name = 'EC_Messages';
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
    ),

    'panels' => array(
        'default' => array(
            array(
                array(
                    'name' => 'type',
                    'label' => 'LBL_TYPE',
                    'customCode' => '{$CUSTOM_TYPE}'
                ),
                array(
                    'name' => 'category',
                    'label' => 'LBL_CATEGORY',
                    'customCode' => '{$CUSTOM_CATEGORY}'
                ),
            ),
            array(
                array(
                    'name' => 'send_from',
                    'label' => 'LBL_SEND_FROM',
                    'customCode' => '{$CUSTOM_SEND_FROM}'
                ),
                'send_time'
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
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'customCode' => '{$CUSTOM_DESCRIPTION}'
                ),
            )
        ),
    ),
);
