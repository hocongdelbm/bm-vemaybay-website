<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = '<module_name>';
$subpanel_layout = array(
    'top_buttons' => array(
        array('widget_class' => 'SubPanelTopCreateButton'),
        array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => $module_name),
    ),

    'where' => '',

    'list_fields' => array(
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_LIST_SALE_NAME',
            'widget_class' => 'SubPanelDetailViewLink',
            'width' => '40%',
        ),
        'sales_stage' => array(
            'name' => 'sales_stage',
            'vname' => 'LBL_LIST_SALE_STAGE',
            'width' => '15%',
        ),
        'date_closed' => array(
            'name' => 'date_closed',
            'vname' => 'LBL_LIST_DATE_CLOSED',
            'width' => '15%',
        ),
        'amount' => array(
            'vname' => 'LBL_LIST_AMOUNT',
            'width' => '15%',
        ),
        'assigned_user_name' => array(
            'name' => 'assigned_user_name',
            'vname' => 'LBL_LIST_ASSIGNED_TO_NAME',
            'widget_class' => 'SubPanelDetailViewLink',
            'target_record_key' => 'assigned_user_id',
            'target_module' => 'Employees',
        ),
        'edit_button' => array(
            'vname' => 'LBL_EDIT_BUTTON',
            'widget_class' => 'SubPanelEditButton',
            'module' => $module_name,
            'width' => '4%',
        ),
        'amount_usdollar' => array(
            'usage' => 'query_only',
        ),
        'remove_button' => array(
            'vname' => 'LBL_REMOVE',
            'widget_class' => 'SubPanelRemoveButton',
            'module' => $module_name,
            'width' => '5%',
        ),
    ),
);
