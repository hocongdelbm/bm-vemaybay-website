<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/EC_Payment_Types/EC_Payment_Types.php');

class EC_Payment_TypesDashlet extends DashletGeneric {
    function __construct($id, $def = null)
    {
        global $current_user, $app_strings;
        require('modules/EC_Payment_Types/metadata/dashletviewdefs.php');

        parent::__construct($id, $def);

        if (empty($def['title'])) {
            $this->title = translate('LBL_HOMEPAGE_TITLE', 'EC_Payment_Types');
        }

        $this->searchFields = $dashletData['EC_Payment_TypesDashlet']['searchFields'];
        $this->columns = $dashletData['EC_Payment_TypesDashlet']['columns'];

        $this->seedBean = new EC_Payment_Types();        
    }
}
