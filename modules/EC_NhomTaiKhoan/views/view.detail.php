<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_NhomTaiKhoanViewDetail extends ViewDetail{
	function display() {
	    $this->populateCustomFields();
		parent::display();
	}
	
	function populateCustomFields() {
		global $app_list_strings;
		$ChiTietTheo = '<p><input type="checkbox" class="checkbox" name="chitiettheo" disabled="disabled" '.($this->bean->chitiettheo == 1 ? 'checked="checked"' : '').' />';
		if($this->bean->ds_chitiettheo != '')
			$ChiTietTheo .= '&nbsp;<label>'.$app_list_strings['ds_chitiettheo_list'][$this->bean->ds_chitiettheo].'</label>';
		if($this->bean->loaidoituong != '')
			$ChiTietTheo .= '-&nbsp;<label>'.$app_list_strings['loaidoituong_list'][$this->bean->loaidoituong].'</label>';
		$ChiTietTheo .= '</p>';
		$this->ss->assign('ChiTietTheo', $ChiTietTheo);
	}
}
