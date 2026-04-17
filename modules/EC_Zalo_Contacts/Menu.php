<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $current_user;

// if (ACLController::checkAccess('EC_Zalo_Contacts', 'edit', true)) {
if ($current_user->id == '1') {
    $module_menu[] = [
        'index.php?module=EC_Zalo_Contacts&action=EditView&return_module=EC_Zalo_Contacts&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'],
        'Add',
        'EC_Zalo_Contacts'
    ];
}

if (ACLController::checkAccess('EC_Zalo_Contacts', 'list', true)) {
    $module_menu[] = [
        'index.php?module=EC_Zalo_Contacts&action=index&return_module=EC_Zalo_Contacts&return_action=DetailView',
        $mod_strings['LNK_LIST'],
        'View',
        'EC_Zalo_Contacts'
    ];
}
