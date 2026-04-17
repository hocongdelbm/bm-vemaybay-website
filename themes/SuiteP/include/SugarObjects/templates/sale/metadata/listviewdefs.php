<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$OBJECT_NAME = '<OBJECT_NAME>';
$listViewDefs[$module_name] = array(
    'NAME' => array(
        'width' => '30',
        'label' => 'LBL_LIST_SALE_NAME',
        'link' => true,
        'default' => true
    ),

    'SALES_STAGE' => array(
        'width' => '10',
        'label' => 'LBL_LIST_SALE_STAGE',
        'default' => true
    ),
    'AMOUNT_USDOLLAR' => array(
        'width' => '10',
        'label' => 'LBL_LIST_AMOUNT',
        'align' => 'right',
        'default' => true,
        'currency_format' => true,
    ),
    $OBJECT_NAME . '_TYPE' => array(
        'width' => '15',
        'label' => 'LBL_TYPE'
    ),
    'LEAD_SOURCE' => array(
        'width' => '15',
        'label' => 'LBL_LEAD_SOURCE'
    ),
    'NEXT_STEP' => array(
        'width' => '10',
        'label' => 'LBL_NEXT_STEP'
    ),
    'PROBABILITY' => array(
        'width' => '10',
        'label' => 'LBL_PROBABILITY'
    ),
    'DATE_CLOSED' => array(
        'width' => '10',
        'label' => 'LBL_LIST_DATE_CLOSED',
        'default' => true
    ),
    'DATE_ENTERED' => array(
        'width' => '10',
        'label' => 'LBL_DATE_ENTERED'
    ),
    'CREATED_BY_NAME' => array(
        'width' => '10',
        'label' => 'LBL_CREATED'
    ),
    'ASSIGNED_USER_NAME' => array(
        'width' => '5',
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true
    ),
    'MODIFIED_BY_NAME' => array(
        'width' => '5',
        'label' => 'LBL_MODIFIED'
    )
);
