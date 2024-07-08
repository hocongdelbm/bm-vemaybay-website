<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db, $current_user;

if(!empty($_SESSION['authenticated_user_id'])){
	
	$type = trim($_GET['type']);
	$term = trim($_GET['term']);
	if($type && $term){
		$data = '';
		if($type == 'airport'){
			$result = myGetAirportInfo2($term, 0);
			$data = json_encode($result['data']);
		} else if($type == 'airline'){
			$result = myGetAirlineInfo2($term, 0);
			$data = json_encode($result['data']);
		}
		echo $data;
	}

} else {
	echo '<script>location.reload();</script>';
}
	  
