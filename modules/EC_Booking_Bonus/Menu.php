<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_Booking_Bonus', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Booking_Bonus&action=index&return_module=EC_Booking_Bonus&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Booking_Bonus');
}
