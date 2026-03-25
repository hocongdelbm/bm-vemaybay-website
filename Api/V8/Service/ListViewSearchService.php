<?php

namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\JsonApi\Helper\AttributeObjectHelper;
use Api\V8\JsonApi\Helper\PaginationObjectHelper;
use Api\V8\JsonApi\Helper\RelationshipObjectHelper;
use Api\V8\JsonApi\Response\AttributeResponse;
use Api\V8\JsonApi\Response\DataResponse;
use Api\V8\JsonApi\Response\DocumentResponse;
use Api\V8\Param\ListViewSearchParams;
use JsonSerializable;
use SearchForm;
use SuiteCRM\LangText;
use ListViewFacade;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/../../../include/SearchForm/SearchForm2.php';
include_once __DIR__ . '/../../../include/ListView/ListViewFacade.php';

/**
 * ListViewSearchService
 *
 * @author gyula
 */
class ListViewSearchService
{

    /**
     * @var BeanManager
     */
    protected $beanManager;

    /**
     * @param BeanManager $beanManager
     * @param AttributeObjectHelper $attributeHelper
     * @param RelationshipObjectHelper $relationshipHelper
     * @param PaginationObjectHelper $paginationHelper
     */
    public function __construct(
        BeanManager $beanManager
    ) {
        $this->beanManager = $beanManager;
    }

    /**
     *
     * @param LangText $trans
     * @param array $data
     * @param string $part
     * @param string $valueKey
     * @param array $displayColumns
     * @return array
     */
    protected function getDataTranslated($trans, $data, $part, $valueKey, $displayColumns)
    {
        foreach ($data[$part] as $key => $value) {
            $text = null;
            if (isset($value[$valueKey])) {
                $text = $value[$valueKey];
            } elseif (isset($value['name']) && isset($displayColumns[strtoupper($value['name'])]['label'])) {
                $text = $displayColumns[strtoupper($value['name'])]['label'];
            } else {
                \LoggerManager::getLogger()->warn("Not found translation text key for search defs for selected module field: $key");
            }

            $label = $text ? $trans->getText($text) : $text;
            $data[$part][$key][$valueKey] = $label;
        }

        return $data;
    }

    /**
     * @param ListViewSearchParams $params
     *
     * @return JsonSerializable
     */
    public function getListViewSearchDefs(ListViewSearchParams $params)
    {
        // retrieving search defs

        $moduleName = $params->getModuleName();
        $searchDefs = SearchForm::retrieveSearchDefs($moduleName);

        // get list view defs
        $displayColumns = ListViewFacade::getDisplayColumns($moduleName);

        // simplified data struct

        $data = [
            'module' => $moduleName,
            'templateMeta' => $searchDefs['searchdefs'][$moduleName]['templateMeta'],
            'basic' => array_values($searchDefs['searchdefs'][$moduleName]['layout']['basic_search']),
            'advanced' => array_values($searchDefs['searchdefs'][$moduleName]['layout']['advanced_search']),
            'fields' => $searchDefs['searchFields'][$moduleName]
        ];

        // translations

        $trans = new LangText(null, null, LangText::USING_ALL_STRINGS, true, false, $moduleName);


        $data = $this->getDataTranslated($trans, $data, 'basic', 'label', $displayColumns);
        $data = $this->getDataTranslated($trans, $data, 'advanced', 'label', $displayColumns);
        $data = $this->getDataTranslated($trans, $data, 'fields', 'vname', $displayColumns);

        // generate response

        $dataResponse = new DataResponse('SearchDefs', null);
        $attributeResponse = new AttributeResponse($data);
        $dataResponse->setAttributes($attributeResponse);

        $response = new DocumentResponse();
        $response->setData($dataResponse);
        return $response;
    }
}
