<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelRemoveButtonAccount extends SugarWidgetSubPanelRemoveButton
{
    /**
     *
     * @see SugarWidgetSubPanelRemoveButton::displayList()
     * @param $layout_def
     * @return bool|string
     */
    public function displayList(&$layout_def)
    {
        if (!$layout_def['EditView']) {
            return false;
        }
        return parent::displayList($layout_def);
    }
}
