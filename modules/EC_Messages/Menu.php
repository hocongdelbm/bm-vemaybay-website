<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;

if(ACLController::checkAccess('EC_Messages', 'list', true)){
    $module_menu[]=array('index.php?module=EC_Messages&action=index&return_module=EC_Messages&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_Messages');
}

// if(ACLController::checkAccess('EC_Messages', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Messages&action=EditView&return_module=EC_Messages&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Messages');
// }

if(is_admin($current_user)) {
    $module_menu[] = array('index.php?module=EC_Messages&action=report', $mod_strings['LNK_REPORT'], '', 'EC_Messages');
}
