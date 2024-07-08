<?php

$module_name = 'EC_WorkingOverTimes';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$STATUS_BTN}'),
                array('customCode' => '{$CHANGE_STATUS_BTN}'),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'lbl_detail_overtime' => array (
            array (
                array(
                    'name' => 'line_items',
                    'label' => 'LBL_LINE_ITEMS',
                    'customCode' => '{$CUS_LINE_ITEMS}',
                ),
            ),
            array (
                'description',
                'status',
            ),
            array (
                'type',
                'bonus_month'
            ),
            array (
                'assigned_user_name',
                array(
                    'name' => 'date_approved',
                    'label' => 'LBL_DATE_APPROVED',
                    'customCode' => '{$DATE_APPROVED}',
                ),
            ),
            array (
                array (
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ),
                array (
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ),
            ),
        ),
    ),
);
