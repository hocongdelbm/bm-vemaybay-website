<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
 
require_once('include/EditView/QuickCreate.php');



class AccountsQuickCreate extends QuickCreate
{
    public $javascript;
    
    public function process()
    {
        global $current_user, $timedate, $app_list_strings, $current_language, $mod_strings;
        $mod_strings = return_module_language($current_language, 'Accounts');
        
        parent::process();
  
        if ($this->viaAJAX) { // override for ajax call
            $this->ss->assign('saveOnclick', "onclick='if(check_form(\"accountsQuickCreate\")) return SUGAR.subpanelUtils.inlineSave(this.form.id, \"accounts\"); else return false;'");
            $this->ss->assign('cancelOnclick', "onclick='return SUGAR.subpanelUtils.cancelCreate(\"subpanel_accounts\")';");
        }
        
        $this->ss->assign('viaAJAX', $this->viaAJAX);

        $this->javascript = new javascript();
        $this->javascript->setFormName('accountsQuickCreate');
        
        $focus = BeanFactory::newBean('Accounts');
        $this->javascript->setSugarBean($focus);
        $this->javascript->addAllFields('');

        $this->ss->assign('additionalScripts', $this->javascript->getScript(false));
    }
}
