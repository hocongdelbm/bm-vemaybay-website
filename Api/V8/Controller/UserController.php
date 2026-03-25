<?php

namespace Api\V8\Controller;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use Api\V8\Service\UserService;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * UserController
 *
 * @author gyula
 */
class UserController extends BaseController
{

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getCurrentUser(Request $request, Response $response, array $args)
    {
        try {
            $jsonResponse = $this->userService->getCurrentUser($request);
            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}
