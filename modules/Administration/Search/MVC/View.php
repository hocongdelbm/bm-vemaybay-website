<?php
namespace SuiteCRM\Modules\Administration\Search\MVC;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use LoggerManager;
use SuiteCRM\Search\SearchWrapper;
use SuiteCRM\Search\UI\MVC\View as BaseView;
use SuiteCRM\Utility\StringUtils;

/**
 * Class View holds utilities for rendering a template file.
 */
abstract class View extends BaseView
{
    /**
     * Configures translations and global variables.
     *
     * Extend to assign more variable.
     */
    public function preDisplay(): void
    {
        global $mod_strings;
        global $app_list_strings;
        global $app_strings;
        global $sugar_config;

        $errors = [];
        $this->smarty->assign('MOD', $mod_strings);
        $this->smarty->assign('APP', $app_strings);
        $this->smarty->assign('APP_LIST', $app_list_strings);
        $this->smarty->assign('LANGUAGES', get_languages());
        $this->smarty->assign('JAVASCRIPT', get_set_focus_js());
        $this->smarty->assign('error', $errors);
        $this->smarty->assign('BUTTONS', $this->getButtons());

        if (empty($sugar_config['search'])) {
            LoggerManager::getLogger()->warn('Configuration does not contains default search settings.');
        }

        $search = $sugar_config['search'] ?? null;
        $this->smarty->assign('config', $search);
    }

    /**
     * Returns the cancel and save button.
     *
     * @return string
     */
    protected function getButtons(): string
    {
        global $mod_strings;
        global $app_strings;

        return <<<EOQ
    <input title="{$app_strings['LBL_SAVE_BUTTON_TITLE']}"
        accessKey="{$app_strings['LBL_SAVE_BUTTON_KEY']}"
        class="btn btn-primary"
        type="submit"
        name="save"
        onclick="return check_form('ConfigureSettings');"
        value="{$app_strings['LBL_SAVE_BUTTON_LABEL']}" >&nbsp;
    <input title="{$mod_strings['LBL_CANCEL_BUTTON_TITLE']}" 
        onclick="document.location.href='index.php?module=Administration&action=index'"
        class="btn btn-danger"
        type="button"
        name="cancel"
        value="{$app_strings['LBL_CANCEL_BUTTON_LABEL']}" >
EOQ;
    }

    /**
     * Returns an associative array with their class name and translated label
     *
     * @return array
     */
    protected function getEngines(): array
    {
        $engines = [];

        foreach (SearchWrapper::getEngines() as $engine) {
            $engines[$engine] = StringUtils::camelToTranslation($engine);
        }

        return $engines;
    }
}
