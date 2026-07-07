<?php
$module_name = 'EC_Bonus';
$viewdefs[$module_name] = array(
    'DetailView' => array(
        'templateMeta' => array(
            'form' => array('buttons' => array('EDIT', 'DUPLICATE', 'DELETE')),
            'maxColumns' => '2',
            'widths' => array(
                array('label' => '10', 'field' => '30'),
                array('label' => '10', 'field' => '30'),
            ),
        ),
        'panels' => array(
            'default' => array(
                array('source_name', 'assigned_user_name'),
                array('bonus_time', 'kpi'),
                array('direct_bonus', 'indirect_bonus'),
                array('description'),
                array('date_entered', 'date_modified'),
            ),
        ),
    ),
);
