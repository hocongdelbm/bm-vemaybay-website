<?php
global $app_strings;
if ($app_strings === null) {
    $app_strings = return_application_language($current_language);
}

$installation_scenarios = array(
    array(
        'key' => 'Sales',
        'title' => $app_strings['LBL_SCENARIO_SALES'],
        'description' => $app_strings['LBL_SCENARIO_SALES_DESCRIPTION'],
        'groupedTabs' => 'LBL_TABGROUP_SALES',
        'modules' =>
        array(
            0 => 'Opportunities',
        ),
        'modulesScenarioDisplayName' =>
        array(
            0 => $app_strings['LBL_OPPORTUNITIES'],
        ),
        'dashlets' =>
        array(
            0 => 'MyOpportunitiesDashlet',
        )
    ),
    array(
        'key' => 'Finance',
        'title' => $app_strings['LBL_SCENARIO_FINANCE'],
        'description' => $app_strings['LBL_SCENARIO_FINANCE_DESCRIPTION'],
        'groupedTabs' => '',
        'modules' =>
        array(
            0 => 'AOS_Products',
            1 => 'AOS_Product_Categories',
            2 => 'AOS_Quotes',
            3 => 'AOS_Invoices',
            4 => 'AOS_Contracts'
        ),
        'modulesScenarioDisplayName' =>
        array(
            0 => 'Products',
            1 => 'Product_Categories',
            2 => 'Quotes',
            3 => 'Invoices',
            4 => 'Contracts'
        ),
        'dashlets' => array()
    ),
    array(
        'key' => 'ServiceManagement',
        'title' => $app_strings['LBL_SCENARIO_SERVICE'],
        'description' => $app_strings['LBL_SCENARIO_SERVICE_DESCRIPTION'],
        'groupedTabs' => 'LBL_TABGROUP_SUPPORT',
        'modules' =>
        array(
            0 => 'Cases',
            1 => 'AOK_KnowledgeBase',
            2 => 'AOK_Knowledge_Base_Categories'    //Is this the same as Knowledge Base Articles
        ),
        'modulesScenarioDisplayName' =>
        array(
            0 => 'Cases',
            1 => 'Knowledge Base',
            2 => 'Knowledge Base Categories'
        ),
        'dashlets' => array()
    ),
    array(
        'key' => 'ProjectManagement',
        'title' => $app_strings['LBL_SCENARIO_PROJECT'],
        'description' => $app_strings['LBL_SCENARIO_PROJECT_DESCRIPTION'],
        'groupedTabs' => '',
        'modules' =>
        array(
            0 => 'AM_TaskTemplates',    //Project Task Templates'
            1 => 'Project Tasks',
            2 => 'AM_ProjectTemplates',
            3 => 'Project'
        ),
        'modulesScenarioDisplayName' =>
        array(
            0 => 'Project Task Templates',    //Project Task Templates'
            1 => 'Project Tasks',
            2 => 'Project Templates',
            3 => 'Project'
        ),
        'dashlets' => array()
    )
);
