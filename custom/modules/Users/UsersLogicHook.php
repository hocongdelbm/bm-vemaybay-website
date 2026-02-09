<?php
class UsersLogicHook
{
	function RedirectUser($focus)
	{
		global $current_user;

		if ($current_user->user_name == 'pandadth') {
			$url = "index.php?module=EC_TongHop&action=report_sales_weekly&return_module=EC_TongHop&return_action=report_sales_weekly";
		} else {
			$url = "index.php?module=EC_TongHop&action=report_sales_issue&return_module=EC_TongHop&return_action=report_sales_issue";
		}

		SugarApplication::redirect($url);
	}
}
