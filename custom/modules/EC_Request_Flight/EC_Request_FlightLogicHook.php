<?php
class EC_Request_FlightLogicHook {
	function customDisplay($focus, $event, $argument) {
		global $app_list_strings;

		switch ($focus->request_status) {
			case 1:
				$color_stt = 'green';
				break;
			case 2:
				$color_stt = 'blue';
				break;
			case 3:
				$color_stt = 'red';
				break;
			default:
				$color_stt = 'orange';
				break;
		}
		$focus->request_status = '<b><font color="'.$color_stt.'">'.$app_list_strings['request_status_list'][(int)$focus->request_status].'</font></b>';
	}
}