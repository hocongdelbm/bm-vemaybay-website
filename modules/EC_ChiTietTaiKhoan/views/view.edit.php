<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');
require_once('custom/modules/Schedulers/_AddJobsHere.php');

class EC_ChiTietTaiKhoanViewEdit extends ViewEdit {
    public function __construct() {
		parent::__construct();
	}

    public function display() {
        parent::display();
    }
}