<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Booking_Details', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Booking_Details&action=EditView&return_module=EC_Booking_Details&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Booking_Details');
}
if (ACLController::checkAccess('EC_Booking_Details', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Booking_Details&action=index&return_module=EC_Booking_Details&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Booking_Details');
}
