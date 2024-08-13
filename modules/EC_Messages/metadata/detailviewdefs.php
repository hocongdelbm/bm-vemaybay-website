<?php
$module_name = 'EC_Messages';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                // 'DUPLICATE',
                'DELETE',
                // 'FIND_DUPLICATES',
            )
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
                'name',
                'type',
            ),
            array(
                'category',
                array(
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'customCode' => '{$CUSTOM_STATUS}'
                )
            ),
            array(
                array(
                    'name' => 'send_from',
                    'label' => 'LBL_SEND_FROM',
                    'customCode' => '{$CUSTOM_SEND_FROM}'
                ),
                array(
                    'name' => 'send_to',
                    'label' => 'LBL_SEND_TO',
                    'customCode' => '{$CUSTOM_SEND_TO}'
                )
            ),
            array(
                'send_time',
                'file',
            ),
            array(
                array(
                    'name' => 'parent',
                    'label' => 'LBL_PARENT_DETAIL',
                    'customCode' => '{$CUSTOM_PARENT}'
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
            ),
            array(
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array(
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        )
    )
);
