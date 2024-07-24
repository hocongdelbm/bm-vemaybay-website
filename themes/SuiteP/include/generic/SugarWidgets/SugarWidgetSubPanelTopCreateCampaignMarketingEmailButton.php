<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelTopCreateCampaignMarketingEmailButton extends SugarWidgetSubPanelTopButton
{
    public function display($layout_def, $additionalFormFields = null, $nonbutton = false)
    {
        global $app_strings;

        $id = $layout_def['focus']->id;
        $module = $layout_def['focus']->module_name;

        $href = "index.php?module=$module&action=WizardMarketing&campaign_id=$id" . (!empty($layout_def['func']) ? '&func=' . $layout_def['func'] : '');

        $label = $app_strings['LBL_CREATE_BUTTON_LABEL'];

        return '<a onclick="location.href=\'' . $href . '\';">' . $label . '</a>';
    }
}
