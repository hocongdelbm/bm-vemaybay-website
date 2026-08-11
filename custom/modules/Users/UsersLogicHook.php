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

	function SyncToChat($bean, $event, $arguments)
	{
		if (empty($bean->id) || empty($bean->user_name)) {
			return;
		}

		require_once 'custom/include/helpers/api/APIChatUserSync.php';

		$fields = array(
			'Username' => from_html($bean->user_name),
			'FullName' => trim(from_html($bean->first_name) . ' ' . from_html($bean->last_name)),
			'Email' => from_html($bean->email1),
			'Phone' => $bean->phone_work ?: $bean->phone_mobile,
			'Title' => from_html($bean->title),
			'Status' => strtolower($bean->status) === 'inactive' ? 'inactive' : 'active',
			'IsAdmin' => !empty($bean->is_admin),
		);

		(new APIChatUserSync())->upsertUser($bean->id, $fields);
	}
}
