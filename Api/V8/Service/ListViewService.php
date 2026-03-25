<?php

namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\JsonApi\Helper\AttributeObjectHelper;
use Api\V8\JsonApi\Helper\PaginationObjectHelper;
use Api\V8\JsonApi\Helper\RelationshipObjectHelper;
use Api\V8\JsonApi\Response\AttributeResponse;
use Api\V8\Param\ListViewColumnsParams;
use ListViewFacade;
use SuiteCRM\LangText;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

include_once __DIR__ . '/../../../include/ListView/ListViewFacade.php';

/**
 * ListViewService
 *
 * @author gyula
 */
class ListViewService
{

    /**
     * an exact match to ListViewColumnInterface struct of Angular front-end
     *
     * @var array
     */
    protected static $listViewColumnInterface = [
        'fieldName' => '',
        'width' => '',
        'label' => '',
        'link' => false,
        'default' => false,
        'module' => '',
        'id' => '',
        'sortable' => false,
        'customCode' => '', // deprecated from legacy (using only on PHP front-end)
    ];

    /**
     * @var BeanManager
     */
    protected $beanManager;

    /**
     * @var AttributeObjectHelper
     */
    protected $attributeHelper;

    /**
     * @var RelationshipObjectHelper
     */
    protected $relationshipHelper;

    /**
     * @var PaginationObjectHelper
     */
    protected $paginationHelper;

    /**
     * @param BeanManager $beanManager
     * @param AttributeObjectHelper $attributeHelper
     * @param RelationshipObjectHelper $relationshipHelper
     * @param PaginationObjectHelper $paginationHelper
     */
    public function __construct(
        BeanManager $beanManager,
        AttributeObjectHelper $attributeHelper,
        RelationshipObjectHelper $relationshipHelper,
        PaginationObjectHelper $paginationHelper
    ) {
        $this->beanManager = $beanManager;
        $this->attributeHelper = $attributeHelper;
        $this->relationshipHelper = $relationshipHelper;
        $this->paginationHelper = $paginationHelper;
    }

    /**
     * @param ListViewColumnsParams $params
     *
     * @return JsonSerializable
     */
    public function getListViewDefs(ListViewColumnsParams $params)
    {
        $moduleName = $params->getModuleName();
        /** @var SugarBean */
        $bean = \BeanFactory::getBean($moduleName);

        $text = new LangText(null, null, LangText::USING_ALL_STRINGS, true, false, $moduleName);
        $displayColumns = ListViewFacade::getDisplayColumns($moduleName);
        $data = [];
        foreach ($displayColumns as $key => $column) {
            $column = array_merge(self::$listViewColumnInterface, $column);
            $column['fieldName'] = $key; // get the vardef instead this "intuitive fieldName"
            $translated = $text->getText($column['label']);
            if (!$translated) {
                $translated = $text->getText($bean->field_name_map[strtolower($key)]['vname']);
            }
            $column['label'] = $translated ? $translated : $column['label'];

            // TODO: validate the column name (for e.g label and name should be requered etc...) also check the ListViewColumnInterface keys are match..
            $data[] = $column;
        }
        $response = new AttributeResponse($data);
        return $response;
    }
}
