<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Schedulers'] = array(
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => array(
        'times' => array(
            'order' => 20,
            'module' => 'SchedulersJobs',
            'sort_by' => 'execute_time',
            'sort_order' => 'desc',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'schedulers_times',
            'add_subpanel_data' => 'scheduler_id',
            'title_key' => 'LBL_JOBS_SUBPANEL_TITLE',
            'top_buttons' => array(),
        ),
    ),
);
