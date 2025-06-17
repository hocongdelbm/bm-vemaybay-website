<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
$GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
$app_strings = return_application_language($GLOBALS['current_language']);
$mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');

global $app_list_strings, $app_strings, $mod_strings, $db;

if (!empty($_SESSION['authenticated_user_id'])) {
	$name = trim(stripslashes($_REQUEST['name']));
	$phone = trim(stripslashes($_REQUEST['phone']));
	$email = trim(stripslashes($_REQUEST['email']));
	$address = trim(stripslashes($_REQUEST['address']));
	$city = trim(stripslashes($_REQUEST['city']));
	$nation = trim(stripslashes($_REQUEST['nation']));

	if (isset($name) && !empty($name)) {
		$acc = new Account();
		$acc->id = '';
		$acc->name = $name;
		$acc->ownership = $name;
		$acc->phone_office = $phone;
		$acc->email1 = $email;
		$acc->billing_address_street = $address;
		$acc->billing_address_city = $city;
		$acc->billing_address_country = $nation;
		$acc->save();
		echo 1;
	}
	else echo 0;
}
else {
	echo '<script>location.reload();</script>';
}
