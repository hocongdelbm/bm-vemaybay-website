<?php
class UsersLogicHook
{
	function RedirectUser($focus)
	{
		global $current_user;

		$url = "index.php?module=EC_Flight_Bookings&action=index";
		if ($current_user->user_name == 'pandadth') {
			$url = "index.php?module=EC_TongHop&action=report_sales_weekly&return_module=EC_TongHop&return_action=report_sales_weekly";
		} 

		SugarApplication::redirect($url);
	}
}
