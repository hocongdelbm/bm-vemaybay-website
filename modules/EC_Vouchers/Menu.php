<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config, $current_user;
 
if(ACLController::checkAccess('EC_Vouchers', 'list', true)) {
    $module_menu[] = ['index.php?module=EC_Vouchers&action=index&return_module=EC_Vouchers&return_action=DetailView', $mod_strings['LNK_LIST'], 'View', 'EC_Vouchers'];
}

if(is_admin($current_user)) {
    if(ACLController::checkAccess('EC_Vouchers', 'edit', true)) {
        $module_menu[] = ["index.php?module=EC_Vouchers&action=createvouchers&return_module=EC_Vouchers&return_action=DetailView", $mod_strings['LNK_MULTIPLE_NEW_RECORDS'], 'CreateEC_Vouchers', 'EC_Vouchers'];
    }
}
