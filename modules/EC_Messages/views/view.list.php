<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class EC_MessagesViewList extends ViewList {
    function __construct() { parent::__construct(); }

    function display() {
		$this->lv->lvd->additionalDetails = false;
		$this->lv->quickViewLinks = false;
		parent::display();
	}

	function listViewPrepare() {
		if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
		parent::listViewPrepare();
	}
}