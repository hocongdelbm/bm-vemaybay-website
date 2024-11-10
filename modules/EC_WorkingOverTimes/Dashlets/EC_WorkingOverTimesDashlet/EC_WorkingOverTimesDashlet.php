<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_WorkingOverTimes/EC_WorkingOverTimes.php');

class EC_WorkingOverTimesDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_WorkingOverTimes/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_WorkingOverTimes');
        }

        $this->searchFields = $dashletData['EC_WorkingOverTimesDashlet']['searchFields'];
        $this->columns = $dashletData['EC_WorkingOverTimesDashlet']['columns'];

        $this->seedBean = new EC_WorkingOverTimes();        
    }
}
