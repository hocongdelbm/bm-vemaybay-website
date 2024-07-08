<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php'); 

class EC_BanksViewDetail extends ViewDetail{
	function display(){
		$this->customFields();
		parent::display();
	}

	function customFields(){
		$image = '<img src="'.$this->bean->image.'" alt="'.$this->bean->short_name.'" />';
		if(!empty($this->bean->image))
			$this->ss->assign('HINHANH', $image);
	}
}
?>
