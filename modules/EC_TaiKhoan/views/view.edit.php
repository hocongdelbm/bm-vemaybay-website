<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_TaiKhoanViewEdit extends ViewEdit{
	function __construct() {
		parent::__construct();
	}
	
	function display(){
		$this->customButtons();
	    $this->populateCustomFields();
		parent::display();
	}
	
	function customButtons(){
		global $app_strings;
		$custom_save = '<input title="'.$app_strings['LBL_SAVE_BUTTON_TITLE'].'" accesskey="'.$app_strings['LBL_SAVE_BUTTON_KEY'].'" class="btn btn-primary" type="button" name="btnSave" id="btnSave" value="'.$app_strings['LBL_SAVE_BUTTON_LABEL'].'">';
		$this->ss->assign('CUSTOM_SAVE', $custom_save);
	}
	
	function populateCustomFields(){
		global $app_list_strings, $app_strings, $mod_strings;
		// Danh sách tài khoản
		$tk = new EC_TaiKhoan();
		$taikhoan = '<select class="box-select" '.($this->bean->cap == 1 ? 'disabled="disabled"' : '').' id="taikhoantonghop" name="taikhoantonghop" tabindex="103" ><option cap="0" value=""></option>'.$tk->listOfTaiKhoan($this->bean->taikhoantonghop).'</select>';
		$taikhoan .= '<input type="hidden" name="cap" id="cap" value="'.($this->bean->cap != '' ? $this->bean->cap : 0).'" />';
		$this->ss->assign('TaiKhoanTongHop', $taikhoan);
		
		// Danh sách nhóm tài khoản
		$ntk = new EC_NhomTaiKhoan();
		$nhomtk = '<select class="box-select" id="nhomtaikhoan" name="nhomtaikhoan" tabindex="104" >'.$ntk->listOfNhomTaiKhoan($this->bean->nhomtaikhoan).'</select>';
		$this->ss->assign('NhomTaiKhoan', $nhomtk);
		
		// Danh sách đối tượng
		$doituong = '<input type="checkbox" id="doituong" name="doituong" value="'.$this->bean->doituong.'" title="" tabindex="107" '.($this->bean->doituong == 1 ? 'checked="checked"' : '').' />&nbsp;<select '.($this->bean->doituong == 0 ? 'style="display:none"' : '').' id="loaidoituong" name="loaidoituong" tabindex="107" >'.get_select_options_with_id($app_list_strings['loaidoituong_list'], $this->bean->loaidoituong).'</select>';
		$this->ss->assign('DoiTuong', $doituong);
	}
}
