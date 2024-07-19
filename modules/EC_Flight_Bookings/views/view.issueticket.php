<?php
require_once("include/Sugar_Smarty.php");

class Viewissueticket extends SugarView {
	function display() {
        $this->displayStyle();

		// if(is_admin($GLOBALS['current_user'])) {
			$smarty = new Sugar_Smarty();
			$this->populateContent($smarty);
			$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_issueticket.tpl');
		// }
	}

	function displayStyle() {
		global $app_list_strings, $current_user;

        // External file
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
	
	function populateContent($smarty_obj) {
		// global $db, $app_list_strings;
	}
}
	
?>