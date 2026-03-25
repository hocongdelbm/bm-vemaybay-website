<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopSelectAccountButton extends SugarWidgetSubPanelTopSelectButton
{
    public function display($widget_data, $additionalFormFields = null, $nonbutton = false)
    {
        if (!ACLController::checkAccess($widget_data["module"], "edit", true)) {
            return;
        }

        return parent::display($widget_data);
    }
}
