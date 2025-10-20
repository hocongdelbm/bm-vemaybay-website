<?php
require_once("include/Sugar_Smarty.php");

class Viewupdateticket extends SugarView {
	public function display() {
        $this->displayStyle();
		$smarty = new Sugar_Smarty();
		$smarty->display("modules/{$this->bean->module_dir}/tpls/view_updateticket.tpl");
        $this->displayScript();
	}

	private function displayStyle() {
		echo "<link type='text/css' rel='stylesheet' href='themes/SuiteP/libs/css/select2.min.css'>
			<link type='text/css' rel='stylesheet' href='modules/{$this->bean->module_dir}/css/updateticket.css?v=1.0.2'>";
    }

    private function displayScript() {
		echo "<script src='modules/{$this->bean->module_dir}/js/updateticket.js?v=1.0.2'></script>";
    }
}
	
?>