<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Zalo_Contacts/EC_Zalo_Contacts.php');

class EC_Zalo_ContactsDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Zalo_Contacts/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo_Contacts');
        }

        $this->searchFields = $dashletData['EC_Zalo_ContactsDashlet']['searchFields'];
        $this->columns = $dashletData['EC_Zalo_ContactsDashlet']['columns'];

        $this->seedBean = new EC_Zalo_Contacts();        
    }
}
