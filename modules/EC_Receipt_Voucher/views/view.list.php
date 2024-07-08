<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');
require_once('modules/EC_Receipt_Voucher/EC_Receipt_VoucherListViewSmarty.php');

class EC_Receipt_VoucherViewList extends ViewList {
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

    function preDisplay() {
        $this->lv = new EC_Receipt_VoucherListViewSmarty();
    }

    function display() {
        // $this->lv->quickViewLinks = false; //This removes Edit Link

        parent::display();
    }
}
