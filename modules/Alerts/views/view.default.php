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
        $this->ss->assign('Flash', $this->view_object_map['Flash']);
        $this->ss->assign('Results', $this->view_object_map['Results']);
        echo $this->ss->fetch('modules/Alerts/templates/default.tpl');
        die();
    }
}
