<?php

$module_name = 'EC_WorkingOverTimes';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' =>
        array(
            array(
                'file' => 'modules/EC_WorkingOverTimes/js/EC_WorkingOverTimes.js',
            ),
        ),
    ),

    'panels' => array(
        'LBL_PANEL1' => array(
            array(
                array(
                    'name' => 'line_items',
                    'label' => 'LBL_LINE_ITEMS',
                    'customCode' => '{$line_items}',
                ),
            ),
            array(
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'cols' => 42
                    ),
                ),
                'assigned_user_name',
            ),
            array(
                'type',
                'bonus_month',
            ),
        )
    ),
);
