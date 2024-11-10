<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_DebtsViewEdit extends ViewEdit {

	function display(){
		$this->populateCustomFields();
		parent::display();
	}

	function populateCustomFields(){
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		
		$this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan) ? date($date_format.' H:i', strtotime($this->bean->ngayhachtoan)) : date($date_format.' H:i');
	}
}
?>