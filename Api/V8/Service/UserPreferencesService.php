<?php

namespace Api\V8\Service;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\JsonApi\Response\AttributeResponse;
use Api\V8\JsonApi\Response\DataResponse;
use Api\V8\JsonApi\Response\DocumentResponse;
use Api\V8\Param\GetUserPreferencesParams;
use DBManagerFactory;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * UserPreferencesService
 *
 * @author gyula
 */
class UserPreferencesService
{


    /**
     * @var BeanManager
     */
    protected $beanManager;

    /**
     * @param BeanManager $beanManager
     */
    public function __construct(
        BeanManager $beanManager
    ) {
        $this->beanManager = $beanManager;
    }

    /**
     *
     * @param GetUserPreferencesParams $params
     * @return DocumentResponse
     */
    public function getUserPreferences(GetUserPreferencesParams $params)
    {
        // needs to determinate the user preferences
        $user = $this->beanManager->getBeanSafe('Users', $params->getUserId());

        $db = DBManagerFactory::getInstance();
        $result = $db->query("SELECT contents, category FROM user_preferences WHERE assigned_user_id='$user->id' AND deleted = 0", false, 'Failed to load user preferences');
        $preferences = [];
        while ($row = $db->fetchByAssoc($result)) {
            $category = $row['category'];
            $preferences[$category] = unserialize(base64_decode($row['contents']));
        }

        $dataResponse = new DataResponse('UserPreference', $params->getUserId());
        $attributeResponse = new AttributeResponse($preferences);
        $dataResponse->setAttributes($attributeResponse);

        $response = new DocumentResponse();
        $response->setData($dataResponse);
        return $response;
    }
}
