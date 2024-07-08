<?php

 if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('EC_LyDoThangThua', 'edit', true)){
    $module_menu[]=array('index.php?module=EC_LyDoThangThua&action=EditView&return_module=EC_LyDoThangThua&return_action=DetailView', $mod_strings['LNK_NEW_RECORD'], 'Add', 'EC_LyDoThangThua');
}
if(ACLController::checkAccess('EC_LyDoThangThua', 'list', true)){
    $module_menu[]=array('index.php?module=EC_LyDoThangThua&action=index&return_module=EC_LyDoThangThua&return_action=DetailView', $mod_strings['LNK_LIST'],'View', 'EC_LyDoThangThua');
}