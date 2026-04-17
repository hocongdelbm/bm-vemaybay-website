<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['InboundEmail'] = [
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => [
        // 'securitygroups' => [
        //     'top_buttons' => [
        //         [
        //             'widget_class' => 'SubPanelTopSelectButton',
        //             'popup_module' => 'SecurityGroups',
        //             'mode' => 'MultiSelect'
        //         ],
        //     ],
        //     'order' => 900,
        //     'sort_by' => 'name',
        //     'sort_order' => 'asc',
        //     'module' => 'SecurityGroups',
        //     'refresh_page' => 1,
        //     'subpanel_name' => 'default',
        //     'get_subpanel_data' => 'SecurityGroups',
        //     'add_subpanel_data' => 'securitygroup_id',
        //     'title_key' => 'LBL_SECURITYGROUPS_SUBPANEL_TITLE',
        // ],
    ],
];
