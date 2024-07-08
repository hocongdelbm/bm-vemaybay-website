<?php
require_once("include/Sugar_Smarty.php");

class Viewchecksms extends SugarView {
	function display() {
		if(ACLController::checkAccess('EC_Flight_Bookings', 'list', true)){
			$smartyCont= new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_checksms.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function populateContent($smartyobj) {
		global $app_list_strings;
        $smartyobj->assign('PORT_LIST', get_select_options_with_id($app_list_strings['sms_port_list'], '0'));
	}
}
