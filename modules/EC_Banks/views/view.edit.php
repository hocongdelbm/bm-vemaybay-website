<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_BanksViewEdit extends ViewEdit{
	function __construct() {
		parent::__construct();
	}
	
	function display(){
		$this->customFields();
		$this->customButtons();
		parent::display();
	}

	function customButtons(){
		global $app_strings;
		$custom_save = '<input title="'.$app_strings['LBL_SAVE_BUTTON_TITLE'].'" accesskey="'.$app_strings['LBL_SAVE_BUTTON_KEY'].'" type="button" class="btn btn-primary" name="btnSave" id="btnSave" value="'.$app_strings['LBL_SAVE_BUTTON_LABEL'].'" />';
		$this->ss->assign('CUSTOM_SAVE', $custom_save);
	}
	
	function customFields(){
		$image = '<input type="text" name="image" id="image" size="30" maxlength="" value="'.$this->bean->image.'" title="" tabindex="103" />';
		if(!empty($this->bean->image))
			$image .= '&nbsp;<img src="'.$this->bean->image.'" alt="'.$this->bean->short_name.'" />';
		$this->ss->assign('HINHANH', $image);
	}
}
?>
