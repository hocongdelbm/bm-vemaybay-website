<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
// if(ACLController::checkAccess('EC_Vouchers', 'edit', true)){
//     $module_menu[]=array('index.php?module=EC_Vouchers&action=EditView&return_module=EC_Vouchers&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_Vouchers');
// }

if(ACLController::checkAccess('EC_Vouchers', 'list', true)){
    $module_menu[] = ["index.php?module=EC_Vouchers&action=index&return_module=EC_Vouchers&return_action=DetailView", $mod_strings["LNK_LIST"], "View", "EC_Vouchers"];
}

if(isAllowedUser()) {
    if(ACLController::checkAccess('EC_Vouchers', 'edit', true)){
        $module_menu[] = ["index.php?module=EC_Vouchers&action=createvouchers&return_module=EC_Vouchers&return_action=DetailView", $mod_strings["LNK_MULTIPLE_NEW_RECORDS"], "CreateEC_Vouchers", "EC_Vouchers"];
    }
}

// if(ACLController::checkAccess('EC_Vouchers', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=EC_Vouchers&return_module=EC_Vouchers&return_action=index", $app_strings['LBL_IMPORT'],"Import", 'EC_Vouchers');
