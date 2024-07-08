<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');
require_once('modules/EC_Payment_Voucher/EC_Payment_VoucherListViewSmarty.php');

class EC_Payment_VoucherViewList extends ViewList
{

    function __construct() {
		parent::__construct();
	}

    function listViewPrepare()
    {
        if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
        parent::listViewPrepare();
    }

    function preDisplay()
    {
        $this->lv = new EC_Payment_VoucherListViewSmarty();
    }

    function display()
    {
        parent::display();
    }
}

?>