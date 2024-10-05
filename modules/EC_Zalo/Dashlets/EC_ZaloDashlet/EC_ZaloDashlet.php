<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Zalo/EC_Zalo.php');

class EC_ZaloDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Zalo/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Zalo');
        }

        $this->searchFields = $dashletData['EC_ZaloDashlet']['searchFields'];
        $this->columns = $dashletData['EC_ZaloDashlet']['columns'];

        $this->seedBean = new EC_Zalo();        
    }
}
