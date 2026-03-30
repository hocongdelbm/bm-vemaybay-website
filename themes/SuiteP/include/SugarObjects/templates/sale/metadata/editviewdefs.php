<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'javascript' => '{$PROBABILITY_SCRIPT}',
    ),
    'panels' => array(
        'lbl_sale_information' => array(
            array(
                'name',
                'currency_id',
            ),
            array(
                $_object_name . '_type',
                'amount',
            ),
            array('lead_source', 'date_closed'),
            array(
                'sales_stage',
            ),

            array(
                array('name' => 'assigned_user_name', 'displayParams' => array('required' => true)),
                'next_step',
            ),
            array(
                'probability',
                ''
            ),

            array(
                'description'
            ),
        ),
    )

);
