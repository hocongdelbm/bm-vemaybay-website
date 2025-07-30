<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_LoaiChungTuViewEdit extends ViewEdit{

	function display(){
	    $this->populateCustomFields();
		parent::display();
	}
	
	function populateCustomFields(){
		global $app_list_strings, $app_strings, $mod_strings, $timedate;
		$tk = new EC_TaiKhoan();
		$tkno = '<select style="width:200px" id="taikhoanno" name="taikhoanno" tabindex="102"><option value=""></option>'.$tk->listOfTaiKhoan($this->bean->taikhoanno).'</select>';
		$this->ss->assign('TAIKHOANNO', $tkno);
		
		$tkco = '<select style="width:200px" id="taikhoanco" name="taikhoanco" tabindex="102"><option value=""></option>'.$tk->listOfTaiKhoan($this->bean->taikhoanco).'</select>';
		$this->ss->assign('TAIKHOANCO', $tkco);
	}
	
	
}
?>