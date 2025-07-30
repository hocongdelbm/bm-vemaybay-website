<?php
require_once('include/MVC/View/SugarView.php');
require_once('modules/Contacts/Popup_picker.php');

class ContactsViewContactAddressPopup extends SugarView
{
    public function __construct()
    {
        parent::__construct();
    }

    public function process()
    {
        $this->display();
    }

    public function display()
    {
        $this->renderJavascript();
        $popup = new Popup_Picker();
        echo $popup->process_page_for_address();
    }
}
