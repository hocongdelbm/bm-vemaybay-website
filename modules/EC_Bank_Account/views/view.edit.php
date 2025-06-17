<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_Bank_AccountViewEdit extends ViewEdit{
	function __construct() {
		parent::__construct();
	}
	
	function display() {
		$this->customFields();
		$this->customButtons();
		parent::display();
	}

	function customButtons() {
		global $app_strings;
		$custom_save = '<input title="'.$app_strings['LBL_SAVE_BUTTON_TITLE'].'" accesskey="'.$app_strings['LBL_SAVE_BUTTON_KEY'].'" type="button" class="btn btn-primary" name="btnSave" id="btnSave" value="'.$app_strings['LBL_SAVE_BUTTON_LABEL'].'" />';
		$this->ss->assign('CUSTOM_SAVE', $custom_save);
	}
	
	function customFields() {
		// Tài khoản
		$tk = new EC_TaiKhoan();
		$taikhoan = '<select class="box-select" id="taikhoan" name="account" tabindex="104"><option value=""></option>'.$tk->listOfTaiKhoan('', $this->bean->account).'</select>';
		$this->ss->assign('TAIKHOAN', $taikhoan);
	}
}
