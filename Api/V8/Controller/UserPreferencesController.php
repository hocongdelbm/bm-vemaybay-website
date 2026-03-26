<?php

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Param\GetUserPreferencesParams;
use Api\V8\Service\UserPreferencesService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * UserPreferencesController
 *
 * @author gyula
 */
class UserPreferencesController extends BaseController
{

    /**
     * @var UserPreferencesService
     */
    protected $userPreferencesService;

    /**
     * @param UserPreferencesService $userPreferencesService
     */
    public function __construct(UserPreferencesService $userPreferencesService)
    {
        $this->userPreferencesService = $userPreferencesService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @param GetUserPreferencesParams $params
     * @return Response
     */
    public function getUserPreferences(Request $request, Response $response, array $args, GetUserPreferencesParams $params)
    {
        try {
            $jsonResponse = $this->userPreferencesService->getUserPreferences($params);

            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
