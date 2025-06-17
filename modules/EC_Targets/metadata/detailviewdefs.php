<?php

$module_name = 'EC_Targets';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array(
        'form' => array(
            'buttons' => array(
                'EDIT',
                'DUPLICATE',
                'DELETE',
                array('customCode' => '{$STATUS_BTN}'),
                array('customCode' => '{$APPROVED_BTN}'),
                array('customCode' => '{$CHANGE_STATUS_BTN}'),
            )
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array (
        'default' => array (
            array (
                'name',
                'target_type',
            ),
            array(
                'year',
                'assigned_user_name',
            ),
            array (
                'description',
                array(
                    'name' => 'approved_date',
                    'label' => 'LBL_APPROVED_DATE',
                    'customCode' => '{$CUS_APPROVED_BY}'
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
        'LBL_PANEL1' =>
        array (
            array(
                'target_month1',
                'target_month2',
            ),
            array(
                'target_month3',
                'target_month4',
            ),
            array(
                'target_month5',
                'target_month6',
            ),
            array(
                'target_month7',
                'target_month8',
            ),
            array(
                'target_month9',
                'target_month10',
            ),
            array(
                'target_month11',
                'target_month12',
            ),
            array(
                'target_quarter1',
                'target_quarter2',
            ),
            array(
                'target_quarter3',
                'target_quarter4',
            ),
            array(
                'target_year',
            ),
        ),  
    ),
);
