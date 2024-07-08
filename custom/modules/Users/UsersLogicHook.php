<?php
class UsersLogicHook {
    function RedirectUser($focus) { 
		global $current_user;

		if($current_user->user_name == 'pandadth'){
			$url = "index.php?module=EC_TongHop&action=bookingqtyreport&return_module=EC_TongHop&return_action=bookingqtyreport";
		} else {
			$url = "index.php?module=EC_Flight_Bookings&action=index";
		}

		SugarApplication::redirect($url);
    }
	
	function UpdateUsrStt($focus) {
		$sql = '
			UPDATE ec_online_report
			SET status = 0
			WHERE deleted = 0 
			AND assigned_user_id = "' . $focus->id . '"
			AND DATE_ADD(date_entered, INTERVAL 7 HOUR) = "' . date('Y-m-d') . '"
		';
 		$focus->db->query($sql);
	}
}
?>
