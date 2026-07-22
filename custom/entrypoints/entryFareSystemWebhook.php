<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json; charset=utf-8');

// Receiver keys are configured in:
// $sugar_config['webhook']['fare_system']['auth_key|signature_key'].
$sendResponse = static function ($httpCode, $body) {
    http_response_code($httpCode);
    echo $body;
    exit;
};

$respond = static function ($httpCode, $message) use ($sendResponse) {
    $body = json_encode([
        'error' => 1,
        'message' => $message,
        'data' => null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($body === false) {
        $body = '{"error":1,"message":"Unable to encode response","data":null}';
    }

    $sendResponse($httpCode, $body);
};

$getRequestHeaders = static function () {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (!is_array($headers)) {
        $headers = [];
    }

    $normalizedHeaders = [];
    foreach ($headers as $name => $value) {
        $normalizedHeaders[strtolower($name)] = $value;
    }

    return $normalizedHeaders;
};

$limitFlightsByDirection = static function (&$responseData, $limit) {
    if (!isset($responseData['data']) || !is_array($responseData['data'])) {
        return;
    }

    foreach (['dep', 'ret'] as $direction) {
        if (!isset($responseData['data'][$direction]) || !is_array($responseData['data'][$direction])) {
            continue;
        }

        $responseData['data'][$direction] = array_slice(
            $responseData['data'][$direction],
            0,
            $limit
        );
    }
};

try {
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    if ($requestMethod !== 'POST') {
        $respond(405, 'Method not allowed');
    }

    $headers = $getRequestHeaders();
    $contentType = $headers['content-type'] ?? ($_SERVER['CONTENT_TYPE'] ?? '');
    if (!is_string($contentType) || stripos($contentType, 'application/json') === false) {
        $respond(415, 'Content-Type must be application/json');
    }

    global $sugar_config;
    $webhookConfig = $sugar_config['webhook']['fare_system'] ?? null;
    if (!is_array($webhookConfig)) {
        $respond(500, 'Fare System webhook is not configured');
    }

    $registeredKey = $webhookConfig['auth_key'] ?? '';
    $signatureKey = $webhookConfig['signature_key'] ?? '';

    if (!is_string($registeredKey) || $registeredKey === '') {
        $respond(500, 'Fare System webhook auth key is not configured');
    }
    if (!is_string($signatureKey) || $signatureKey === '') {
        $respond(500, 'Fare System webhook signature key is not configured');
    }

    $requestKey = $headers['x-auth-key'] ?? ($_SERVER['HTTP_X_AUTH_KEY'] ?? '');
    if (!is_string($requestKey) || $requestKey === '' || !hash_equals($registeredKey, $requestKey)) {
        $respond(401, 'Unauthorized');
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || trim($rawBody) === '') {
        $respond(400, 'Request body is required');
    }

    $requestSignature = $headers['x-signature'] ?? ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    $requestSignature = is_string($requestSignature) ? strtolower(trim($requestSignature)) : '';
    $expectedSignature = hash_hmac('sha256', $rawBody, $signatureKey);
    if (!preg_match('/^[a-f0-9]{64}$/', $requestSignature)
        || !hash_equals($expectedSignature, $requestSignature)
    ) {
        $respond(401, 'Unauthorized');
    }

    $payload = json_decode($rawBody, true);
    if (!is_array($payload)) {
        $payload = [];
    }

    $departureCode = is_string($payload['departureCode'] ?? null)
        ? strtoupper(trim($payload['departureCode']))
        : '';
    $destinationCode = is_string($payload['destinationCode'] ?? null)
        ? strtoupper(trim($payload['destinationCode']))
        : '';
    $airlineCode = is_string($payload['airlineCode'] ?? null)
        ? strtoupper(trim($payload['airlineCode']))
        : '';
    $departureDate = is_string($payload['departureDate'] ?? null)
        ? trim($payload['departureDate'])
        : '';

    $returnDateValue = $payload['returnDate'] ?? '';
    if ($returnDateValue === null) {
        $returnDate = '';
    } elseif (is_string($returnDateValue)) {
        $returnDate = trim($returnDateValue);
    } else {
        $returnDate = null;
    }

    $searchParams = [
        'airlineCode' => $airlineCode,
        'depCode' => $departureCode,
        'desCode' => $destinationCode,
        'departDate' => $departureDate,
        'returnDate' => $returnDate,
        'isLive' => true,
    ];

    require_once 'custom/entrypoints/entryFactory.php';

    $className = 'entryFareSystemClass';
    $entryClass = entryFactory::create($className);
    if (!$entryClass || !method_exists($entryClass, 'searchFlightBM')) {
        $respond(500, 'Unable to initialize Fare System service');
    }

    $response = $entryClass->searchFlightBM($searchParams);
    if (!is_string($response) || trim($response) === '') {
        $respond(502, 'Fare System returned an empty response');
    }

    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($responseData) || !array_key_exists('error', $responseData)) {
        $respond(502, 'Fare System returned invalid JSON');
    }

    if ((int) $responseData['error'] !== 0) {
        $sendResponse(502, $response);
    }
    if (!isset($responseData['data']) || !is_array($responseData['data'])) {
        $respond(502, 'Fare System response is missing flight data');
    }

    $limitFlightsByDirection($responseData, 6);
    $encodedResponse = json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encodedResponse === false) {
        $respond(502, 'Unable to encode Fare System response');
    }

    $sendResponse(200, $encodedResponse);
} catch (Throwable $throwable) {
    $logMessage = sprintf(
        'Fare System webhook failed: %s on line %d in %s',
        $throwable->getMessage(),
        $throwable->getLine(),
        $throwable->getFile()
    );

    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->fatal($logMessage);
    } else {
        error_log($logMessage);
    }

    $respond(500, 'Internal server error');
}
