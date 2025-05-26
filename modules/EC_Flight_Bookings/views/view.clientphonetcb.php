<?php
require_once("include/Sugar_Smarty.php");

class Viewclientphonetcb extends SugarView {
	function display() {
        die;
        $this->displayStyle();
		$smarty = new Sugar_Smarty();
		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_phone_request_tcb.tpl');
        $this->displayScript();
	}

	function displayStyle() {
		$style = '';
		$style .= '<link type="text/css" rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/phone_request_tcb.css">';
        echo $style;
    }
    function displayScript() {
		$script = '';
		$script .= '<script src="modules/'.$this->bean->module_dir.'/js/phone_request_tcb.js"></script>';
        echo $script;
    }
}
	
?>