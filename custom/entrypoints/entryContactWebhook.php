<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use custom\services\Webhook\WebhookAuthenticator;

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json; charset=utf-8');

// Contact operations the Chat System may invoke. entryContactClass has other public
// methods (clearCache) that must not be reachable from the network.
const CONTACT_WEBHOOK_METHODS = ['getContactsByPhones', 'createContact', 'updateContact'];

$contactWebhookSend = static function (int $httpCode, array $payload): void {
    http_response_code($httpCode);
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo $body === false ? '{"error":1,"message":"Unable to encode response","data":null}' : $body;
    exit;
};

$contactWebhookFail = static function (int $httpCode, string $message) use ($contactWebhookSend): void {
    $contactWebhookSend($httpCode, ['error' => 1, 'message' => $message, 'data' => null]);
};

$contactWebhookLog = static function (string $message): void {
    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->fatal($message);
        return;
    }

    error_log($message);
};

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        $contactWebhookFail(405, 'Method not allowed');
    }

    $headers = WebhookAuthenticator::requestHeaders();
    if (stripos(WebhookAuthenticator::header($headers, 'content-type'), 'application/json') === false) {
        $contactWebhookFail(415, 'Content-Type must be application/json');
    }

    $authenticator = WebhookAuthenticator::fromConfig('contact');
    if ($authenticator === null) {
        $contactWebhookLog('Contact webhook keys are not configured');
        $contactWebhookFail(500, 'Contact webhook is not configured');
    }

    if (!$authenticator->verifyAuthKey($headers)) {
        $contactWebhookFail(401, 'Unauthorized');
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || trim($rawBody) === '') {
        $contactWebhookFail(400, 'Request body is required');
    }

    if (!$authenticator->verifySignature($headers, $rawBody)) {
        $contactWebhookFail(401, 'Unauthorized');
    }

    $payload = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        $contactWebhookFail(400, 'Invalid JSON payload');
    }

    $method = is_string($payload['method'] ?? null) ? trim($payload['method']) : '';
    if (!in_array($method, CONTACT_WEBHOOK_METHODS, true)) {
        $contactWebhookFail(400, 'Method not allowed');
    }
    $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

    // entryContactClass evaluates every read and write against $current_user (ACL +
    // SecurityGroup) and stamps assigned_user_id from it. A server-to-server call has
    // no SuiteCRM session, so fall back to the configured integration user — otherwise
    // every operation would come back as "Access denied".
    global $current_user, $sugar_config;
    
    require_once 'custom/entrypoints/entryFactory.php';
    $entryClass = entryFactory::create('entryContactClass');
    if (!$entryClass || !method_exists($entryClass, $method)) {
        $contactWebhookLog('Unable to initialize Contact service');
        $contactWebhookFail(500, 'Unable to initialize Contact service');
    }

    $result = $entryClass->{$method}($params);

    // entryContactClass reports its own failures as ['error' => '<message>']; surface
    // those as 400 rather than a 200 the caller has to introspect.
    if (is_array($result) && isset($result['error']) && is_string($result['error'])) {
        $contactWebhookSend(400, ['error' => 1, 'message' => $result['error'], 'data' => $result]);
    }

    $contactWebhookSend(200, ['error' => 0, 'data' => $result]);
} catch (Throwable $throwable) {
    $contactWebhookLog(sprintf(
        'Contact webhook failed: %s on line %d in %s',
        $throwable->getMessage(),
        $throwable->getLine(),
        $throwable->getFile()
    ));
    $contactWebhookFail(500, 'Internal server error');
}
