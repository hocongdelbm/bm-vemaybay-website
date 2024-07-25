<?php
require_once('include/MVC/View/SugarView.php');
require_once('modules/Contacts/Popup_picker.php');

class ContactsViewMailMergePopup extends SugarView
{
    public function ContactAddressPopup()
    {
        parent::__construct();
    }
    
    public function process()
    {
        $this->display();
    }

    public function display()
    {
        $popup = new Popup_Picker();
        echo $popup->process_page_for_merge();
    }
}
