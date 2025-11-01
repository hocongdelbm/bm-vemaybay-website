<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_Zalo_ContactsViewEdit extends ViewEdit {
	public function __construct() {
		parent::__construct();
	}

    public function display() {
        parent::display();
        // $this->bean->sync_data_from_contacts_table();
    }
}
?>