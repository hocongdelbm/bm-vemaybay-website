<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['ACLRoles'] = array(
    // sets up which panels to show, in which order, and with what linked_fields
    'subpanel_setup' => array(
        'users' => array(
            'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectUsersButton', 'mode' => 'MultiSelect', 'popup_module' => 'Users', 'filter_out_is_admin' => true,),),
            'order' => 20,
            'module' => 'Users',
            'sort_by' => 'user_name',
            'sort_order' => 'asc',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'users',
            'add_subpanel_data' => 'user_id',
            'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
        ),
        'securitygroups' => array(
            'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'SecurityGroups', 'mode' => 'MultiSelect'),),
            'order' => 900,
            'sort_by' => 'name',
            'sort_order' => 'asc',
            'module' => 'SecurityGroups',
            'refresh_page' => 1,
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'SecurityGroups',
            'add_subpanel_data' => 'securitygroup_id',
            'title_key' => 'LBL_SECURITYGROUPS_SUBPANEL_TITLE',
        ),
    ),
);
$layout_defs['UserRoles'] = array(
    // sets up which panels to show, in which order, and with what linked_fields
    'subpanel_setup' => array(
        'aclroles' => array(
            'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectUsersButton', 'mode' => 'MultiSelect', 'popup_module' => 'ACLRoles', 'filter_out_is_admin' => true,),),
            'order' => 20,
            'module' => 'ACLRoles',
            'sort_by' => 'name',
            'sort_order' => 'asc',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'aclroles',
            'add_subpanel_data' => 'role_id',
            'title_key' => 'LBL_ROLES_SUBPANEL_TITLE',
        ),
    ),

);
