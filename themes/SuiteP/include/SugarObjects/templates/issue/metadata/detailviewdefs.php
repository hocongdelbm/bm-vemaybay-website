<?php


$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array('buttons' => array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES',)),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(

            array(
                $_object_name . '_number',
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

            array(

                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                ),
            ),

            array(
                'description',
            ),

            array(
                'work_log',
            ),
        )
    )
);
