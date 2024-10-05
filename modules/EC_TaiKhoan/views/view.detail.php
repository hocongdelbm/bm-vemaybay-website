<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_TaiKhoanViewDetail extends ViewDetail{	
	function display(){
	    $this->populateCustomFields();
		parent::display();
	}
	
	function populateCustomFields(){
		global $app_list_strings;
		$doituong = '<input type="checkbox" class="checkbox" name="doituong" '.($this->bean->doituong == 1 ? 'disabled="disabled" checked="checked"' : 'disabled="disabled"').' />';
		$doituong .= '&nbsp;'.$app_list_strings['loaidoituong_list'][$this->bean->loaidoituong];
		$this->ss->assign('DoiTuong', $doituong);
	}
}
?>