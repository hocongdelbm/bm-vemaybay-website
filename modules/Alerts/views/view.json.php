<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
class AlertsViewDefault extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        return array('Alerts');
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay() {}

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        $this->ss->assign('json', $this->ss->get_config_vars());
        echo $this->ss->fetch('modules/Alerts/templates/json.tpl');
        die();
    }
}
