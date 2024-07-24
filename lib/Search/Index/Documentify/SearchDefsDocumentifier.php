<?php

namespace SuiteCRM\Search\Index\Documentify;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Exception;
use LoggerManager;
use Monolog\Logger;
use ParserSearchFields;
use SuiteCRM\Log\CliLoggerHandler;
use SuiteCRM\Log\SugarLoggerHandler;
use SuiteCRM\Utility\ArrayMapper;

require_once 'modules/ModuleBuilder/parsers/parser.searchfields.php';

/**
 * This class converts a SugarBean into a document using the customisable Search Defs framework.
 *
 * @see ParserSearchFields
 */
class SearchDefsDocumentifier extends AbstractDocumentifier
{
    /** @var array a cache with fields definition */
    protected $fields = [];
    /** @var ArrayMapper */
    protected $mapper = null;
    /** @var Logger */
    protected $logger;

    /**
     * SearchDefsDocumentifier constructor.
     */
    public function __construct()
    {
        try {
            $this->logger = new Logger('SearchDefsDocumentifier', [
                new CliLoggerHandler(),
                new SugarLoggerHandler(),
            ]);
        } catch (Exception $exception) {
            LoggerManager::getLogger()->error('Failed to start Monolog loggers');
        }

        $this->mapper = ArrayMapper::make()
            ->loadYaml(__DIR__ . '/SearchDefsDocumentifier.yml')
            ->setHideEmptyValues(true);
    }

    /** @inheritdoc */
    public function documentify(\SugarBean $bean, ParserSearchFields $parser = null)
    {
        $fields = &$this->getFieldsToIndexCached($bean, $parser);

        $body = &$this->parseBeans($bean, $fields);

        $body = $this->mapper
            ->setMappable($body)
            ->map();

        $this->fixPhone($body);
        $this->fixEmails($bean, $body);

        return $body;
    }

    /**
     * Parses the Search Defs files and creates a map of fields to index for a given module.
     *
     * The mapping is cached in the class property `$fields`.
     *
     * @param string                  $module
     * @param ParserSearchFields|null $parser
     *
     * @return string[]
     */
    protected function getFieldsToIndex($module, ParserSearchFields $parser = null)
    {
        if (empty($parser)) {
            $parser = new ParserSearchFields($module);
        }

        $fields = $parser->getSearchFields()[$module];

        $parsedFields = [];

        $badKeys = ['favorites_only', 'open_only', 'do_not_call', 'email', 'optinprimary'];
        $goodOperators = ['=', 'in'];

        foreach ($fields as $key => $field) {
            if (in_array($key, $badKeys)) {
                continue;
            }

            if (isset($field['query_type']) && $field['query_type'] != 'default') {
                $this->logger->warn("[$module]->$key is not a supported query type [{$field['query_type']}]");
                continue;
            };

            if (!empty($field['operator']) && !in_array($field['operator'], $goodOperators)) {
                $this->logger->warn("[$module]->$key has an unsupported operator [{$field['operator']}]");
                $this->logger->warn("field:\n" . json_encode($field, JSON_PRETTY_PRINT));
                continue;
            }

            if (strpos($key, 'range_date') !== false) {
                continue;
            }

            if (empty($field['db_field'])) {
                $parsedFields[] = $key;
                continue;
            }

            foreach ($field['db_field'] as $db_field) {
                $parsedFields[$key][] = $db_field;
            }
        }

        // injects the standard metadata fields as they are not present in the searchdefs
        $parsedFields = array_merge($parsedFields, $this->getMetaData());

        return $parsedFields;
    }

    /**
     * Cached version of getFieldsToIndex().
     *
     * @see getFieldsToIndex
     *
     * @param \SugarBean         $bean
     * @param ParserSearchFields $parser
     *
     * @return array
     */
    private function &getFieldsToIndexCached(\SugarBean $bean, ParserSearchFields $parser = null)
    {
        $module_name = $bean->module_name;

        if (empty($this->fields[$module_name])) {
            $this->fields[$module_name] = $this->getFieldsToIndex($module_name, $parser);
        }

        return $this->fields[$module_name];
    }

    /**
     * @param \SugarBean $bean
     * @param array      $fields
     *
     * @return mixed
     */
    private function &parseBeans(\SugarBean $bean, array &$fields)
    {
        $body = [];

        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subvalue) {
                    if (property_exists($bean, $subvalue) && !empty($bean->$subvalue)) {
                        $body[$key][$subvalue] = $bean->$subvalue;
                    }
                }
                continue;
            }

            if (property_exists($bean, $value) && !empty($bean->$value)) {
                $body[$value] = $bean->$value;
            }
        }

        return $body;
    }
}
