<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;

/*
**	Check field exist
*/
if(!empty($_SESSION['authenticated_user_id'])){
	$module = trim(stripslashes($_REQUEST['module']));
	$field = trim(stripslashes($_REQUEST['field']));
	$field_value = trim(stripslashes($_REQUEST['field_value']));
	$id = trim(stripslashes($_REQUEST['record']));
	echo myCheckValueExist($module, array($field), array($field_value), $id) ? '1' : '0';
} 
else {
	echo '<script>location.reload();</script>';
}
	  
