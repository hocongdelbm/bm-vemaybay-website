<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $current_user;

if (ACLController::checkAccess('EC_Zalo_Apps', 'list', true)) {
    $module_menu[] = array(
        'index.php?module=EC_Zalo_Apps&action=index&return_module=EC_Zalo_Apps&return_action=DetailView',
        $mod_strings['LNK_LIST'],
        'View',
        'EC_Zalo_Apps'
    );
}

if (ACLController::checkAccess('EC_Zalo_Apps', 'edit', true) && is_admin($current_user)) {
    $module_menu[] = array(
        'index.php?module=EC_Zalo_Apps&action=EditView&return_module=EC_Zalo_Apps&return_action=DetailView',
        $mod_strings['LNK_NEW_RECORD'],
        'Add',
        'EC_Zalo_Apps'
    );
}
