<?php
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewbookerips extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('EC_TongHop', 'list', true)) {
            $smartyCont = new Sugar_Smarty();
            $smartyCont->assign('TODAY', date('Y-m-d'));
            $smartyCont->display('modules/EC_TongHop/tpls/view_bookerips.tpl');
        } else {
            header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }
}
