<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_DebtsViewList extends ViewList {

	function listViewPrepare()
  	{
		if(empty($_REQUEST['orderBy']))
		{
		  $_REQUEST['orderBy'] = 'date_entered'; 
		  $_REQUEST['sortOrder'] = 'desc';
		} 
		parent::listViewPrepare(); 
  	}	
	
	function display()
	{
		parent::display();
	}
}

?>