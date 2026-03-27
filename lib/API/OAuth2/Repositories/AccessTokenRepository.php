<?php
namespace SuiteCRM\API\OAuth2\Repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use SuiteCRM\API\OAuth2\Entities\AccessTokenEntity;
use Throwable;
use custom\services\Notification\NotificationService;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    const ACCESS_TOKEN_FIELD = 'access_token';
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        global $timedate;

        // Used by password grand
        // Some logic here to save the access token to a database
        $token = new \OAuth2Tokens();
        $token->token_is_revoked = false;
        $token->access_token = $accessTokenEntity->getIdentifier();
        $token->access_token_expires = $timedate->asUser($accessTokenEntity->getExpiryDateTime());
        $token->client = $accessTokenEntity->getClient()->getIdentifier();
        $token->assigned_user_id = $accessTokenEntity->getUserIdentifier();

        if (!$token->assigned_user_id) {
            $client = new \OAuth2Clients();
            $client->retrieve($token->client);
            $token->assigned_user_id = $client->assigned_user_id;
        }

        $token->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        // Some logic here to revoke the access token
        $token = new \OAuth2Tokens();
        $tokens =$token->get_list(
            '',
            self::ACCESS_TOKEN_FIELD.' = "'.$tokenId.'"'
        );
        /**
         * @var \OAuth2Tokens $token
         */
        foreach ($tokens['list'] as $token) {
            $token->token_is_revoked = true;
            $token->save();
        }
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId)
    {
        global $timedate;

        $token = new \OAuth2Tokens();
        $tokens =$token->get_list(
            '',
            self::ACCESS_TOKEN_FIELD.' = "'.$tokenId.'"'
        );
        /**
         * @var \OAuth2Tokens $token
         */
        foreach ($tokens['list'] as $token) {
            $expires = $timedate->fromUser($token->access_token_expires);
            if (!empty($expires)) {
                $now = new \DateTime('now', $expires->getTimezone());
                if ($now > $expires || (bool)$token->token_is_revoked === true) {
                    try {
                        // Sentele test
                        $log_token = [
                            'path' => 'lib/API/OAuth2/Repositories/AccessTokenRepository.php',
                            'tokenId' => $tokenId,
                            'now' => $now,
                            'expires' => $expires,
                            'getTimezone' => $expires->getTimezone(),
                            'token_is_revoked' => $token->token_is_revoked,
                            'access_token_expires' => $token->access_token_expires,
                        ];
                        $message = "The problem with tokens";
                        $message .= "\n<pre>". json_encode($log_token) ."</pre>";
                        NotificationService::sendErrorMessage($message, 'default', ['threadKey' => 'logs']);
                    }
                    catch(Throwable $th) {
                        $GLOBALS['log']->fatal($th->getMessage());
                    }
                    finally {
                        $token->token_is_revoked = true;
                        $token->save();
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @return AccessTokenEntity
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }
}
