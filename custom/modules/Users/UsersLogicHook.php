<?php
class UsersLogicHook
{
	function RedirectUser($focus)
	{
		global $current_user;

		if ($current_user->user_name == 'pandadth') {
			$url = "index.php?module=EC_TongHop&action=businessreport&return_module=EC_TongHop&return_action=businessreport";
		} else {
			$url = "index.php?module=EC_Flight_Bookings&action=index";
		}

		SugarApplication::redirect($url);
	}
}
