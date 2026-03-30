<?php
require_once("include/Sugar_Smarty.php");

class Viewissueticket extends SugarView {
	function display() {
		$smarty = new Sugar_Smarty();
		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_issueticket.tpl');
	}
}
	
?>