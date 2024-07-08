<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $mod_strings;
$module_menu = array(
    array("index.php?module=ACLRoles&action=index", $mod_strings['LIST_ROLES'],"List"),
    array("index.php?module=ACLRoles&action=ListUsers", $mod_strings['LIST_ROLES_BY_USER'],"List"),
    
    );
