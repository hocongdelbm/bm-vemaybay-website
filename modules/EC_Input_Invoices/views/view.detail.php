<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_Input_InvoicesViewDetail extends ViewDetail
{

	function display()
	{
		header("Location: index.php?module=EC_HoaDonBan&action=inputinvoice&ticket_code=" . $this->bean->name);
		// parent::display();
	}
}
