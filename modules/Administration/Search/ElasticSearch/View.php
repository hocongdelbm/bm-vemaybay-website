<?php
/** @noinspection PhpIllegalStringOffsetInspection */

namespace SuiteCRM\Modules\Administration\Search\ElasticSearch;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use LoggerManager;
use SuiteCRM\Modules\Administration\Search\MVC\View as AbstractView;

class View extends AbstractView
{
    /**
     * ElasticSearchSettingsView constructor.
     */
    public function __construct()
    {
        parent::__construct(__DIR__ . '/view.tpl');
    }

    public function preDisplay(): void
    {
        parent::preDisplay();

        global $sugar_config;

        if (!isset($sugar_config['search']['ElasticSearch']) || $sugar_config['search']['ElasticSearch']) {
            LoggerManager::getLogger()->warn('Configuration does not contains Elasticsearch default settings.');
        }

        $elasticsearchConfig = isset($sugar_config['search']['ElasticSearch']) ? $sugar_config['search']['ElasticSearch'] : null;

        $this->smarty->assign('config', $elasticsearchConfig);
    }
}
