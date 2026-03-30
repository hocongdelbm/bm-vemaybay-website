<?php

$module_name = 'EC_Vouchers';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array ('file' => 'custom/jqueryui/plugins/jquery.number.min.js'),
        ),
    ),

    'panels' =>array (
        'default' => array (
            array (
                array(
                    'name' => 'duration',
                    'label' => 'LBL_DURATION',
                    'customCode' => '{$CUS_DURATION}',
                ),
            ),
            array (
                'reduce_amount',
                'reduce_percent',
            ),
            array(
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'displayParams' => array(
                        'cols' => 32,
                        'rows' => 4
                    )
                ),
                array()
            ),
        ),                                              
    ),

);
