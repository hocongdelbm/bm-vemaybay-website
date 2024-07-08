<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_NhomTaiKhoanViewEdit extends ViewEdit{
	function __construct() {
		parent::__construct();
	}
	
	function display() {
		$this->customButtons();
	    $this->populateCustomFields();
		parent::display();
	}
	
	function customButtons(){
		global $app_strings, $app_list_strings;
		$custom_save = '<input title="'.$app_strings['LBL_SAVE_BUTTON_TITLE'].'" accesskey="'.$app_strings['LBL_SAVE_BUTTON_KEY'].'" class="button" type="button" id="btnSave" name="btnSave" value="'.$app_strings['LBL_SAVE_BUTTON_LABEL'].'">';
		$this->ss->assign('CUSTOM_SAVE', $custom_save);
	}
	
	function populateCustomFields(){
		global $app_list_strings, $app_strings, $mod_strings, $timedate;
		$ChiTietTheo = '<p><input type="checkbox" id="chitiettheo" name="chitiettheo" value="'.$this->bean->chitiettheo.'" title="" tabindex="104" '.($this->bean->chitiettheo == 1 ? 'checked="cheched"' : '').' />';
		$ChiTietTheo .= '&nbsp;<select '.($this->bean->ds_chitiettheo != '' ? '' : 'style="display:none" disabled="disabled"').' id="ds_chitiettheo" name="ds_chitiettheo" tabindex="104" >'.get_select_options_with_id($app_list_strings['ds_chitiettheo_list'], $this->bean->ds_chitiettheo).'</select>';
		$ChiTietTheo .= '&nbsp;<select '.($this->bean->loaidoituong != '' ? '' : 'style="display:none" disabled="disabled"').' id="loaidoituong" name="loaidoituong" tabindex="104" >'.get_select_options_with_id($app_list_strings['loaidoituong_list'], $this->bean->loaidoituong).'</select></p>';
		$this->ss->assign('ChiTietTheo', $ChiTietTheo);
	}
}
