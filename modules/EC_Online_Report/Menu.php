<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

if (ACLController::checkAccess('EC_Online_Report', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_Online_Report&action=index&return_module=EC_Online_Report&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Online_Report');
}
if (ACLController::checkAccess('EC_Online_Report', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Online_Report&action=EditView&return_module=EC_Online_Report&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Online_Report');
}
