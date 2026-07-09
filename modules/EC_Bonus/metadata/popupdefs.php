<?php
$popupMeta = array(
    'moduleMain' => 'EC_Bonus',
    'varName' => 'EC_Bonus',
    'orderBy' => 'ec_bonus.name',
    'whereClauses' => array(
        'name' => 'ec_bonus.name',
    ),
    'searchInputs' => array('ec_bonus_number', 'name', 'priority', 'status'),
    'listviewdefs' => array(
        'NAME' => array(
            'width' => '30%',
            'label' => 'LBL_NAME',
            'default' => true,
            'link' => true,
        ),
        'ASSIGNED_USER_NAME' => array(
            'width' => '20%',
            'label' => 'LBL_ASSIGNED_TO_NAME',
            'default' => true,
        ),
        'FLIGHT_DATE' => array(
            'width' => '20%',
            'label' => 'LBL_FLIGHT_DATE',
            'default' => true,
        ),
        'TOTAL_BONUS' => array(
            'width' => '20%',
            'label' => 'LBL_TOTAL_BONUS',
            'default' => true,
        ),
    ),
);
