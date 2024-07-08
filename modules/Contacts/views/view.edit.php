<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ContactsViewEdit extends ViewEdit
{
    public function __construct()
    {
        parent::__construct();
        $this->useForSubpanel = true;
        $this->useModuleQuickCreateTemplate = true;
    }

    /**
     * @see SugarView::display()
     *
     * We are overridding the display method to manipulate the sectionPanels.
     * If portal is not enabled then don't show the Portal Information panel.
     */
    public function display()
    {
        $this->ev->process();
        if (!empty($_REQUEST['contact_name']) && !empty($_REQUEST['contact_id'])
            && $this->ev->fieldDefs['report_to_name']['value'] == ''
            && $this->ev->fieldDefs['reports_to_id']['value'] == '') {
            $this->ev->fieldDefs['report_to_name']['value'] = $_REQUEST['contact_name'];
            $this->ev->fieldDefs['reports_to_id']['value'] = $_REQUEST['contact_id'];
        }
        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings();
        if (empty($admin->settings['portal_on']) || !$admin->settings['portal_on']) {
            unset($this->ev->sectionPanels[strtoupper('lbl_portal_information')]);
        } else {
            if (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
                $this->ev->fieldDefs['portal_name']['value'] = '';
                $this->ev->fieldDefs['portal_active']['value'] = '0';
                $this->ev->fieldDefs['portal_password']['value'] = '';
                $this->ev->fieldDefs['portal_password1']['value'] = '';
                $this->ev->fieldDefs['portal_name_verified'] = '0';
                $this->ev->focus->portal_name = '';
                $this->ev->focus->portal_password = '';
                $this->ev->focus->portal_acitve = 0;
            } else {
                $this->ev->fieldDefs['portal_password']['value'] = '';
                $this->ev->fieldDefs['portal_password1']['value'] = '';
            }
            echo getVersionedScript('modules/Contacts/Contact.js');
            echo '<script language="javascript">';
            echo 'addToValidateComparison(\'EditView\', \'portal_password\', \'varchar\', false, SUGAR.language.get(\'app_strings\', \'ERR_SQS_NO_MATCH_FIELD\') + SUGAR.language.get(\'Contacts\', \'LBL_PORTAL_PASSWORD\'), \'portal_password1\');';
            echo 'addToValidateVerified(\'EditView\', \'portal_name_verified\', \'bool\', false, SUGAR.language.get(\'app_strings\', \'ERR_EXISTING_PORTAL_USERNAME\'));';
            echo 'YAHOO.util.Event.onDOMReady(function() {YAHOO.util.Event.on(\'portal_name\', \'blur\', validatePortalName);YAHOO.util.Event.on(\'portal_name\', \'keydown\', handleKeyDown);});';
            echo '</script>';
        }

        echo $this->ev->display($this->showTitle);
    }
}
