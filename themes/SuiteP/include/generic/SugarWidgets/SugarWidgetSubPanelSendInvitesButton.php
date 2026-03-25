<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php');

class SugarWidgetSubPanelSendInvitesButton extends SugarWidgetSubPanelTopButton
{
    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        return '';
    }
}
