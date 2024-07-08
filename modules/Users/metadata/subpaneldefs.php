<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Users'] = array(
    // default subpanel provided by this SugarBean
    'subpanel_setup' => array(
        'aclroles' => array(
            'module' => 'ACLRoles',
            'order' => 30,
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'admin',
            'title_key' => 'LBL_ROLES_SUBPANEL_TITLE',
            'get_subpanel_data' => 'aclroles',
            'add_subpanel_data' => 'aclroles',
        ),
    ),
    'default_subpanel_define' => array(
        'subpanel_title' => 'LBL_DEFAULT_SUBPANEL_TITLE',
        'sort_by' => 'name',
        'sort_order' => 'asc',
        'top_buttons' => array(
            array('widget_class' => 'SubPanelTopCreateButton'),
            array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Users', 'mode' => 'MultiSelect'),
        ),
        'list_fields' => array(
            'Users' => array(
                'columns' => array(
                    array(
                        'name' => 'first_name',
                        'usage' => 'query_only',
                    ),
                    array(
                        'name' => 'last_name',
                        'usage' => 'query_only',
                    ),
                    array(
                        'name' => 'name',
                        'vname' => 'LBL_LIST_NAME',
                        'widget_class' => 'SubPanelDetailViewLink',
                        'module' => 'Users',
                        'width' => '25%',
                    ),
                    array(
                        'name' => 'user_name',
                        'vname' => 'LBL_LIST_USER_NAME',
                        'width' => '25%',
                    ),
                    array(
                        'name' => 'email1',
                        'vname' => 'LBL_LIST_EMAIL',
                        'width' => '25%',
                    ),
                    array(
                        'name' => 'phone_work',
                        'vname' => 'LBL_LIST_PHONE',
                        'width' => '21%',
                    ),
                    array(
                        'name' => 'nothing',
                        'widget_class' => 'SubPanelRemoveButton',
                        'module' => 'Users',
                        'width' => '4%',
                        'linked_field' => 'users',
                    ),
                ),
            ),
        ),
    ),
);
$layout_defs['UserRoles'] = array(
    // sets up which panels to show, in which order, and with what linked_fields
    'subpanel_setup' => array(
        'aclroles' => array(
            'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'ACLRoles', 'mode' => 'MultiSelect'),),
            'order' => 20,
            'sort_by' => 'name',
            'sort_order' => 'asc',
            'module' => 'ACLRoles',
            'refresh_page' => 1,
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'aclroles',
            'add_subpanel_data' => 'role_id',
            'title_key' => 'LBL_ROLES_SUBPANEL_TITLE',
        ),
    ),
);
global $current_user;
if ($current_user->isAdminForModule('Users')) {
    $layout_defs['UserRoles']['subpanel_setup']['aclroles']['subpanel_name'] = 'admin';
} else {
    $layout_defs['UserRoles']['subpanel_setup']['aclroles']['top_buttons'] = array();
}

$layout_defs['UserEAPM'] = array(
    'subpanel_setup' => array(
        'eapm' => array(
            'order' => 30,
            'module' => 'EAPM',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'eapm',
            'add_subpanel_data' => 'assigned_user_id',
            'title_key' => 'LBL_EAPM_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
            ),
        ),

    ),
);

global $modules_exempt_from_availability_check;
$modules_exempt_from_availability_check['SecurityGroups'] = 'SecurityGroups';

$layout_defs['Users']['subpanel_setup']['securitygroups'] = array(
    'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'SecurityGroups', 'mode' => 'MultiSelect'),),
    'order' => 100,
    'sort_by' => 'name',
    'sort_order' => 'asc',
    'module' => 'SecurityGroups',
    'refresh_page' => 1,
    'subpanel_name' => 'default',
    'get_subpanel_data' => 'SecurityGroups',
    'add_subpanel_data' => 'securitygroup_id',
    'title_key' => 'LBL_SECURITYGROUPS_SUBPANEL_TITLE',
);
$layout_defs['UserRoles']['subpanel_setup']['securitygroups'] = array(
    'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'SecurityGroups', 'mode' => 'MultiSelect'),),
    'order' => 100,
    'sort_by' => 'name',
    'sort_order' => 'asc',
    'module' => 'SecurityGroups',
    'refresh_page' => 1,
    'subpanel_name' => 'default',
    'get_subpanel_data' => 'SecurityGroups',
    'add_subpanel_data' => 'securitygroup_id',
    'title_key' => 'LBL_SECURITYGROUPS_SUBPANEL_TITLE',
);


if (is_admin($current_user)) {
    $layout_defs['Users']['subpanel_setup']['securitygroups']['subpanel_name'] = 'ForUsers';
} else {
    $layout_defs['Users']['subpanel_setup']['securitygroups']['top_buttons'] = array();
}
