<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'EC_Flight_Bookings';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '10%',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true,
    ),
    'CONTACT_NAME' => array(
        'label' => 'LBL_CONTACT_NAME',
        'width' => '12%',
        'default' => true,
    ),
    'IS_REFERENCE' => array(
        'label' => 'LBL_IS_REFERENCE',
        'width' => '12%',
        'default' => false,
    ),
    'IS_PRIOR' => array(
        // 'label' => 'LBL_IS_PRIOR',
        'label' => 'Ký hiệu',
        'width' => '15%',
        'type' => 'text',
        'default' => true,
    ),
    'PHONE' => array(
        'label' => 'LBL_PHONE',
        'width' => '10%',
        'default' => true,
    ),
    'TOTAL_QTY' => array(
        'label' => 'LBL_TOTAL_QTY',
        'width' => '10%',
        'default' => true,
        'align' => 'center'
    ),
    'EMAIL' => array(
        'label' => 'LBL_EMAIL',
        'width' => '10%',
        'default' => true,
    ),
    'DESCRIPTION' => array(
        'label' => 'LBL_DESCRIPTION',
        'width' => '35%',
        'default' => true,
    ),
    'TOTAL_AMOUNT' => array(
        'label' => 'LBL_TOTAL_AMOUNT',
        'width' => '10%',
        'default' => true,
        'align' => 'right'
    ),
    'BOOKING_STATUS' => array(
        'default' => true,
        'label' => 'LBL_BOOKING_STATUS',
        'width' => '8%',
    ),
    'RECALL_C' => array(
        'width' => '10%',
        'label' => 'LBL_RECALL_C',
        'default' => true,
        'align' => 'center'
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '9%',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true,
    ),
    'IP_ADDRESS' => array(),
    'DATE_ENTERED' => array(
        'label' => 'LBL_DATE_ENTERED',
        'width' => '15%',
        'default' => true,
    ),
);

// Show/hide IP column
if(in_array($GLOBALS['current_user']->id, [
    // '1', // ducpham
    'e3bbb3e5-6660-0bf7-8976-54869c4ee609', // ksnb
    '4f4d7a13-4171-9b7d-251c-64dd8f9885e4', // pandapo
    '168889bb-54c2-59c7-8b3f-649102530d3c', // haihung
    'dd9c1488-60b4-22e5-545b-69bb6d12a6b0', // thanhdat
    '9eb0f65f-a9f6-65bb-1985-637ca8511491', // trinhdoan
    '622ecf27-f729-7187-7e27-6520e0dab882', // quangnd
    '5ac1d89e-0258-7237-0763-6a20dab448b4', // dahy
])) {
    $listViewDefs[$module_name]['IP_ADDRESS'] = [
        'width' => '10%',
        'label' => 'LBL_IP_ADDRESS',
        'default' => true,
    ];
}
else {
    unset($listViewDefs[$module_name]['IP_ADDRESS']);
}
