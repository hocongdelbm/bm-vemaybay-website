<?php

class ContactsController extends SugarController
{
    public function action_Popup()
    {
        if (!empty($_REQUEST['html']) && $_REQUEST['html'] == 'mail_merge') {
            $this->view = 'mailmergepopup';
        } else {
            $this->view = 'popup';
        }
    }
    
    public function action_ValidPortalUsername()
    {
        $this->view = 'validportalusername';
    }

    public function action_RetrieveEmail()
    {
        $this->view = 'retrieveemail';
    }

    public function action_ContactAddressPopup()
    {
        $this->view = 'contactaddresspopup';
    }
  
    public function action_CloseContactAddressPopup()
    {
        $this->view = 'closecontactaddresspopup';
    }
}
