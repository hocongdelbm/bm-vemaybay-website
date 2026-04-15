<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['QuickCreate'] = array(
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
                array('name' => 'assigned_user_name', 'displayParams' => array('required' => true)),
            ),

            array(
                'amount',
            ),

            array($_object_name . '_type', 'date_closed'),

            array('lead_source', array('name' => 'sales_stage', 'displayParams' => array('required' => true))),

            array(
                'next_step',
                'probability'
            ),

            array(
                'description',
                ''
            ),
        ),
    )

);
