<?php

namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\JsonApi\Helper\AttributeObjectHelper;
use Api\V8\JsonApi\Helper\RelationshipObjectHelper;
use Api\V8\JsonApi\Response\AttributeResponse;
use Api\V8\JsonApi\Response\DataResponse;
use Api\V8\JsonApi\Response\DocumentResponse;
use Slim\Http\Request;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * UserService
 *
 * @author gyula
 */
class UserService
{


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
     * @param BeanManager $beanManager
     */
    public function __construct(
        BeanManager $beanManager,
        AttributeObjectHelper $attributeHelper,
        RelationshipObjectHelper $relationshipHelper
    ) {
        $this->beanManager = $beanManager;
        $this->attributeHelper = $attributeHelper;
        $this->relationshipHelper = $relationshipHelper;
    }

    /**
     *
     * @param Request $request
     * @return DocumentResponse
     */
    public function getCurrentUser(Request $request)
    {
        $oauth2Token = $this->beanManager->newBeanSafe('OAuth2Tokens');

        $oauth2Token->retrieve_by_string_fields(
            ['access_token' => $request->getAttribute('oauth_access_token_id')]
        );

        $currentUser = $this->beanManager->getBeanSafe('Users', $oauth2Token->assigned_user_id);

        $currentUserData = $currentUser->toArray();
        unset($currentUserData['user_hash']);

        $dataResponse = new DataResponse($currentUser->getObjectName(), $currentUser->id);
        $attributeResponse = new AttributeResponse($currentUserData);
        $dataResponse->setAttributes($attributeResponse);
        $dataResponse->setRelationships($this->relationshipHelper->getRelationships($currentUser, $request->getUri()->getPath()));

        $response = new DocumentResponse();
        $response->setData($dataResponse);
        return $response;
    }
}
