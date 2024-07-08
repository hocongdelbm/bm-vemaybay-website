<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_LeaveAbsences/EC_LeaveAbsences.php');

class EC_LeaveAbsencesDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_LeaveAbsences/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_LeaveAbsences');
        }

        $this->searchFields = $dashletData['EC_LeaveAbsencesDashlet']['searchFields'];
        $this->columns = $dashletData['EC_LeaveAbsencesDashlet']['columns'];

        $this->seedBean = new EC_LeaveAbsences();        
    }
}
