<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Emails'] = array(
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => array(
        'notes' => array(
            'order' => 5,
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'notes',
            'title_key' => 'LBL_NOTES_SUBPANEL_TITLE',
            'module' => 'Notes',
            'top_buttons' => array(),
        ),
        'accounts' => array(
            'order' => 10,
            'module' => 'Accounts',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'accounts',
            'add_subpanel_data' => 'account_id',
            'title_key' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'contacts' => array(
            'order' => 20,
            'module' => 'Contacts',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'contacts',
            'add_subpanel_data' => 'contact_id',
            'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'opportunities' => array(
            'order' => 25,
            'module' => 'Opportunities',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'opportunities',
            'add_subpanel_data' => 'opportunity_id',
            'title_key' => 'LBL_OPPORTUNITY_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'leads' => array(
            'order' => 30,
            'module' => 'Leads',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'leads',
            'add_subpanel_data' => 'lead_id',
            'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'cases' => array(
            'order' => 40,
            'module' => 'Cases',
            'sort_order' => 'desc',
            'sort_by' => 'case_number',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'cases',
            'add_subpanel_data' => 'case_id',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'users' => array(
            'order' => 50,
            'module' => 'Users',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'users',
            'add_subpanel_data' => 'user_id',
            'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),
        'bugs' => array(
            'order' => 60,
            'module' => 'Bugs',
            'sort_order' => 'desc',
            'sort_by' => 'bug_number',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'bugs',
            'add_subpanel_data' => 'bug_id',
            'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),


        'project' => array(
            'order' => 80,
            'module' => 'Project',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'ForEmails',
            'get_subpanel_data' => 'project',
            'add_subpanel_data' => 'project_id',
            'title_key' => 'LBL_PROJECT_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect')
            ),
        ),

        'meetings' => array(
            'order' => 1,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'module' => 'Meetings',
            'subpanel_name' => 'ForActivities',
            'get_subpanel_data' => 'meetings',
            'top_buttons' => array(),
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
