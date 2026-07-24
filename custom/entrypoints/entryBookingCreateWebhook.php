<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('Content-Type: application/json; charset=utf-8');

$bookingWebhookRespond = static function (int $httpCode, bool $success, ?string $bookingDetail = null): void {
    http_response_code($httpCode);
    $body = json_encode([
        'success' => $success,
        'booking_detail' => $bookingDetail,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    echo $body === false ? '{"success":false,"booking_detail":null}' : $body;
    exit;
};

$bookingWebhookHeaders = static function (): array {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $normalized = [];
    foreach (is_array($headers) ? $headers : [] as $name => $value) {
        $normalized[strtolower($name)] = $value;
    }

    return $normalized;
};

$bookingWebhookLog = static function (string $message): void {
    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->fatal($message);
        return;
    }

    error_log($message);
};

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        $bookingWebhookRespond(405, false);
    }

    $headers = $bookingWebhookHeaders();
    $contentType = $headers['content-type'] ?? ($_SERVER['CONTENT_TYPE'] ?? '');
    if (!is_string($contentType) || stripos($contentType, 'application/json') === false) {
        $bookingWebhookRespond(415, false);
    }

    global $sugar_config;
    $config = $sugar_config['webhook']['booking'] ?? [];
    $authKey = is_string($config['auth_key'] ?? null) ? trim($config['auth_key']) : '';
    $signatureKey = is_string($config['signature_key'] ?? null) ? trim($config['signature_key']) : '';
    $serviceUserId = is_string($config['service_user_id'] ?? null) ? trim($config['service_user_id']) : '';

    if (!preg_match('/^[a-f0-9]{64}$/i', $authKey)
        || !preg_match('/^[a-f0-9]{64}$/i', $signatureKey)
        || !preg_match('/^[a-f0-9-]{36}$/i', $serviceUserId)
    ) {
        $bookingWebhookLog('Booking webhook configuration is incomplete or invalid');
        $bookingWebhookRespond(500, false);
    }

    $requestAuthKey = $headers['x-auth-key'] ?? ($_SERVER['HTTP_X_AUTH_KEY'] ?? '');
    if (!is_string($requestAuthKey) || !hash_equals($authKey, $requestAuthKey)) {
        $bookingWebhookRespond(401, false);
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || trim($rawBody) === '') {
        $bookingWebhookRespond(400, false);
    }

    $requestSignature = $headers['x-signature'] ?? ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    $requestSignature = is_string($requestSignature) ? strtolower(trim($requestSignature)) : '';
    $expectedSignature = hash_hmac('sha256', $rawBody, $signatureKey);
    if (!preg_match('/^[a-f0-9]{64}$/', $requestSignature)
        || !hash_equals($expectedSignature, $requestSignature)
    ) {
        $bookingWebhookRespond(401, false);
    }

    $payload = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        $bookingWebhookRespond(400, false);
    }

    // Chat does not currently send this header. It remains optional so the
    // endpoint can provide idempotency without another API change later.
    $requestId = $headers['x-request-id'] ?? ($_SERVER['HTTP_X_REQUEST_ID'] ?? null);
    if ($requestId !== null) {
        $requestId = is_string($requestId) ? strtolower(trim($requestId)) : '';
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $requestId)) {
            $bookingWebhookRespond(400, false);
        }
    }

    require_once 'custom/services/BookingWebhook/BookingWebhookPayloadValidator.php';
    $validator = new \custom\services\BookingWebhook\BookingWebhookPayloadValidator();
    $validation = $validator->validate($payload);
    if (!$validation['valid']) {
        $bookingWebhookLog(
            'Booking webhook validation failed for fields: ' . implode(', ', $validation['errors'])
        );
        $bookingWebhookRespond(400, false);
    }

    require_once 'custom/entrypoints/entryFactory.php';
    $entryClass = entryFactory::create('entryBookingClass');
    if (!$entryClass || !method_exists($entryClass, 'createBookingFromWebhook')) {
        $bookingWebhookLog('Unable to initialize booking webhook service');
        $bookingWebhookRespond(500, false);
    }

    $result = $entryClass->createBookingFromWebhook(
        $validation['payload'],
        $requestId,
        hash('sha256', $rawBody),
        $serviceUserId
    );

    if (empty($result['success'])) {
        $bookingWebhookRespond((int) ($result['http_code'] ?? 500), false);
    }

    $siteUrl = rtrim((string) ($sugar_config['site_url'] ?? ''), '/');
    if ($siteUrl === '') {
        $bookingWebhookLog('Booking webhook site_url is not configured');
        $bookingWebhookRespond(500, false);
    }

    $detailUrl = $siteUrl
        . '/index.php?module=EC_Flight_Bookings&action=DetailView&record='
        . rawurlencode($result['booking_id']);
    $bookingWebhookRespond(200, true, $detailUrl);
} catch (Throwable $throwable) {
    $bookingWebhookLog(sprintf(
        'Booking webhook failed: %s on line %d in %s',
        $throwable->getMessage(),
        $throwable->getLine(),
        $throwable->getFile()
    ));
    $bookingWebhookRespond(500, false);
}
