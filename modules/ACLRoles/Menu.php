<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

$module_menu = array(
    array("index.php?module=ACLRoles&action=EditView", $mod_strings['LBL_CREATE_ROLE'], "Create"),
    array("index.php?module=ACLRoles&action=index", $mod_strings['LIST_ROLES'], "Role_Management"),
    array("index.php?module=ACLRoles&action=ListUsers", $mod_strings['LIST_ROLES_BY_USER'], "List"),
);

global $current_language;
$sg_mod_strings = return_module_language($current_language, 'SecurityGroups');
$module_menu[] = array("index.php?module=SecurityGroups&action=EditView&return_module=SecurityGroups&return_action=DetailView", $sg_mod_strings['LNK_NEW_RECORD'], "Create");
$module_menu[] = array("index.php?module=SecurityGroups&action=ListView&return_module=SecurityGroups&return_action=ListView", $sg_mod_strings['LBL_LIST_FORM_TITLE'], "Security_Groups");

global $current_user;
if (is_admin($current_user)) {
    global $current_language;
    $admin_mod_strings = return_module_language($current_language, 'Administration');
    $module_menu[] = array("index.php?module=Users&action=index&return_module=SecurityGroups&return_action=ListView", $admin_mod_strings['LBL_MANAGE_USERS_TITLE'], "List");
    $module_menu[] = array("index.php?module=SecurityGroups&action=config&return_module=SecurityGroups&return_action=ListView", $admin_mod_strings['LBL_CONFIG_SECURITYGROUPS_TITLE'], "Security_Groups");
}
