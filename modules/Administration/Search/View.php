<?php
namespace SuiteCRM\Modules\Administration\Search;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use SuiteCRM\Search\SearchModules;
use SuiteCRM\Search\SearchWrapper;
use SuiteCRM\Modules\Administration\Search\MVC\View as AbstractView;

/**
 * Class View renders the Search settings.
 */
class View extends AbstractView
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/view.tpl');
    }

    public function preDisplay(): void
    {
        parent::preDisplay();

        $this->smarty->assign('selectedController', SearchWrapper::getController());
        $this->smarty->assign('selectedEngine', SearchWrapper::getDefaultEngine());

        $legacyEngines = [
            'BasicSearchEngine' => translate('LBL_BASIC_SEARCH_ENGINE'),
            'BasicAndAodEngine' => translate('LBL_BASIC_AND_AOD_ENGINE'),
        ];

        $engines = $this->getEngines();
        $engines = array_merge($legacyEngines, $engines);
        unset($engines['LuceneSearchEngine']);

        $this->smarty->assign('engines', [
            translate('LBL_SEARCH_WRAPPER_ENGINES') => $engines
        ]);
    }

    /**
     * @see SugarView::display()
     */
    public function display(): void
    {
        global $mod_strings, $app_strings;

        $this->smarty->assign('APP', $app_strings);
        $this->smarty->assign('MOD', $mod_strings);

        $modules = SearchModules::getAllModules();

        $this->smarty->assign('enabled_modules', json_encode($modules['enabled'], JSON_THROW_ON_ERROR));
        $this->smarty->assign('disabled_modules', json_encode($modules['disabled'], JSON_THROW_ON_ERROR));

        $template = $this->templateFile;
        if (file_exists('custom/' . $this->templateFile)) {
            $template = 'custom/' . $this->templateFile;
        }

        $this->smarty->display($template);
    }
}
