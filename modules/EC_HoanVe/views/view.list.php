<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_HoanVeViewList extends ViewList {

	private function loadSupplierListOptions() {
		global $app_list_strings;

		$app_list_strings['hoanve_supplier_list'] = array('' => '');
		$sql = "SELECT id, name
				FROM accounts
				WHERE deleted = 0
					AND account_type = 'Supplier'
					AND is_stop_tracking = 0
				ORDER BY name";
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$app_list_strings['hoanve_supplier_list'][$row['id']] = $row['name'];
		}

		// Backward-compatible alias in case cached metadata uses the old typo key.
		$app_list_strings['hoanve_supperlier_list'] = $app_list_strings['hoanve_supplier_list'];
	}

	function listViewPrepare(){
		$this->loadSupplierListOptions();

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

	function preDisplay() {
		$this->loadSupplierListOptions();

		parent::preDisplay();
	}

	function prepareSearchForm() {
		$this->loadSupplierListOptions();

		parent::prepareSearchForm();
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