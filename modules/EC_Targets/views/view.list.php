<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_TargetsViewList extends ViewList {
	
	function listViewPrepare(){	 
		global $current_user;

		if(empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])){
		  $_REQUEST['orderBy'] = 'date_entered'; 
		  $_REQUEST['sortOrder'] = 'desc';
		}

		parent::listViewPrepare(); 
  	}

  	function display() {
  		parent::display();
  	}

}

?>