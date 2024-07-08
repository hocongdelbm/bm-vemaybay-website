<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php');

class SugarWidgetSubPanelSelectAllButton extends SugarWidgetSubPanelTopButton
{
    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        $button  = "<form method='post' action='/index.php?module=MODULE_NAME&action=CUSTOM_ACTION'>";
        // $button .= "<input id='custom_hidden_1' type='hidden' name='custom_hidden_1' value=''/>";
        $button .= "<input class='btn btn-primary' type='submit' name='Custom Save' value='Select All' />\n</form>";
        return $button;
    }
}
