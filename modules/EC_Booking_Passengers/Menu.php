<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Booking_Passengers', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Booking_Passengers&action=EditView&return_module=EC_Booking_Passengers&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Booking_Passengers');
}
if (ACLController::checkAccess('EC_Booking_Passengers', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Booking_Passengers&action=index&return_module=EC_Booking_Passengers&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Booking_Passengers');
}
