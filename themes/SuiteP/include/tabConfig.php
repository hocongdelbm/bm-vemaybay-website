<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$GLOBALS['tabStructure'] = array(
    "LBL_TABGROUP_SALES" => array(
        'label' => 'LBL_TABGROUP_SALES',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Opportunities",
            "Leads",
            "Contracts",
            "Quotes",
            "Forecasts",
        )
    ),
    "LBL_TABGROUP_MARKETING" => array(
        'label' => 'LBL_TABGROUP_MARKETING',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Leads",
        )
    ),
    "LBL_TABGROUP_SUPPORT" => array(
        'label' => 'LBL_TABGROUP_SUPPORT',
        'modules' => array(
            "Home",
            "Accounts",
            "Contacts",
            "Cases",
            "Bugs",
        )
    ),
    "LBL_TABGROUP_ACTIVITIES" => array(
        'label' => 'LBL_TABGROUP_ACTIVITIES',
        'modules' => array(
            "Home",
            "Calendar",
            "Calls",
            "Meetings",
            "Emails",
            "Tasks",
            "Notes",
        )
    ),
    "LBL_TABGROUP_COLLABORATION"=>array(
        'label' => 'LBL_TABGROUP_COLLABORATION',
        'modules' => array(
            "Home",
            "Emails",
            "Documents",
        )
    ),
);

if (file_exists('custom/include/tabConfig.php')) {
    require 'custom/include/tabConfig.php';
}
