<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewissueticket extends SugarView {
	function display() {
		$smarty = new Sugar_Smarty();

		// Map [iata_code => logo_url].
		$airlineLogoMap = [];
		foreach (array_keys(EC_Airlines::getAirlineList()) as $airlineCode) {
			$airlineLogoMap[$airlineCode] = EC_Airlines::getLogoUrl($airlineCode);
		}
		$smarty->assign('AIRLINE_LOGO_MAP', json_encode($airlineLogoMap));

		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_issueticket.tpl');
	}
}
	
?>