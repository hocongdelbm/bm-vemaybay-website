<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
if (ACLController::checkAccess('Contacts', 'edit', true)) {
    $module_menu[] = array("index.php?module=Contacts&action=EditView&return_module=Contacts&return_action=index", $mod_strings['LNK_NEW_CONTACT'], "Create", 'Contacts');
}

// if (ACLController::checkAccess('Contacts', 'import', true)) {
//     $module_menu[] = array("index.php?module=Contacts&action=ImportVCard", $mod_strings['LNK_IMPORT_VCARD'], "Create_Contact_Vcard", 'Contacts');
// }
if (ACLController::checkAccess('Contacts', 'list', true)) {
    $module_menu[] = array("index.php?module=Contacts&action=index&return_module=Contacts&return_action=DetailView", $mod_strings['LNK_CONTACT_LIST'], "List", 'Contacts');
}
// if (ACLController::checkAccess('Contacts', 'import', true)) {
//     $module_menu[] = array("index.php?module=Import&action=Step1&import_module=Contacts&return_module=Contacts&return_action=index", $mod_strings['LNK_IMPORT_CONTACTS'], "Import", 'Contacts');
// }

if (ACLController::checkAccess('Contacts', 'list', true)) {
    $module_menu[] = array("index.php?module=Contacts&action=summary&return_module=Contacts&return_action=summary", "Báo cáo KH", "summary");
}

if(ACLController::checkAccess('EC_Contact_Points_Log', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Contact_Points_Log&action=index&return_module=EC_Contact_Points_Log', $mod_strings['LNK_POINTS_LOG'], 'View', 'EC_Contact_Points_Log');
}
