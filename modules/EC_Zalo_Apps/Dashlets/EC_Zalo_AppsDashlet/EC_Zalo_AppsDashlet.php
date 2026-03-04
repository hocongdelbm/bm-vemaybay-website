<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Zalo_Apps/EC_Zalo_Apps.php');

class EC_Zalo_AppsDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Zalo_Apps/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo_Apps');
        }

        $this->searchFields = $dashletData['EC_Zalo_AppsDashlet']['searchFields'];
        $this->columns = $dashletData['EC_Zalo_AppsDashlet']['columns'];

        $this->seedBean = new EC_Zalo_Apps();        
    }
}
