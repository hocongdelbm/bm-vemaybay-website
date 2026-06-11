<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $mod_strings;
 
if(ACLController::checkAccess('EC_Zalo_Messages', 'edit', true)) {
    $module_menu[] = array('index.php?module=EC_Zalo_Messages&action=EditView&return_module=EC_Zalo_Messages&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Zalo_Messages');
}

// if(id_admin()) {
//     $module_menu[] = array('index.php?module=EC_Zalo_Messages&action=index&return_module=EC_Zalo_Messages&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Zalo_Messages');
// }
