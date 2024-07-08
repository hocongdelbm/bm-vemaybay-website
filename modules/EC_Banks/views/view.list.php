<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_BanksViewList extends ViewList
{
	function __construct()
	{
		parent::__construct();
	}

	function listViewPrepare()
	{
		if (empty($_REQUEST['orderBy'])) {
			$_REQUEST['orderBy'] = 'short_name';
			$_REQUEST['sortOrder'] = 'asc';
		}
		parent::listViewPrepare();
	}

	function display()
	{
		parent::display();
	}
}
