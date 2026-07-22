<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json; charset=utf-8');

// Receiver keys are configured per webhook in config_override.php:
// webhook_api_keys: X-Auth-Key, webhook_signature_keys: request HMAC,
// webhook_secret_keys: optional response acknowledgement HMAC.
$ackSecretKey = '';

$sendResponse = static function ($httpCode, $body) use (&$ackSecretKey) {
    http_response_code($httpCode);
    if (is_string($ackSecretKey) && $ackSecretKey !== '') {
        header('X-Ack-Signature: ' . hash('sha256', $body . $ackSecretKey));
    }

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

$isFiniteNumber = static function ($value) {
    return (is_int($value) || is_float($value)) && is_finite((float) $value);
};

$recalculatePriceNode = static function (&$data, $serviceFee, $prefix = '') use ($isFiniteNumber) {
    $fieldNames = [];
    foreach (['fare', 'tax', 'airportFee', 'adminFee', 'fee', 'price'] as $fieldName) {
        $fieldNames[$fieldName] = $prefix === ''
            ? $fieldName
            : $prefix . ucfirst($fieldName);
    }

    // Keep each price node atomic: do not partially update malformed data.
    foreach ($fieldNames as $fieldName) {
        if (!array_key_exists($fieldName, $data) || !$isFiniteNumber($data[$fieldName])) {
            return;
        }
    }

    // Business rule for one passenger on one leg:
    // fee = adminFee + airportFee + serviceFee; price = fare + tax + fee.
    $fee = $data[$fieldNames['adminFee']]
        + $data[$fieldNames['airportFee']]
        + $serviceFee;

    $data[$fieldNames['fee']] = $fee;
    $data[$fieldNames['price']] = $data[$fieldNames['fare']]
        + $data[$fieldNames['tax']]
        + $fee;
};

$applyServiceFeeToFlightResponse = static function (&$responseData, $serviceFee) use ($recalculatePriceNode) {
    if ($serviceFee == 0) {
        return;
    }

    foreach (['dep', 'ret'] as $direction) {
        if (!isset($responseData['data'][$direction]) || !is_array($responseData['data'][$direction])) {
            continue;
        }

        foreach ($responseData['data'][$direction] as &$flight) {
            if (!is_array($flight)) {
                continue;
            }

            $recalculatePriceNode($flight, $serviceFee);
            foreach (['adt', 'chd', 'inf'] as $passengerType) {
                $recalculatePriceNode($flight, $serviceFee, $passengerType);
            }

            if (!isset($flight['fareOptions']) || !is_array($flight['fareOptions'])) {
                continue;
            }

            foreach ($flight['fareOptions'] as &$fareOption) {
                if (!is_array($fareOption)) {
                    continue;
                }

                $recalculatePriceNode($fareOption, $serviceFee);

                foreach (['adt', 'chd', 'inf'] as $passengerType) {
                    if (!isset($fareOption[$passengerType]) || !is_array($fareOption[$passengerType])) {
                        continue;
                    }

                    $recalculatePriceNode($fareOption[$passengerType], $serviceFee);
                }
            }
            unset($fareOption);
        }
        unset($flight);
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
    $webhookApiKeys = $sugar_config['webhook_api_keys'] ?? [];
    $webhookSignatureKeys = $sugar_config['webhook_signature_keys'] ?? [];
    $webhookSecretKeys = $sugar_config['webhook_secret_keys'] ?? [];

    $registeredKey = is_array($webhookApiKeys) ? ($webhookApiKeys['fare_system'] ?? '') : '';
    $signatureKey = is_array($webhookSignatureKeys) ? ($webhookSignatureKeys['fare_system'] ?? '') : '';
    $ackSecretKey = is_array($webhookSecretKeys) ? ($webhookSecretKeys['fare_system'] ?? '') : '';

    if (!is_string($registeredKey) || $registeredKey === '') {
        $respond(500, 'Fare System webhook auth key is not configured');
    }
    if (!is_string($signatureKey) || $signatureKey === '') {
        $respond(500, 'Fare System webhook signature key is not configured');
    }
    if (!is_string($ackSecretKey)) {
        $respond(500, 'Fare System webhook ack secret key has an invalid configuration');
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

    $serviceFeeValue = $payload['serviceFee'] ?? 0;
    $serviceFee = (is_int($serviceFeeValue) || is_float($serviceFeeValue))
        && is_finite((float) $serviceFeeValue)
        ? $serviceFeeValue
        : 0;

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

    $applyServiceFeeToFlightResponse($responseData, $serviceFee);
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
