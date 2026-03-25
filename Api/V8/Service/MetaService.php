<?php

namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\Helper\ModuleListProvider;
use Api\V8\JsonApi\Response\AttributeResponse;
use Api\V8\JsonApi\Response\DataResponse;
use Api\V8\JsonApi\Response\DocumentResponse;
use Api\V8\Param\GetFieldListParams;
use Slim\Http\Request;
use SuiteCRM\Exception\Exception;
use SuiteCRM\Exception\NotAllowedException;
use SuiteCRM\Exception\NotFoundException;

/**
 * Class MetaService
 * @package Api\V8\Service
 */
class MetaService
{
    /**
     * @var BeanManager
     */
    private $beanManager;

    /**
     * @var ModuleListProvider
     */
    private $moduleListProvider;

    private static $allowedVardefFields = [
        'type',
        'dbType',
        'source',
        'relationship',
        'default',
        'len',
        'precision',
        'comments',
        'required',
    ];

    /**
     * UserService constructor.
     * @param BeanManager $beanManager
     */
    public function __construct(
        BeanManager $beanManager,
        ModuleListProvider $moduleListProvider
    ) {
        $this->beanManager = $beanManager;
        $this->moduleListProvider = $moduleListProvider;
    }

    /**
     * Build the response with a list of modules to return.
     *
     * @param Request $request
     * @return DocumentResponse
     */
    public function getModuleList(Request $request)
    {
        $modules = $this->moduleListProvider->getModuleList();

        $dataResponse = new DataResponse('modules', '');
        $attributeResponse = new AttributeResponse($modules);
        $dataResponse->setAttributes($attributeResponse);

        $response = new DocumentResponse();
        $response->setData($dataResponse);

        return $response;
    }

    /**
     * Build the response with a list of fields to return.
     *
     * @param Request $request
     * @param GetFieldListParams $fieldListParams
     * @return DocumentResponse
     * @throws NotAllowedException
     */
    public function getFieldList(Request $request, GetFieldListParams $fieldListParams)
    {
        $fieldList = $this->buildFieldList($fieldListParams->getModule());

        $dataResponse = new DataResponse('fields', '');
        $attributeResponse = new AttributeResponse($fieldList);
        $dataResponse->setAttributes($attributeResponse);

        $response = new DocumentResponse();
        $response->setData($dataResponse);

        return $response;
    }

    /**
     * @param string $module
     * @throws NotAllowedException
     */
    private function checkIfUserHasModuleAccess($module)
    {
        global $current_user;

        $modules = query_module_access_list($current_user);
        \ACLController::filterModuleList($modules, false);

        if (!in_array($module, $modules, true)) {
            throw new NotAllowedException('The API user does not have access to this module.');
        }
    }

    /**
     * Build the list of fields for a given module.
     *
     * @param string $module
     * @return array
     * @throws NotAllowedException
     */
    private function buildFieldList($module)
    {
        $this->checkIfUserHasModuleAccess($module);
        $bean = $this->beanManager->newBeanSafe($module);
        $fieldList = [];
        foreach ($bean->field_defs as $fieldName => $fieldDef) {
            $fieldList[$fieldName] = $this->pruneVardef($fieldDef);
        }

        return $fieldList;
    }

    /**
     * We only allow certain fields from the vardefs to be returned in the field list.
     *
     * @param array $def
     * @return array
     */
    private function pruneVardef($def)
    {
        $pruned = [];
        foreach ($def as $var => $val) {
            if (in_array($var, static::$allowedVardefFields, true)) {
                $pruned[$var] = $val;
            }
        }
        if (!isset($def['required'])) {
            $pruned['required'] = false;
        }
        if (!isset($def['dbType'])) {
            $pruned['dbType'] = $def['type'];
        }

        return $pruned;
    }

    /**
     * Build the response with the swagger schema.
     *
     * @return DocumentResponse
     * @throws NotFoundException
     * @throws Exception
     */
    public function getSwaggerSchema()
    {
        $path = __DIR__ . '/../../docs/swagger/swagger.json';
        if (!file_exists($path)) {
            throw new NotFoundException(
                'Unable to find JSON Api Schema file: ' . $path
            );
        }

        $swaggerFile = file_get_contents($path);

        if (!$swaggerFile) {
            throw new Exception(
                'Unable to read JSON Api Schema file: ' . $path
            );
        }

        return json_decode($swaggerFile, true);
    }
}
