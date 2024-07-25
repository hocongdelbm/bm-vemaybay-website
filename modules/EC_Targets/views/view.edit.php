<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php'); 

class EC_TargetsViewEdit extends ViewEdit{
	
	function display() {
		if($this->bean->status > 0) {
			header("Location: index.php?module=EC_Targets&action=DetailView&record=".$this->bean->id);
			exit;
		} else {
			$this->populateCustomFields();
			$this->displayJS();
			parent::display();
		}
	}
	
	function populateCustomFields() {
		global $current_user;

		// năm
		$cus_year = '<select name="year" id="year">';
		for($i = date('Y'); $i <= date('Y') + 2; $i++) {
			$cus_year .= '<option value="'.$i.'">'.$i.'</option>';
		}
		$cus_year .= '</select>';
		$this->ss->assign('CUS_YEAR', $cus_year);
	}

	function displayJS() {
		echo '<script>
			$(document).ready(function() {
				var sig_digits = $("#sig_digits").val();
				var dec_seperator = $("#dec_seperator").val();
				var grp_seperator = $("#grp_seperator").val();

				$("input[name*=\'target_month\']").number(true, sig_digits, dec_seperator, grp_seperator);
			});
		</script>';
	}
}
?>