<?php
require_once("include/Sugar_Smarty.php");

class Viewissueticket extends SugarView {
	function display() {
        $this->displayStyle();

		$smarty = new Sugar_Smarty();
		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_issueticket.tpl');
	}

	function displayStyle() {
		$v = '1.0.0';

		$js = '';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/other.js?v='.$v.'"></script>';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/search.js?v='.$v.'"></script>';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/payment.js?v='.$v.'"></script>';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/add_ancillary.js?v='.$v.'"></script>';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/update_passenger.js?v='.$v.'"></script>';
        $js .= '<script src="modules/'.$this->bean->module_dir.'/js/api_vietjet/update_journey.js?v='.$v.'"></script>';

		$css = '';
		$css .= '<link type="text/css" rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/issueticket.css?v='.$v.'">';

        echo $js.$css;
    }
	
}
	
?>