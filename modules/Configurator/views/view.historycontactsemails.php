<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


require_once('include/SubPanel/SubPanelDefinitions.php');

class ConfiguratorViewHistoryContactsEmails extends SugarView
{
    public function preDisplay()
    {
        if (!is_admin($GLOBALS['current_user'])) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
        }
    }

    public function display()
    {
        $modules = array();
        foreach ($GLOBALS['beanList'] as $moduleName => $objectName) {
            $bean = BeanFactory::getBean($moduleName);

            if (!($bean instanceof SugarBean)) {
                continue;
            }
            if (empty($bean->field_defs)) {
                continue;
            }

            $subPanel = new SubPanelDefinitions($bean);
            if (empty($subPanel->layout_defs)) {
                continue;
            }
            if (empty($subPanel->layout_defs['subpanel_setup'])) {
                continue;
            }

            $isValid = false;
            foreach ($subPanel->layout_defs['subpanel_setup'] as $subPanelDef) {
                if (empty($subPanelDef['module']) || $subPanelDef['module'] != 'History') {
                    continue;
                }
                if (empty($subPanelDef['collection_list'])) {
                    continue;
                }
                foreach ($subPanelDef['collection_list'] as $v) {
                    if (!empty($v['get_subpanel_data']) && $v['get_subpanel_data'] == 'function:get_emails_by_assign_or_link') {
                        $isValid = true;
                        break 2;
                    }
                }
            }
            if (!$isValid) {
                continue;
            }

            $bean->load_relationships();
            foreach ($bean->get_linked_fields() as $fieldName => $fieldDef) {
                if ($bean->$fieldName->getRelatedModuleName() == 'Contacts') {
                    $modules[$moduleName] = array(
                        'module' => $moduleName,
                        'label' => translate($moduleName),
                        'enabled' => empty($fieldDef['hide_history_contacts_emails'])
                    );
                    break;
                }
            }
        }

        if (!empty($GLOBALS['sugar_config']['hide_history_contacts_emails'])) {
            foreach ($GLOBALS['sugar_config']['hide_history_contacts_emails'] as $moduleName => $flag) {
                $modules[$moduleName]['enabled'] = !$flag;
            }
        }

        $this->ss->assign('modules', $modules);
        $this->ss->display('modules/Configurator/tpls/historyContactsEmails.tpl');
    }
}
