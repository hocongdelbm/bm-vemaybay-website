<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php');

class SugarWidgetSubPanelSendInvitesButton extends SugarWidgetSubPanelTopButton
{
    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        global $mod_strings;
       
        $button = '<input class="btn btn-primary" onclick="document.location=\'index.php?module=FP_events&action=sendinvitemails&record='.$defines['focus']->id.'\'" name="sendinvites" value="'.$mod_strings['LBL_INVITE_PDF'].'" type="button">';
        return $button;
    }
}
