<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_LoaiChungTuViewList extends ViewList {

	function listViewPrepare()
  	{
		if(empty($_REQUEST['orderBy']))
		{
		  $_REQUEST['orderBy'] = 'maloai'; 
		  $_REQUEST['sortOrder'] = 'asc';
		} 
		parent::listViewPrepare(); 
  	}
	
	function display()
	{
		parent::display();
	}
}

?>