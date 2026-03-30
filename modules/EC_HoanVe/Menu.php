<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;

if (ACLController::checkAccess('EC_HoanVe', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_HoanVe&action=EditView&return_module=EC_HoanVe&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_HoanVe');
}
if (ACLController::checkAccess('EC_HoanVe', 'list', true)) {
    $module_menu[] = array('index.php?module=EC_HoanVe&action=index&return_module=EC_HoanVe&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_HoanVe');
}

if (ACLController::checkAccess('EC_HoanVe', 'list', true)){
    $module_menu[] = array("index.php?module=EC_HoanVe&action=returnbooking&return_module=EC_HoanVe&return_action=DetailView", $mod_strings['LNK_RETURN_BK'], "EC_HoanVe", 'EC_HoanVe');
} 

// if (ACLController::checkAccess('EC_HoanVe', 'import', true)) {
//     $module_menu[] = array('index.php?module=Import&action=Step1&import_module=EC_HoanVe&return_module=EC_HoanVe&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'EC_HoanVe');
// }
