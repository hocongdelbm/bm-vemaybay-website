<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Booking_Details/EC_Booking_Details.php');

class EC_Booking_DetailsDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Booking_Details/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Booking_Details');
        }

        $this->searchFields = $dashletData['EC_Booking_DetailsDashlet']['searchFields'];
        $this->columns = $dashletData['EC_Booking_DetailsDashlet']['columns'];

        $this->seedBean = new EC_Booking_Details();        
    }
}
