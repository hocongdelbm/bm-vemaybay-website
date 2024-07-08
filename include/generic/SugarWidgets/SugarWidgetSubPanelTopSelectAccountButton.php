<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopSelectAccountButton extends SugarWidgetSubPanelTopSelectButton
{
    public function display($widget_data, $additionalFormFields = null, $nonbutton = false)
    {
        /*
        * i.dymovsky
        * Because when user role can't edit Accounts, it also can't edit Membership Organizations. Select button leads to change MO list
        * See bug 25633
        * Bug25633 code change start
        */
        if (!ACLController::checkAccess($widget_data["module"], "edit", true)) {
            return ;
        }
        /*
        * Bug25633 code change end
        */
        
        return parent::display($widget_data);
    }
}
