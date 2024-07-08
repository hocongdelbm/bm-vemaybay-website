<?php

$module_name = 'EC_Targets';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array (
              'file' => 'custom/jqueryui/plugins/jquery.number.min.js',
            ),
        ),        
    ),

    'panels' => array (
        'default' => 
        array (
            array (
                'target_type',
                array(
                    'name' => 'year',
                    'label' => 'LBL_YEAR',
                    'customCode' => '{$CUS_YEAR}',
                ),
            ),
            array (
                'description',
                'assigned_user_name',
            ),
        ),
        'LBL_PANEL1' =>
        array(
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
        ),                                               
    ),   

);
