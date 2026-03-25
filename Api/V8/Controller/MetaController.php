<?php

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\GetFieldListParams;
use Api\V8\Service\MetaService;
use Api\V8\Service\UserService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * MetaController
 */
class MetaController extends BaseController
{

    /**
     * @var UserService
     */
    private $metaService;

    /**
     * @param MetaService $metaService
     */
    public function __construct(MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getModuleList(Request $request, Response $response, array $args)
    {
        try {
            $jsonResponse = $this->metaService->getModuleList($request);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param GetFieldListParams $fieldListParams
     * @return Response
     */
    public function getFieldList(Request $request, Response $response, array $args, GetFieldListParams $fieldListParams)
    {
        try {
            $jsonResponse = $this->metaService->getFieldList($request, $fieldListParams);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getSwaggerSchema(Request $request, Response $response)
    {
        try {
            $jsonResponse = $this->metaService->getSwaggerSchema();

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
