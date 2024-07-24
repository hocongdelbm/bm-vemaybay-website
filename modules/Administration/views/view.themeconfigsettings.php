<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/Administration/Forms.php');
require_once('modules/Configurator/Configurator.php');
require_once('include/MVC/View/SugarView.php');
        
class AdministrationViewThemeConfigSettings extends SugarView
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;
        
        return array(
           "<a href='index.php?module=Administration&action=index'>".$mod_strings['LBL_MODULE_NAME']."</a>",
           $mod_strings['LBL_THEME_SETTINGS']
           );
    }
    
    /**
     * @see SugarView::process()
     */
    public function process()
    {
        global $current_user;
        if (!is_admin($current_user)) {
            sugar_die("Unauthorized access to administration.");
        }

        // Check if the theme is valid
        if (!isset($_REQUEST['theme']) || !in_array($_REQUEST['theme'], array_keys(SugarThemeRegistry::allThemes()))) {
            sugar_die("theme is invalid.");
        }

        if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
            $theme_config = SugarThemeRegistry::getThemeConfig($_REQUEST['theme']);

            $configurator = new Configurator();

            foreach ($theme_config as $name => $def) {
                if (isset($_REQUEST[$name])) {
                    if ($_REQUEST[$name] == 'true') {
                        $_REQUEST[$name] = true;
                    } else {
                        if ($_REQUEST[$name] == 'false') {
                            $_REQUEST[$name] = false;
                        }
                    }
                    $configurator->config['theme_settings'][$_REQUEST['theme']][$name] = $_REQUEST[$name];
                }
            }
            $configurator->handleOverride();
            sleep(3);
            SugarApplication::redirect('index.php?module=Administration&action=ThemeSettings');
            exit();
        }

        parent::process();
    }
    
    /**
     * display the form
     */
    public function display()
    {
        global $mod_strings, $app_strings;

        $this->ss->assign('config', SugarThemeRegistry::getThemeConfig($_REQUEST['theme']));
        $this->ss->assign('mod', $mod_strings);
        $this->ss->assign('APP', $app_strings);
        
        echo $this->getModuleTitle(false);
        echo $this->ss->fetch('modules/Administration/templates/themeConfigSettings.tpl');
    }
}
