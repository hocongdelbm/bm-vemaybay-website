<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Payment_Voucher/EC_Payment_Voucher.php');

class EC_Payment_VoucherDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Payment_Voucher/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Payment_Voucher');
        }

        $this->searchFields = $dashletData['EC_Payment_VoucherDashlet']['searchFields'];
        $this->columns = $dashletData['EC_Payment_VoucherDashlet']['columns'];

        $this->seedBean = new EC_Payment_Voucher();        
    }
}
