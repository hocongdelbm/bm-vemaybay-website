<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$_module_name = '<_module_name>';
$searchdefs[$module_name] = array(
    'templateMeta' => array(
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => array('label' => '10', 'field' => '30'),
    ),
    'layout' => array(
        'basic_search' => array(
            'name',
            array('name' => 'current_user_only', 'label' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
            array(
                'name' => 'open_only',
                'label' => 'LBL_OPEN_ITEMS',
                'type' => 'bool',
                'default' => false,
                'width' => '10%'
            ),
        ),
        'advanced_search' => array(
            'name',
            'amount',
            'date_closed',
            'probability',
            'next_step',
            'lead_source',
            'sales_stage',
            array(
                'name' => 'assigned_user_id',
                'type' => 'enum',
                'label' => 'LBL_ASSIGNED_TO',
                'function' => array('name' => 'get_user_array', 'params' => array(false))
            ),
        ),
    ),
);
