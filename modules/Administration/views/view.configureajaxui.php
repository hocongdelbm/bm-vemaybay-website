<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ViewConfigureAjaxUI extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        return array(
            "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME') . "</a>",
            translate('LBL_CONFIG_AJAX')
        );
    }

    /**
     * @see SugarView::preDisplay()
     */
    public function preDisplay()
    {
        global $current_user;

        if (!is_admin($current_user)) {
            sugar_die("Unauthorized access to administration.");
        }
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        global $sugar_config, $moduleList;
        //create array of subpanels to show, used to create Drag and Drop widget
        $enabled = array();
        $disabled = array();
        $banned = ajaxBannedModules();

        foreach ($moduleList as $module) {
            if (!in_array($module, $banned)) {
                $enabled[] = array("module" => $module, 'label' => translate($module));
            }
        }
        if (!empty($sugar_config['addAjaxBannedModules'])) {
            foreach ($sugar_config['addAjaxBannedModules'] as $module) {
                $disabled[] = array("module" => $module, 'label' => translate($module));
            }
        }

        $this->ss->assign('enabled_mods', json_encode($enabled));
        $this->ss->assign('disabled_mods', json_encode($disabled));
        $this->ss->assign('title', $this->getModuleTitle(false));

        echo $this->ss->fetch('modules/Administration/templates/ConfigureAjaxUI.tpl');
    }
}
