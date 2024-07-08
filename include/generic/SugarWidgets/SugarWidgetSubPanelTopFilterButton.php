<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopFilterButton extends SugarWidgetSubPanelTopButton
{
    public function display($defines, $additionalFormFields = null, $nonbutton = false)
    {
        global $app_strings;

        $button = "<script src='include/SubPanel/SubPanel.js'></script>";

        $button .= "<input class='button' type='button'  value='".$app_strings['LBL_SUBPANEL_FILTER_LABEL']."'  id='". $this->getWidgetId() ."'  name='".$app_strings['LBL_SUBPANEL_FILTER_LABEL']."'  title='".$app_strings['LBL_SUBPANEL_FILTER_LABEL']."' onclick=\"showSearchPanel('" .  strtolower($this->widget_id) . "');return false;\" />";

        return $button;
    }
}
