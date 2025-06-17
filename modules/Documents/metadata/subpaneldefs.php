<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Documents'] = array(
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => array(
        'therevisions' => array(
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'revision',
            'module' => 'DocumentRevisions',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_DOC_REV_HEADER',
            'get_subpanel_data' => 'revisions',
            'fill_in_additional_fields' => true,
        ),
        // 'accounts' => array(
        //     'order' => 30,
        //     'module' => 'Accounts',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
        //     'get_subpanel_data' => 'accounts',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
        // ),
        // 'contacts' => array(
        //     'order' => 40,
        //     'module' => 'Contacts',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
        //     'get_subpanel_data' => 'contacts',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
        // ),
        // 'opportunities' => array(
        //     'order' => 40,
        //     'module' => 'Opportunities',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
        //     'get_subpanel_data' => 'opportunities',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
        // ),
        // 'cases' => array(
        //     'order' => 50,
        //     'module' => 'Cases',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
        //     'get_subpanel_data' => 'cases',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
        // ),
        // 'bugs' => array(
        //     'order' => 60,
        //     'module' => 'Bugs',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
        //     'get_subpanel_data' => 'bugs',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
        // ),
        // 'aos_contracts_documents' => array(
        //     'order' => 101,
        //     'module' => 'AOS_Contracts',
        //     'subpanel_name' => 'default',
        //     'sort_order' => 'asc',
        //     'sort_by' => 'id',
        //     'title_key' => 'AOS_Contracts',
        //     'get_subpanel_data' => 'aos_contracts',
        //     'top_buttons' =>
        //         array(
        //             0 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopButtonQuickCreate',
        //                 ),
        //             1 =>
        //                 array(
        //                     'widget_class' => 'SubPanelTopSelectButton',
        //                     'mode' => 'MultiSelect',
        //                 ),
        //         ),
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
