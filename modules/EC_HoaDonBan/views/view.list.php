<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.list.php');

class EC_HoaDonBanViewList extends ViewList {
	public function __construct() {
		parent::__construct();
	}

	public function listViewPrepare() {
		if (empty($_REQUEST['orderBy']) || !empty($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
		parent::listViewPrepare();
	}

	public function preDisplay() {
        parent::preDisplay();

		// Remove mass update and merge duplicates from the actions menu
        $this->lv->showMassupdateFields = false;
        $this->lv->mergeduplicates = false;
        $this->lv->export = false;
		
        // Add custom button the actions menu
        $this->lv->actionsMenuExtraItems[] = $this->getNewActionMenuItem();

		// Remove inline edit
		$this->lv->quickViewLinks = false;

		$this->lv->data['pageData']['pageLimit'] = 50; // Change this number as needed
    }

	public function display() {
		$this->getStyles();
		parent::display();
		$this->getScripts();
	}

	private function getStyles() {
		echo "<link rel='stylesheet' href='modules/{$this->bean->module_dir}/css/view.list.css?v=1.0.1'></script>";
	}

	private function getScripts() {
		echo "<script src='modules/{$this->bean->module_dir}/js/view.list.js?v=1.0.3'></script>";
	}

	private function getNewActionMenuItem() {
		return '
			<a type="button" href="#" class="menuItem button-mass-signing">Ký số hàng loạt</a>
		';
    }
}