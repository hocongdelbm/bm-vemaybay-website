<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class AccountsViewDetail extends ViewDetail
{
    public function __construct()
    {
        parent::__construct();
    }




    /**
     * display
     * Override the display method to support customization for the buttons that display
     * a popup and allow you to copy the account's address into the selected contacts.
     * The custom_code_billing and custom_code_shipping Smarty variables are found in
     * include/SugarFields/Fields/Address/DetailView.tpl (default).  If it's a English U.S.
     * locale then it'll use file include/SugarFields/Fields/Address/en_us.DetailView.tpl.
     */
    public function display()
    {
        if (empty($this->bean->id)) {
            global $app_strings;
            sugar_die($app_strings['ERROR_NO_RECORD']);
        }

        require_once('modules/AOS_PDF_Templates/formLetter.php');
        formLetter::DVPopupHtml('Accounts');

        $this->dv->process();
        
        if (ACLController::checkAccess('Contacts', 'edit', true)) {
            $push_billing = $this->generatePushCode('billing');
            $push_shipping = $this->generatePushCode('shipping');
        } else {
            $push_billing = '';
            $push_shipping = '';
        }

        $this->ss->assign("custom_code_billing", $push_billing);
        $this->ss->assign("custom_code_shipping", $push_shipping);

        if (empty($this->bean->id)) {
            global $app_strings;
            sugar_die($app_strings['ERROR_NO_RECORD']);
        }
        echo $this->dv->display();
    }

    public function generatePushCode($param)
    {
        global $mod_strings;
        $address_fields = array('street', 'city', 'state', 'postalcode','country');

        $html = '<input class="button" title="' . $mod_strings['LBL_PUSH_CONTACTS_BUTTON_LABEL'] .
             '" type="button" onclick=\'open_contact_popup("Contacts", 600, 600, "&account_name=' .
             $this->bean->name . '&html=change_address';

        foreach ($address_fields as $value) {
            $field_name = $param.'_address_'.$value;
            $html .= '&primary_address_'.$value.'='.str_replace(array("\rn", "\r", "\n"), array('','','<br>'), urlencode($this->bean->$field_name)) ;
        }

        $html .= '", true, false);\' value="' . $mod_strings['LBL_PUSH_CONTACTS_BUTTON_TITLE']. '">';
        return $html;
    }
}
