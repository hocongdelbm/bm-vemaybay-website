<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/EditView/QuickCreate.php');



class BugsQuickCreate extends QuickCreate
{
    public $javascript;

    public function process()
    {
        global $current_user, $timedate, $app_list_strings, $current_language, $mod_strings;
        $mod_strings = return_module_language($current_language, 'Bugs');

        parent::process();

        $this->ss->assign("PRIORITY_OPTIONS", get_select_options_with_id($app_list_strings['bug_priority_dom'], $app_list_strings['bug_priority_default_key']));
        $this->ss->assign("STATUS_OPTIONS", get_select_options_with_id($app_list_strings['bug_status_dom'], $app_list_strings['bug_status_default_key']));
        $this->ss->assign("TYPE_OPTIONS", get_select_options_with_id($app_list_strings['bug_type_dom'], $app_list_strings['bug_type_default_key']));

        if ($this->viaAJAX) { // override for ajax call
            $this->ss->assign('saveOnclick', "onclick='if(check_form(\"bugsQuickCreate\")) return SUGAR.subpanelUtils.inlineSave(this.form.id, \"bugs\"); else return false;'");
            $this->ss->assign('cancelOnclick', "onclick='return SUGAR.subpanelUtils.cancelCreate(\"subpanel_bugs\")';");
        }

        $this->ss->assign('viaAJAX', $this->viaAJAX);

        $this->javascript = new javascript();
        $this->javascript->setFormName('bugsQuickCreate');

        $focus = BeanFactory::newBean('Bugs');
        $this->javascript->setSugarBean($focus);
        $this->javascript->addAllFields('');

        $this->ss->assign('additionalScripts', $this->javascript->getScript(false));
    }
}
