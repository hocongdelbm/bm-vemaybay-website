<?php

namespace Api\V8\OAuth2\Repository;

use Api\V8\BeanDecorator\BeanManager;
use Api\V8\OAuth2\Entity\AccessTokenEntity;
use DateTime;
use InvalidArgumentException;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use OAuth2Tokens;
use User;
use Custom\Services\Notification\NotificationService;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var AccessTokenEntity
     */
    private $accessTokenEntity;

    /**
     * @var BeanManager
     */
    private $beanManager;

    /**
     * @param AccessTokenEntity $accessTokenEntity
     * @param BeanManager $beanManager
     */
    public function __construct(AccessTokenEntity $accessTokenEntity, BeanManager $beanManager)
    {
        $this->accessTokenEntity = $accessTokenEntity;
        $this->beanManager = $beanManager;
    }

    /**
     * @inheritdoc
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $this->accessTokenEntity->setClient($clientEntity);

        // we keep this even we don't have scopes atm
        foreach ($scopes as $scope) {
            $this->accessTokenEntity->addScope($scope);
        }

        $this->accessTokenEntity->setUserIdentifier($userIdentifier);

        return $this->accessTokenEntity;
    }

    /**
     * @inheritdoc
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $clientId = $accessTokenEntity->getClient()->getIdentifier();
        $userId = null;

        /** @var User $user */
        $client = $this->beanManager->getBeanSafe('OAuth2Clients', $clientId);

        switch ($client->allowed_grant_type) {
            case 'password':
                $userId = $accessTokenEntity->getUserIdentifier();
                break;
            case 'client_credentials':
                $userId = $client->assigned_user_id;
                break;
        }

        if ($userId === null) {
            throw new InvalidArgumentException('No user found');
        }

        /** @var OAuth2Tokens $token */
        $token = $this->beanManager->newBeanSafe(OAuth2Tokens::class);

        $token->access_token = $accessTokenEntity->getIdentifier();

        $token->access_token_expires = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');

        $token->client = $clientId;

        $token->assigned_user_id = $userId;

        $token->save();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException When access token is not found.
     */
    public function revokeAccessToken($tokenId)
    {
        $token = $this->beanManager->newBeanSafe(OAuth2Tokens::class);
        $token->retrieve_by_string_fields(
            ['access_token' => $tokenId]
        );

        if ($token->id === null) {
            throw new InvalidArgumentException('Access token is not found for this client');
        }

        $token->mark_deleted($token->id);
    }

    /**
     * @inheritdoc
     */
    public function isAccessTokenRevoked($tokenId)
    {
        /** @var OAuth2Tokens $token */
        $token = $this->beanManager->newBeanSafe(OAuth2Tokens::class);
        $token->retrieve_by_string_fields(
            ['access_token' => $tokenId]
        );

        try {
            // Send problem about token
            $log_token = array(
                'path' => 'API/V8/OAuth2/Repository/AccessTokenRepository.php',
                'tokenId' => $tokenId,
                'token_is_revoked' => $token->token_is_revoked,
                'access_token_expires' => $token->access_token_expires,
                'newDateTime' => new DateTime(),
                'expiresDateTime' => new DateTime($token->access_token_expires),
            );
            if($token->id === null || $token->token_is_revoked === '1' || new DateTime() > new DateTime($token->access_token_expires)) {
                $message = "The problem with tokens";
                $message .= "\n<pre>". json_encode($log_token) ."</pre>";
                NotificationService::sendErrorMessage($message, "default", ["threadKey" => 'logs']);
            }
        }
        catch(\Throwable $th) {
            $GLOBALS['log']->fatal($th->getMessage());
        }

        return $token->id === null || $token->token_is_revoked === '1' || new DateTime() > new DateTime($token->access_token_expires);
    }

    // public function sendTestTelegramTokenRevoked($content, $parseMode = 'HTML', $timeout = 5)
    // {
    //     $chat_id = '-1001360390468'; // Group Test
    //     $token = '1668507961:AAF76B96rWELQlN9lG1g0TO22wcm66jkvTk';

    //     $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;
    //     $url = $url . "&parse_mode=" . $parseMode . "&text=" . urlencode($content);
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    //     curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    //     $result = curl_exec($curl);
    //     curl_close($curl);
    //     return $result;
    // }
}
