<?php
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewanalytics extends SugarView {
	function display() {
        if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $smartyCont->display('modules/EC_TongHop/tpls/view_analytics.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }
}
