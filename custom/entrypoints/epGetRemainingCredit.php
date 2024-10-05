<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;

if(!empty($_SESSION['authenticated_user_id'])){
   
   if($_POST['aircode'] && $_POST['agent_id'] && $_POST['agent_pwd']){
	   
	   $airline = $_POST['aircode'];
	   $agent_id = $_POST['agent_id'];
	   $agent_pwd = $_POST['agent_pwd'];
	   $data = myGetSupplierRemainingCredit($airline, $agent_id, $agent_pwd);
	   echo json_encode($data);
	   
   }
   
} else echo 0;