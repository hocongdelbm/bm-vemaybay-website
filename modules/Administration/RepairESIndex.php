<?php
use SuiteCRM\Search\ElasticSearch\ElasticSearchIndexer;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
global $sugar_config, $mod_strings;

if (!is_admin($current_user)) {
    sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}

if (empty($sugar_config['search']['ElasticSearch']['enabled']) || isFalse($sugar_config['search']['ElasticSearch']['enabled'])) {
    echo $mod_strings['LBL_SETUP_ELASTICSEARCH'];
    return;
}

echo $mod_strings['LBL_REPAIR_ELASTICSEARCH_INDEX_DONE'];

// repair elastic search index
ElasticSearchIndexer::repairElasticsearchIndex();

