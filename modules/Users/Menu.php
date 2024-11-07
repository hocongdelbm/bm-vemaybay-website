<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings;
global $current_user, $sugar_config, $current_language;

$arr_admin = array(
    '1', //ducpham
    '622ecf27-f729-7187-7e27-6520e0dab882', //quangnd
    '4f4d7a13-4171-9b7d-251c-64dd8f9885e4', //nhatdo
    '168889bb-54c2-59c7-8b3f-649102530d3c', //hungnh
);

$module_menu = array();
if ($GLOBALS['current_user']->isAdminForModule('Users')) {
    $module_menu = array(
        array("index.php?module=Users&action=EditView&return_module=Users&return_action=DetailView", $mod_strings['LNK_NEW_USER'], "Create"),
        array("index.php?module=Users&action=EditView&usertype=group&return_module=Users&return_action=DetailView", $mod_strings['LNK_NEW_GROUP_USER'], "Create_Group_User")
    );
    $module_menu[] = array("index.php?module=Users&action=ListView&return_module=Users&return_action=DetailView", $mod_strings['LNK_USER_LIST'], "List");
    $module_menu[] = array("index.php?module=Import&action=Step1&import_module=Users&return_module=Users&return_action=index", $mod_strings['LNK_IMPORT_USERS'], "Import", 'Contacts');
}

$sg_mod_strings = return_module_language($current_language, 'SecurityGroups');
$module_menu[]  = array("index.php?module=SecurityGroups&action=EditView&return_module=SecurityGroups&return_action=DetailView", $sg_mod_strings['LNK_NEW_RECORD'], "Create_Security_Group");
$module_menu[]  = array("index.php?module=SecurityGroups&action=ListView&return_module=SecurityGroups&return_action=ListView", $sg_mod_strings['LBL_LIST_FORM_TITLE'], "Security_Groups");

if (is_admin($current_user)) {
    global $current_language;
    $admin_mod_strings      = return_module_language($current_language, 'Administration');
    $module_menu[]          = array("index.php?module=ACLRoles&action=index&return_module=SecurityGroups&return_action=ListView", $admin_mod_strings['LBL_MANAGE_ROLES_TITLE'], "Role_Management");
    $module_menu[]          = array("index.php?module=SecurityGroups&action=config&return_module=SecurityGroups&return_action=ListView", $admin_mod_strings['LBL_CONFIG_SECURITYGROUPS_TITLE'], "Security_Suite_Settings");

    // Login as user
    if(!empty($_REQUEST['record']) && isset($_REQUEST['module']) && $_REQUEST['module'] == 'Users' && in_array($current_user->id, $arr_admin)) {
		require_once('modules/Users/User.php');
		$log_user = new User();
		$log_user->retrieve($_REQUEST['record']);
		$module_menu[] = Array("index.php?module=Users&action=Masquerade&record=".$_REQUEST['record'], $app_strings['LBL_LOGIN_AS'].$log_user->user_name,"Masquerade");
	}
}

// $module_menu[] = array("index.php?module=InboundEmail&action=index", $mod_strings['LNK_LIST_INBOUND_EMAIL_ACCOUNTS'], "List");
// $module_menu[] = array("index.php?module=OutboundEmailAccounts&action=index", $mod_strings['LNK_LIST_OUTBOUND_EMAIL_ACCOUNTS'], "List");
// $module_menu[] = array("index.php?module=ExternalOAuthConnection&action=index", $mod_strings['LNK_EXTERNAL_OAUTH_CONNECTIONS'], "List");
