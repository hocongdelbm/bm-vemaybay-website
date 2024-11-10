<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class AdministrationController extends SugarController
{
    public function action_savetabs()
    {
        require_once('include/SubPanel/SubPanelDefinitions.php');
        require_once('modules/MySettings/TabController.php');


        global $current_user, $app_strings;

        if (!is_admin($current_user)) {
            sugar_die($app_strings['ERR_NOT_ADMIN']);
        }

        // handle the tabs listing
        $toDecode = html_entity_decode($_REQUEST['enabled_tabs'], ENT_QUOTES);
        $enabled_tabs = json_decode($toDecode);
        $tabs = new TabController();
        $tabs->set_system_tabs($enabled_tabs);
        $tabs->set_users_can_edit(isset($_REQUEST['user_edit_tabs']) && $_REQUEST['user_edit_tabs'] == 1);

        // handle the subpanels
        if (isset($_REQUEST['disabled_tabs'])) {
            $disabledTabs = json_decode(html_entity_decode($_REQUEST['disabled_tabs'], ENT_QUOTES));
            $disabledTabsKeyArray = $tabs->get_key_array($disabledTabs);
            $subPanelDefinition = new SubPanelDefinitions($this->bean);
            $subPanelDefinition->set_hidden_subpanels($disabledTabsKeyArray);
        }

        header("Location: index.php?module=Administration&action=ConfigureTabs");
    }

    public function action_savelanguages()
    {
        global $sugar_config;
        $toDecode = html_entity_decode($_REQUEST['disabled_langs'], ENT_QUOTES);
        $disabled_langs = json_decode($toDecode);
        $toDecode = html_entity_decode($_REQUEST['enabled_langs'], ENT_QUOTES);
        $enabled_langs = json_decode($toDecode);
        $cfg = new Configurator();
        $cfg->config['disabled_languages'] = implode(',', $disabled_langs);
        // TODO: find way to enforce order
        $cfg->handleOverride();
        header("Location: index.php?module=Administration&action=Languages");
    }


    /**
     * action_saveglobalsearchsettings
     *
     * This method handles saving the selected modules to display in the Global Search Settings.
     * It instantiates an instance of UnifiedSearchAdvanced and then calls the saveGlobalSearchSettings
     * method.
     *
     */
    public function action_saveglobalsearchsettings()
    {
        global $current_user, $app_strings;

        if (!is_admin($current_user)) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
        }

        try {
            require_once('modules/Home/UnifiedSearchAdvanced.php');
            $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
            $unifiedSearchAdvanced->saveGlobalSearchSettings();

            $return = 'true';
            echo $return;
        } catch (Exception $ex) {
            echo "false";
        }
    }

    /**
     *
     * Merge current FTS config with the new passed parameters:
     *
     * We want to merge the current $sugar_config settings with those passed in
     * to be able to add additional parameters which are currently not supported
     * in the UI (i.e. additional curl settings for elastic search for auth)
     *
     * @param array $config
     * @return array
     */
    protected function mergeFtsConfig($type, $newConfig)
    {
        $currentConfig = SugarConfig::getInstance()->get("full_text_engine.{$type}", array());
        return array_merge($currentConfig, $newConfig);
    }

    public function action_UpdateAjaxUI()
    {
        require_once('modules/Configurator/Configurator.php');
        $cfg = new Configurator();
        $disabled = json_decode(html_entity_decode($_REQUEST['disabled_modules'], ENT_QUOTES));
        $cfg->config['addAjaxBannedModules'] = empty($disabled) ? false : $disabled;
        $cfg->addKeyToIgnoreOverride('addAjaxBannedModules', $disabled);
        $cfg->handleOverride();
        $this->view = "configureajaxui";
    }


    /*
     * action_callRebuildSprites
     *
     * This method is responsible for actually running the SugarSpriteBuilder class to rebuild the sprites.
     * It is called from the ajax request issued by RebuildSprites.php.
     */
    public function action_callRebuildSprites()
    {
        global $current_user;
        $this->view = 'ajax';
        if (function_exists('imagecreatetruecolor')) {
            if (is_admin($current_user)) {
                require_once('modules/UpgradeWizard/uw_utils.php');
                rebuildSprites(false);
            }
        } else {
            echo $mod_strings['LBL_SPRITES_NOT_SUPPORTED'];
            $GLOBALS['log']->error($mod_strings['LBL_SPRITES_NOT_SUPPORTED']);
        }
    }
}
