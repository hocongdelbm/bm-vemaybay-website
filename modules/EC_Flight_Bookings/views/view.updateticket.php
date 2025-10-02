<?php
require_once("include/Sugar_Smarty.php");

class Viewupdateticket extends SugarView {
	function display() {
        $this->displayStyle();
		$smarty = new Sugar_Smarty();
		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_updateticket.tpl');
        $this->displayScript();
	}

	function displayStyle() {
		$style = '';
		$style .= '<link type="text/css" rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/updateticket.css">';
        echo $style;
    }
    function displayScript() {
		$script = '';
		$script .= '<script src="modules/'.$this->bean->module_dir.'/js/updateticket.js"></script>';
        echo $script;
    }
}
	
?>