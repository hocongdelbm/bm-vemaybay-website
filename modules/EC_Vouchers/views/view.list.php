<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_VouchersViewList extends ViewList {
    function __construct() {
        parent::__construct();
    }

    function listViewPrepare() {
        if(empty($_REQUEST['orderBy']) || isset($_POST['query'])) {
            $_REQUEST['orderBy'] = 'date_entered';
            $_REQUEST['sortOrder'] = 'desc';
        }

        parent::listViewPrepare();
    }

    function display() {
        parent::display();
    }
}
