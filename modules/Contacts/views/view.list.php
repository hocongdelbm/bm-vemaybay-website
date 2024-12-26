<?php
require_once('modules/Contacts/ContactsListViewSmarty.php');

class ContactsViewList extends ViewList
{
    /**
     * @see ViewList::preDisplay()
     */
    public function preDisplay()
    {
        require_once('modules/AOS_PDF_Templates/formLetter.php');
        formLetter::LVPopupHtml('Contacts');
        parent::preDisplay();

        $this->lv = new ContactsListViewSmarty();
    }

    public function listViewPrepare() {
		if (empty($_REQUEST['orderBy']) || isset($_REQUEST['query'])) {
			$_REQUEST['orderBy'] = 'date_entered';
			$_REQUEST['sortOrder'] = 'desc';
		}
		parent::listViewPrepare();
	}

    public function display() {
		$this->lv->lvd->additionalDetails = false;
		$this->lv->quickViewLinks = true;
		parent::display();
	}
}
