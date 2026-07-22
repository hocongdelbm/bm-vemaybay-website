<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $current_user;
$module_menu = array();

// if (is_admin($current_user)) {
    // $module_menu[] = array("index.php?module=Employees&action=EditView&return_module=Employees&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'], "Create", "Employees");
// } 
$module_menu[] = array("index.php?module=Employees&action=index&return_module=Employees&return_action=DetailView", $mod_strings['LNK_LIST'], "List", "Employees");
