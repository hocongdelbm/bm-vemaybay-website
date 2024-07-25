<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/EditView/QuickCreate.php');

class ContactsQuickCreate extends QuickCreate
{
    public $javascript;
    
    public function process()
    {
        global $current_user, $timedate, $app_list_strings, $current_language, $mod_strings;
        $mod_strings = return_module_language($current_language, 'Contacts');
        
        parent::process();
  
        $this->ss->assign("SALUTATION_OPTIONS", get_select_options_with_id($app_list_strings['salutation_dom'], ''));

        if ($this->viaAJAX) { // override for ajax call
            $this->ss->assign('saveOnclick', "onclick='if(check_form(\"contactsQuickCreate\")) return SUGAR.subpanelUtils.inlineSave(this.form.id, \"contacts\"); else return false;'");
            $this->ss->assign('cancelOnclick', "onclick='return SUGAR.subpanelUtils.cancelCreate(\"subpanel_contacts\")';");
        }
        
        $this->ss->assign('viaAJAX', $this->viaAJAX);

        $this->javascript = new javascript();
        $this->javascript->setFormName('contactsQuickCreate');
        
        $focus = BeanFactory::newBean('Contacts');
        $this->javascript->setSugarBean($focus);
        $this->javascript->addAllFields('');

        $this->ss->assign('additionalScripts', $this->javascript->getScript(false));
    }
}
