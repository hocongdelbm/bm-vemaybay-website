<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_HoanVeViewList extends ViewList {

	function listViewPrepare(){
		if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
		  $_REQUEST['orderBy'] = 'date_entered'; 
		  $_REQUEST['sortOrder'] = 'desc';
		} 
		parent::listViewPrepare(); 
  	}	
	
	function display() {

		# Hide Quick Edit Pencil
		$this->lv->quickViewLinks = false;

		// $this->display;	
		parent::display();
	}

	function displayJS() {
		echo '<script>
			$(document).ready(function() {
				$("\
					<form id=\'search_bk_return\' method=\'post\' action=\'index.php\' target=\'_blank\'> \
						<input type=\'hidden\' name=\'searchFormTab\' value=\'advanced_search\'> \
						<input type=\'hidden\' name=\'module\' value=\'EC_Payment_Voucher\'> \
						<input type=\'hidden\' name=\'action\' value=\'ListView\'> \
						<input type=\'hidden\' name=\'query\' value=\'true\'> \
					</form> \
				").insertAfter("#MassUpdate");
			});
		</script>';
	}
}

?>