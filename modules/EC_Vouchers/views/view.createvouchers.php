<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewcreatevouchers extends SugarView {
	function display() {
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Vouchers/tpls/view_createvouchers.tpl');	
	}

	function populateContent($smarty) {
		// Thời hạn voucher
		// nếu là ngày cuối tháng thì thời hạn chạy sang tháng sau
		if(myCalculateDayBetweenDates(date('d-m-Y'), date('t-m-Y')) < 10) {
			$start_date = date('01-m-Y', strtotime("+1 month"));
			$end_date = date('t-m-Y', strtotime("+1 month"));
		} else {
			$start_date = date('d-m-Y');
			$end_date = date('t-m-Y');
		}
		$smarty->assign('START_DATE', $start_date);
		$smarty->assign('END_DATE', $end_date);
	}
}
