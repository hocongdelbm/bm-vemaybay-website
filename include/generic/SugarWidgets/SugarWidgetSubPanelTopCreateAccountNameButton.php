<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopCreateAccountNameButton extends SugarWidgetSubPanelTopButtonQuickCreate
{
    public function getWidgetId($buttonSuffix = true)
    {
        return parent::getWidgetId();
    }

    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        global $app_strings;
        global $currentModule;

        $title = $app_strings['LBL_NEW_BUTTON_TITLE'];
        //$accesskey = $app_strings['LBL_NEW_BUTTON_KEY'];
        $value = $app_strings['LBL_NEW_BUTTON_LABEL'];
        $this->module = 'Contacts';
        if (ACLController::moduleSupportsACL($defines['module'])  && !ACLController::checkAccess($defines['module'], 'edit', true)) {
            $button = "<input title='$title'class='button' type='button' name='button' value='  $value  ' disabled/>\n";
            return $button;
        }
        
        $additionalFormFields = array();
        if (isset($defines['focus']->billing_address_street)) {
            $additionalFormFields['primary_address_street'] = $defines['focus']->billing_address_street;
        }
        if (isset($defines['focus']->billing_address_city)) {
            $additionalFormFields['primary_address_city'] = $defines['focus']->billing_address_city;
        }
        if (isset($defines['focus']->billing_address_state)) {
            $additionalFormFields['primary_address_state'] = $defines['focus']->billing_address_state;
        }
        if (isset($defines['focus']->billing_address_country)) {
            $additionalFormFields['primary_address_country'] = $defines['focus']->billing_address_country;
        }
        if (isset($defines['focus']->billing_address_postalcode)) {
            $additionalFormFields['primary_address_postalcode'] = $defines['focus']->billing_address_postalcode;
        }
        if (isset($defines['focus']->phone_office)) {
            $additionalFormFields['phone_work'] = $defines['focus']->phone_office;
        }
        
        
        $button = $this->_get_form($defines, $additionalFormFields);
        $button .= "<input title='$title' class='button' type='submit' name='{$this->getWidgetId()}' id='{$this->getWidgetId()}' value='  $value  '/>\n";
        $button .= "</form>";
        return $button;
    }
}
