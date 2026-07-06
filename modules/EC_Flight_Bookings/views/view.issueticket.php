<?php
require_once("include/Sugar_Smarty.php");

class Viewissueticket extends SugarView {
	function display() {
		$smarty = new Sugar_Smarty();

		// Map [iata_code => logo_url] để JS lấy logo hãng bay qua EC_Airlines::getLogoUrl() thay vì tự build đường dẫn ảnh tĩnh.
		$airlineLogoMap = [];
		foreach (array_keys(EC_Airlines::getAirlineList()) as $airlineCode) {
			$airlineLogoMap[$airlineCode] = EC_Airlines::getLogoUrl($airlineCode);
		}
		$smarty->assign('AIRLINE_LOGO_MAP', json_encode($airlineLogoMap));

		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_issueticket.tpl');
	}
}
	
?>