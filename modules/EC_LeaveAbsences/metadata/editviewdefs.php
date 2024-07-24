<?php
$module_name = 'EC_LeaveAbsences';
$viewdefs[$module_name]['EditView'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
        'includes' => array(
            array (
                'file' => 'modules/EC_LeaveAbsences/js/view.edit.js',
              ),
        )
    ),
    'panels' => array (
        'default' => 
        array (
            array (
                'from_date',
                'to_date',
            ),
            array(
                'assigned_user_name',
                'absence_type',
            ),
            array(
                array(
                    'name' => 'absence_days',
                    'label' => 'LBL_ABSENCE_DAYS',
                    'customCode' => '{$CUS_ABSENCE_DAYS}',
                ),
                'reason',
            ),
        ),                                                
    ),

);
