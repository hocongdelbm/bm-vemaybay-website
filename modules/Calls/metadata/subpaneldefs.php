<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Calls'] = array(
    // sets up which panels to show, in which order, and with what linked_fields
    'subpanel_setup' => array(
        // 'contacts' => array(
        //     'top_buttons' => array(),
        //     'order' => 10,
        //     'module' => 'Contacts',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'last_name, first_name',
        //     'subpanel_name' => 'ForCalls',
        //     'get_subpanel_data' => 'contacts',
        //     'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
        // ),
        // 'users' => array(
        //     'top_buttons' => array(),
        //     'order' => 20,
        //     'module' => 'Users',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'full_name',
        //     'subpanel_name' => 'ForCalls',
        //     'get_subpanel_data' => 'users',
        //     'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
        // ),
        // 'leads' => array(
        //     'order' => 30,
        //     'module' => 'Leads',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'last_name, first_name',
        //     'subpanel_name' => 'ForCalls',
        //     'get_subpanel_data' => 'leads',
        //     'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
        //     'top_buttons' => array(),
        // ),
        // 'history' => array(
        //     'order' => 40,
        //     'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
        //     'type' => 'collection',
        //     'subpanel_name' => 'history',   //this values is not associated with a physical file.
        //     'sort_order' => 'desc',
        //     'sort_by' => 'date_entered',
        //     'header_definition_from_subpanel' => 'calls',
        //     'module' => 'History',
        //     'top_buttons' => array(
        //         array('widget_class' => 'SubPanelTopCreateNoteButton'),
        //     ),
        //     'collection_list' => array(
        //         'notes' => array(
        //             'module' => 'Notes',
        //             'subpanel_name' => 'ForCalls',
        //             'get_subpanel_data' => 'notes',
        //         ),
        //     ),
        // ),
        // 'securitygroups' => array(
        //     'top_buttons' => array(array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'SecurityGroups', 'mode' => 'MultiSelect'),),
        //     'order' => 900,
        //     'sort_by' => 'name',
        //     'sort_order' => 'asc',
        //     'module' => 'SecurityGroups',
        //     'refresh_page' => 1,
        //     'subpanel_name' => 'default',
        //     'get_subpanel_data' => 'SecurityGroups',
        //     'add_subpanel_data' => 'securitygroup_id',
        //     'title_key' => 'LBL_SECURITYGROUPS_SUBPANEL_TITLE',
        // ),
    ),
);
