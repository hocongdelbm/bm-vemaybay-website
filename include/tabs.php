<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
class SugarWidgetTabs
{
    public $tabs;
    public $current_key;

    public function __construct(&$tabs, $current_key, $jscallback)
    {
        $this->tabs = $tabs;
        $this->current_key = $current_key;
        $this->jscallback = $jscallback;
    }

    public function display()
    {
        $template = new Sugar_Smarty();
        $template->assign('subpanel_tabs', $this->tabs);
        $template->assign('subpanel_tabs_count', count($this->tabs));
        $template->assign('jscallback', $this->jscallback);
        $template->assign('subpanel_current_key', $this->current_key);

        return $template->display('include/tabs.tpl');
    }
}
