<?php
namespace SuiteCRM\Modules\Administration\PDF\MVC;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use LoggerManager;
use SuiteCRM\PDF\PDFWrapper;
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
        global $mod_strings, $app_list_strings, $app_strings, $sugar_config;

        $errors = [];
        $this->smarty->assign('MOD', $mod_strings);
        $this->smarty->assign('APP', $app_strings);
        $this->smarty->assign('APP_LIST', $app_list_strings);
        $this->smarty->assign('LANGUAGES', get_languages());
        $this->smarty->assign('JAVASCRIPT', get_set_focus_js());
        $this->smarty->assign('error', $errors);
        $this->smarty->assign('BUTTONS', $this->getButtons());

        if (empty($sugar_config['pdf'])) {
            LoggerManager::getLogger()->warn('Configuration does not contains default PDF settings.');
        }

        $pdfSettings = $sugar_config['pdf'] ?? null;
        $this->smarty->assign('config', $pdfSettings);
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

        $this->smarty->assign('MOD', $mod_strings);
        $this->smarty->assign('APP', $app_strings);

        return $this->smarty->fetch('modules/Administration/PDF/buttons.tpl');
    }

    /**
     * Returns an associative array with their class name and translated label
     *
     * @return array
     */
    protected function getEngines(): array
    {
        $engines = [];

        foreach (PDFWrapper::getEngines() as $engine) {
            $engines[$engine] = StringUtils::camelToTranslation($engine);
        }

        return $engines;
    }
}
