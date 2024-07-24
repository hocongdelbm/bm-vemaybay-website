<?php
namespace SuiteCRM\Modules\Administration\Search;

use SuiteCRM\Search\SearchConfigurator;
use SuiteCRM\Modules\Administration\Search\MVC\Controller as AbstractController;
use Configurator;
use Exception;
use SuiteCRM\Search\SearchModules;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/**
 * Class Controller handles the the actions for the search settings.
 */
class Controller extends AbstractController
{
    public function __construct()
    {
        parent::__construct(new View());
    }

    /**
     * Saves the configuration from a POST request.
     *
     * If called from ajax it will return a json.
     * @throws Exception
     */
    public function doSave(): void
    {
        $searchEngine = filter_input(INPUT_POST, 'search-engine', FILTER_SANITIZE_STRING);
        $aod = $searchEngine === 'BasicAndAodEngine';

        SearchConfigurator::make()
            ->setEngine($searchEngine)
            ->save();

        SearchModules::saveGlobalSearchSettings();
        $this->doSaveAODConfig($aod);

        if ($this->isAjax()) {
            $this->yieldJson(['status' => 'success']);
        }

        $this->redirect('index.php?module=Administration&action=index');
    }

    /**
     * Saves the configuration getting data from POST.
     * @param bool $enabled
     */
    public function doSaveAODConfig(bool $enabled): void
    {
        $cfg = new Configurator();

        if (!array_key_exists('aod', $cfg->config)) {
            $cfg->config['aod'] = [
                'enable_aod' => '',
            ];
        }

        $cfg->config['aod']['enable_aod'] = $enabled;

        $cfg->saveConfig();
    }
}
