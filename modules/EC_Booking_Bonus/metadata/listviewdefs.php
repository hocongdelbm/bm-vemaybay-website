<?php
$module_name = 'EC_Booking_Bonus';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '15%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '10%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true,
    ),
    'FLIGHT_DATE' => array(
        'width' => '10%',
        'label' => 'LBL_FLIGHT_DATE',
        'default' => true,
    ),
    'TICKET_QTY' => array(
        'width' => '5%',
        'label' => 'LBL_TICKET_QTY',
        'default' => true,
    ),
    'KPI' => array(
        'width' => '5%',
        'label' => 'LBL_KPI',
        'default' => true,
    ),
    'DIRECT_BONUS' => array(
        'width' => '10%',
        'label' => 'LBL_DIRECT_BONUS',
        'default' => true,
    ),
    'INDIRECT_BONUS' => array(
        'width' => '10%',
        'label' => 'LBL_INDIRECT_BONUS',
        'default' => true,
    ),
    'TOTAL_BONUS' => array(
        'width' => '10%',
        'label' => 'LBL_TOTAL_BONUS',
        'default' => true,
    ),
    'DATE_MODIFIED' => array(
        'width' => '10%',
        'label' => 'LBL_DATE_MODIFIED',
        'default' => false,
    ),
);
