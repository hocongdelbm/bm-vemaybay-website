<?php

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\ListViewColumnsParams;
use Api\V8\Service\ListViewService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * ListViewController
 *
 * @author gyula
 */
class ListViewController extends BaseController
{

    /**
     * @var ListViewService
     */
    protected $listViewService;

    /**
     * @param ListViewService $listViewService
     */
    public function __construct(ListViewService $listViewService)
    {
        $this->listViewService = $listViewService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param ListViewColumnsParams $params
     * @return HttpResponse
     */
    public function getListViewColumns(Request $request, Response $response, array $args, ListViewColumnsParams $params)
    {
        try {
            $jsonResponse = $this->listViewService->getListViewDefs($params);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
