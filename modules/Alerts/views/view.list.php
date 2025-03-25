<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class AlertsViewList extends ViewList {
	function __construct() {
		parent::__construct();
	}

	function listViewPrepare() {
		if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
		parent::listViewPrepare();
	}

	public function listViewProcess() {
		global $current_user;

		// if(!is_admin($current_user)) 
		$this->params['custom_where'] = " AND alerts.assigned_user_id = '$current_user->id'";
        parent::listViewProcess();
    }

	function display() {
		$this->lv->quickViewLinks = false;
		$this->lv->lvd->additionalDetails = false;
		parent::display();
	}
}
