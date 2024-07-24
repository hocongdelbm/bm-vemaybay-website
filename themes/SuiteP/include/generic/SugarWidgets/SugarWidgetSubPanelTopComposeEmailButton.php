<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopComposeEmailButton extends SugarWidgetSubPanelTopButton
{
    public $form_value = '';

    public function getWidgetId($buttonSuffix = true)
    {
        global $app_strings;
        $this->form_value = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_LABEL'];
        return parent::getWidgetId();
    }

    public function &_get_form($defines, $additionalFormFields = null, $nonbutton = false)
    {
        if ((ACLController::moduleSupportsACL($defines['module']) && !ACLController::checkAccess($defines['module'], 'edit', true) ||
                $defines['module'] == "Activities" & !ACLController::checkAccess("Emails", 'edit', true))) {
            $temp = '';
            return $temp;
        }

        global $app_strings, $current_user;
        $title = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_TITLE'];
        $value = $app_strings['LBL_COMPOSE_EMAIL_BUTTON_LABEL'];

        //martin Bug 19660
        $client = $current_user->getEmailClient();

        /** @var Person|Company|Opportunity $bean */
        $bean = $defines['focus'];

        if ($client != 'sugar') {
            // awu: Not all beans have emailAddress property, we must account for this
            if (isset($bean->emailAddress)) {
                $to_addrs = $bean->emailAddress->getPrimaryAddress($bean);
                $button = "<input class='btn btn-primary' type='button' value='$value' id='" . $this->getWidgetId() . "' name='" . preg_replace('[ ]', '', $value) . "' title='$title' onclick=\"location.href='mailto:$to_addrs';return false;\"/>";
            } else {
                $button = "<input class='btn btn-secondary' type='button' value='$value' id='" . $this->getWidgetId() . "' name='" . preg_replace('[ ]', '', $value) . "' title='$title' onclick=\"location.href='mailto:';return false;\"/>";
            }
        } else {
            // Generate the compose package for the quick create options.
            require_once 'modules/Emails/EmailUI.php';


            // Opportunities does not have an email1 field
            // we need to use the related account email instead
            if ($bean->module_name === 'Opportunities') {
                $relatedAccountId = $bean->account_id;
                /** @var Account $relatedAccountBean */
                $relatedAccountBean = BeanFactory::getBean('Accounts', $relatedAccountId);
                if (!empty($relatedAccountBean) && !empty($relatedAccountBean->email1)) {
                    $bean->email1 = $relatedAccountBean->email1;
                    $bean->name = $relatedAccountBean->name;
                }
            }

            if (empty($bean->email1)) {
                $bean->email1 = '';
            }

            $emailUI = new EmailUI();
            $emailUI->appendTick = false;
            $button = '<a class="email-link btn btn-primary" onclick="$(document).openComposeViewModal(this);" data-module="'
            . $bean->module_name . '" data-record-id="'
            . $bean->id . '" data-module-name="'
            . $bean->name .'" data-email-address="'
            . $bean->email1 .'">'
            . $app_strings['LBL_COMPOSE_EMAIL_BUTTON_LABEL']
            . '</a>';
        }

        return $button;
    }

    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        $focus = new Meeting;
        if (!$focus->ACLAccess('EditView')) {
            return '';
        }
        
        $inputID = $this->getWidgetId();

        $button = $this->_get_form($defines, $additionalFormFields);

        global $current_user;
        $client = $current_user->getEmailClient();

        if ($client == 'sugar') {
            $button .= "<input class='btn btn-secondary' onclick='return false;' type='button' id='$inputID' value='$this->form_value'>";
        }

        return $button;
    }
}
