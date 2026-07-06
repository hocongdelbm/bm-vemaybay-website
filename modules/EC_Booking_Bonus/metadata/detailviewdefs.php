<?php
$module_name = 'EC_Booking_Bonus';
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
                array('booking_name', 'assigned_user_name'),
                array('flight_date', 'is_inter'),
                array('ticket_qty', 'avg_profit'),
                array('total_revenue', 'total_cost'),
                array('total_profit', 'bonus_per_ticket'),
                array('min_threshold_value', 'extra_threshold_value'),
                array('bonus_percent', 'extra_bonus_percent'),
                array('total_direct_bonus', 'total_indirect_bonus'),
                array('total_indirect_kpi', 'kpi'),
                array('direct_bonus', 'indirect_bonus'),
                array('total_bonus', 'description'),
                array('date_entered', 'date_modified'),
            ),
        ),
    ),
);
