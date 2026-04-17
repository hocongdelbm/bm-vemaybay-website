<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_HoaDonBan/EC_HoaDonBan.php');

class EC_HoaDonBanDashlet extends DashletGeneric {
    function __construct($id, $def = null) {
        require('modules/EC_HoaDonBan/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_HoaDonBan');
        }

        $this->searchFields = $dashletData['EC_HoaDonBanDashlet']['searchFields'];
        $this->columns = $dashletData['EC_HoaDonBanDashlet']['columns'];
        $this->seedBean = new EC_HoaDonBan();        
    }
}
