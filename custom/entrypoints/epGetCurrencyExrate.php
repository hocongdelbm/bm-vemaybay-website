<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;


 if(!empty($_SESSION['authenticated_user_id'])){
	 
	$CurrencyCode = trim(stripslashes($_REQUEST['CurrencyCode']));
	$type = trim(stripslashes($_REQUEST['type']));
	echo myGetCurrencyExrate($CurrencyCode, $type);

 } else {
	 echo '<script>
	 		location.reload();
	 	   </script>';
 }
