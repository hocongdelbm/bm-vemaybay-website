<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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
            array('name', array('name' => 'amount', 'label' => '{$MOD.LBL_AMOUNT} ({$CURRENCY})'),),
            array('date_closed', 'sales_stage'),
            array($_object_name . '_type', 'next_step'),
            array(
                'lead_source',
                array(
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}'
                ),
            ),
            array(
                'probability'
            ),
            array(
                'assigned_user_name',
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}'
                )
            ),
            array(array('name' => 'description', 'nl2br' => true)),
        )
    )
);
