<?php

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



use Api\V8\Param\ListViewSearchParams;
use Api\V8\Service\ListViewSearchService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * ListViewSearchController
 *
 * @author gyula
 */
class ListViewSearchController extends BaseController
{

    /**
     * @var ListViewSearchService
     */
    protected $listViewSearchService;

    /**
     * @param ListViewSearchService $listViewSearchService
     */
    public function __construct(ListViewSearchService $listViewSearchService)
    {
        $this->listViewSearchService = $listViewSearchService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param ListViewSearchParams $params
     * @return HttpResponse
     */
    public function getModuleSearchDefs(Request $request, Response $response, array $args, ListViewSearchParams $params)
    {
        try {
            $jsonResponse = $this->listViewSearchService->getListViewSearchDefs($params);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
