<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/** Shared transport validation and response envelope for chat attachments. */
final class ChatUploadWebhookDispatcher
{
    private const CLIENT_ID = 'chat_websocket';

    public static function requestHeaders()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        return array_change_key_case(is_array($headers) ? $headers : [], CASE_LOWER);
    }

    public static function headerValue(array $headers, $name, $serverName)
    {
        $value = $headers[strtolower($name)] ?? ($_SERVER[$serverName] ?? '');
        return is_string($value) ? trim($value) : '';
    }

    public static function resolveKind(array $headers)
    {
        $kind = strtolower(self::headerValue($headers, 'X-Upload-Kind', 'HTTP_X_UPLOAD_KIND'));
        return in_array($kind, ['image', 'file'], true) ? $kind : null;
    }

    public static function responsePayload($success, $code, $message, $requestId, array $urls = [], array $files = [])
    {
        return [
            'success' => (bool) $success,
            'error' => $success ? 0 : 1,
            'code' => $code,
            'message' => $message,
            'requestId' => $requestId,
            'urls' => $urls,
            'files' => $files,
        ];
    }

    public static function requestContext($kind, callable $fail)
    {
        $headers = self::requestHeaders();
        $requestId = self::headerValue($headers, 'X-Request-Id', 'HTTP_X_REQUEST_ID');
        $label = $kind === 'image' ? 'Image' : 'File';

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $fail(405, 'METHOD_NOT_ALLOWED', 'Method not allowed', $requestId ?: null);
        }
        $contentType = self::headerValue($headers, 'Content-Type', 'CONTENT_TYPE');
        if (!preg_match('/^multipart\\/form-data(?:\\s*;|$)/i', $contentType)) {
            $fail(415, 'UNSUPPORTED_MEDIA_TYPE', 'Content-Type must be multipart/form-data', $requestId ?: null);
        }
        $clientId = self::headerValue($headers, 'X-Client-Id', 'HTTP_X_CLIENT_ID');
        if ($clientId !== self::CLIENT_ID) {
            $fail(401, 'UNAUTHORIZED', 'Unauthorized', $requestId ?: null);
        }

        $authenticator = \custom\services\Webhook\WebhookAuthenticator::fromConfig('upload');
        if ($authenticator === null) {
            $fail(500, 'CONFIGURATION_ERROR', $label . ' upload webhook is not configured', $requestId ?: null);
        }
        if (!$authenticator->verifyAuthKey($headers)) {
            $fail(401, 'UNAUTHORIZED', 'Unauthorized', $requestId ?: null);
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/D', $requestId)) {
            $fail(400, 'INVALID_REQUEST_ID', 'X-Request-Id must be a lowercase UUID', null);
        }
        $timestamp = self::headerValue($headers, 'X-Timestamp', 'HTTP_X_TIMESTAMP');
        if (!preg_match('/^[0-9]{13}$/D', $timestamp)) {
            $fail(401, 'INVALID_TIMESTAMP', 'X-Timestamp must be Unix time in milliseconds', $requestId);
        }
        if (abs(((int) floor(microtime(true) * 1000)) - (int) $timestamp) > 300000) {
            $fail(401, 'TIMESTAMP_EXPIRED', 'Request timestamp is outside the allowed time window', $requestId);
        }

        return ['headers' => $headers, 'clientId' => $clientId, 'requestId' => $requestId, 'timestamp' => $timestamp, 'authenticator' => $authenticator];
    }

    public static function hasValidSignature(array $headers, $manifest, $authenticator)
    {
        return $authenticator instanceof \custom\services\Webhook\WebhookAuthenticator
            && $authenticator->verifySignature($headers, $manifest);
    }
}

require_once 'custom/include/helpers/api/APINextCloud.php';

class NextCloudImageUploadException extends RuntimeException
{
    private $httpStatus;
    private $errorCode;

    public function __construct($httpStatus, $errorCode, $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->httpStatus = (int) $httpStatus;
        $this->errorCode = (string) $errorCode;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}

class NextCloudImageUploadService
{
    const MAX_FILE_COUNT = 5;
    const MAX_FILE_SIZE = 16777216;
    const MAX_BATCH_SIZE = 83886080;
    const DEFAULT_DEADLINE_SECONDS = 25;
    const CLEANUP_RESERVE_SECONDS = 3;
    const MAX_FILENAME_ATTEMPTS = 3;

    private $api;
    private $cleanupApi;
    private $uploadedFileValidator;
    private $clockMilliseconds;
    private $dateProvider;
    private $uniqueIdProvider;
    private $unixTimeProvider;
    private $operationDeadlineMs;
    private $hardDeadlineMs;
    private $cleanupReserveMs;

    public function __construct($api = null, array $options = [])
    {
        $this->uploadedFileValidator = $options['uploaded_file_validator'] ?? static function ($path) {
            return is_uploaded_file($path);
        };
        $this->clockMilliseconds = $options['clock_milliseconds'] ?? static function () {
            return (int) floor(microtime(true) * 1000);
        };
        $this->dateProvider = $options['date_provider'] ?? static function () {
            return new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        };
        $this->uniqueIdProvider = $options['unique_id_provider'] ?? static function () {
            return bin2hex(random_bytes(16));
        };
        $this->unixTimeProvider = $options['unix_time_provider'] ?? static function () {
            return time();
        };

        foreach (
            [
                'uploaded_file_validator' => $this->uploadedFileValidator,
                'clock_milliseconds' => $this->clockMilliseconds,
                'date_provider' => $this->dateProvider,
                'unique_id_provider' => $this->uniqueIdProvider,
                'unix_time_provider' => $this->unixTimeProvider,
            ] as $optionName => $callable
        ) {
            if (!is_callable($callable)) {
                throw new NextCloudImageUploadException(
                    500,
                    'CONFIGURATION_ERROR',
                    "Image upload option {$optionName} must be callable"
                );
            }
        }

        $deadlineSeconds = $options['deadline_seconds'] ?? self::DEFAULT_DEADLINE_SECONDS;
        if (!is_int($deadlineSeconds) && !is_float($deadlineSeconds)) {
            throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'Invalid upload deadline');
        }

        $deadlineSeconds = max(1, (float) $deadlineSeconds);
        $startMs = $this->nowMilliseconds();
        $this->hardDeadlineMs = $startMs + (int) floor($deadlineSeconds * 1000);
        $this->cleanupReserveMs = min(
            (int) floor(self::CLEANUP_RESERVE_SECONDS * 1000),
            max(0, (int) floor(($deadlineSeconds - 1) * 1000))
        );
        $this->operationDeadlineMs = $this->hardDeadlineMs - $this->cleanupReserveMs;

        if ($api !== null) {
            $this->api = $api;
            $this->cleanupApi = $options['cleanup_api'] ?? $api;
        }
    }

    public static function buildCanonicalManifest($clientId, $requestId, $timestampMs, array $preparedFiles)
    {
        $lines = [
            'image-upload-v1',
            (string) $clientId,
            (string) $requestId,
            (string) $timestampMs,
            (string) count($preparedFiles),
        ];

        foreach ($preparedFiles as $index => $file) {
            if (!isset($file['size'], $file['sha256'])) {
                throw new InvalidArgumentException('Prepared file metadata is incomplete');
            }
            $lines[] = $index . ':' . $file['size'] . ':' . $file['sha256'];
        }

        return implode("\n", $lines);
    }

    public function uploadFiles($filesField)
    {
        return $this->uploadPreparedFiles($this->validateAndPrepareFiles($filesField));
    }

    public function validateAndPrepareFiles($filesField)
    {
        $files = $this->normalizeFilesField($filesField);
        $preparedFiles = [];
        $totalSize = 0;

        if (!class_exists('finfo')) {
            throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'Fileinfo extension is not available');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);

        foreach ($files as $index => $file) {
            $uploadError = $file['error'];
            if ($uploadError !== UPLOAD_ERR_OK) {
                $this->throwForUploadError($uploadError, $index);
            }

            $tmpPath = $file['tmp_name'];
            if (!is_string($tmpPath) || $tmpPath === '' || !call_user_func($this->uploadedFileValidator, $tmpPath)) {
                throw new NextCloudImageUploadException(400, 'INVALID_UPLOAD', "Image {$index} is not a valid HTTP upload");
            }

            $actualSize = filesize($tmpPath);
            if ($actualSize === false) {
                throw new NextCloudImageUploadException(500, 'INTERNAL_ERROR', "Unable to read image {$index}");
            }
            $actualSize = (int) $actualSize;
            if ($actualSize <= 0) {
                throw new NextCloudImageUploadException(400, 'INVALID_IMAGE', "Image {$index} is empty");
            }
            if ($actualSize > self::MAX_FILE_SIZE) {
                throw new NextCloudImageUploadException(413, 'FILE_TOO_LARGE', "Image {$index} exceeds 16 MiB");
            }

            $totalSize += $actualSize;
            if ($totalSize > self::MAX_BATCH_SIZE) {
                throw new NextCloudImageUploadException(413, 'BATCH_TOO_LARGE', 'Image batch exceeds 80 MiB');
            }

            $mimeType = $fileInfo->file($tmpPath);
            $mimeDefinition = $this->getMimeDefinition($mimeType);
            if ($mimeDefinition === null) {
                throw new NextCloudImageUploadException(
                    415,
                    'UNSUPPORTED_IMAGE_TYPE',
                    "Image {$index} must be JPG, PNG or WebP"
                );
            }

            $imageInfo = @getimagesize($tmpPath);
            if (!is_array($imageInfo)
                || !isset($imageInfo[0], $imageInfo[1], $imageInfo[2])
                || (int) $imageInfo[0] <= 0
                || (int) $imageInfo[1] <= 0
                || (int) $imageInfo[2] !== $mimeDefinition['imageType']
            ) {
                throw new NextCloudImageUploadException(415, 'INVALID_IMAGE', "Image {$index} content is invalid");
            }

            $sha256 = hash_file('sha256', $tmpPath);
            if (!is_string($sha256) || !preg_match('/^[a-f0-9]{64}$/', $sha256)) {
                throw new NextCloudImageUploadException(500, 'INTERNAL_ERROR', "Unable to hash image {$index}");
            }

            $originalName = $this->normalizeOriginalName($file['name']);
            $preparedFiles[] = [
                'index' => $index,
                'tmpPath' => $tmpPath,
                'originalName' => $originalName,
                'mimeType' => $mimeDefinition['mimeType'],
                'extension' => $mimeDefinition['extension'],
                'size' => $actualSize,
                'width' => (int) $imageInfo[0],
                'height' => (int) $imageInfo[1],
                'sha256' => $sha256,
            ];
        }

        return $preparedFiles;
    }

    public function uploadPreparedFiles(array $preparedFiles)
    {
        if (count($preparedFiles) < 1) {
            throw new NextCloudImageUploadException(400, 'MISSING_IMAGES', 'At least one image is required');
        }
        if (count($preparedFiles) > self::MAX_FILE_COUNT) {
            throw new NextCloudImageUploadException(413, 'TOO_MANY_FILES', 'A maximum of 5 images is allowed');
        }

        $date = call_user_func($this->dateProvider);
        if (!$date instanceof DateTimeInterface) {
            throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'Date provider returned an invalid value');
        }

        $folderPath = '/bmvmb/chat_uploads/' . $date->format('Y/m/d');
        $artifacts = [];

        try {
            $this->initializeApiClients();
            $this->ensureFolders($folderPath);
            $uploadedFiles = [];

            foreach ($preparedFiles as $position => $file) {
                $this->assertOperationDeadline();
                $this->validatePreparedFile($file, $position);

                $remotePath = null;
                $artifactIndex = null;
                $uploaded = false;

                for ($attempt = 0; $attempt < self::MAX_FILENAME_ATTEMPTS; $attempt++) {
                    $this->assertOperationDeadline();
                    $uniqueId = (string) call_user_func($this->uniqueIdProvider);
                    $unixTime = (int) call_user_func($this->unixTimeProvider);
                    if ($uniqueId === '' || !preg_match('/^[a-zA-Z0-9]+$/', $uniqueId) || $unixTime <= 0) {
                        throw new NextCloudImageUploadException(500, 'INTERNAL_ERROR', 'Unable to generate remote filename');
                    }

                    $remotePath = $folderPath . '/' . $uniqueId . '_' . $unixTime . '.' . $file['extension'];
                    $artifactIndex = count($artifacts);
                    $artifacts[] = [
                        'remotePath' => $remotePath,
                        'shareId' => null,
                    ];

                    $uploadResult = $this->decodeApiResponse(
                        $this->api->uploadFile($file['tmpPath'], $remotePath, ['prevent_overwrite' => true])
                    );
                    if ((int) ($uploadResult['httpCode'] ?? 0) === 412) {
                        array_pop($artifacts);
                        continue;
                    }
                    if ($this->responseTimedOut($uploadResult)) {
                        throw new NextCloudImageUploadException(504, 'UPLOAD_TIMEOUT', 'Image upload deadline exceeded');
                    }
                    if (!$this->isSuccessfulHttpResult($uploadResult, [201])) {
                        throw new NextCloudImageUploadException(
                            502,
                            'NEXTCLOUD_UPLOAD_FAILED',
                            'Failed to upload image to NextCloud'
                        );
                    }

                    $uploaded = true;
                    break;
                }

                if (!$uploaded) {
                    throw new NextCloudImageUploadException(
                        502,
                        'NEXTCLOUD_UPLOAD_COLLISION',
                        'Unable to allocate a unique image path'
                    );
                }

                $this->assertOperationDeadline();
                $shareResult = $this->decodeApiResponse($this->api->createShare($remotePath, 1));
                if ($this->responseTimedOut($shareResult)) {
                    throw new NextCloudImageUploadException(504, 'UPLOAD_TIMEOUT', 'Image upload deadline exceeded');
                }

                $shareData = $shareResult['data'] ?? null;
                $shareId = is_array($shareData) ? ($shareData['id'] ?? null) : null;
                $shareUrl = is_array($shareData) ? ($shareData['url'] ?? '') : '';
                if (!$this->isSuccessfulHttpResult($shareResult, [200])
                    || (!is_int($shareId) && !is_string($shareId))
                    || (string) $shareId === ''
                    || !$this->isValidHttpsUrl($shareUrl)
                ) {
                    throw new NextCloudImageUploadException(502, 'NEXTCLOUD_SHARE_FAILED', 'Failed to create public image share');
                }

                $artifacts[$artifactIndex]['shareId'] = $shareId;
                $shareUrl = rtrim($shareUrl, '/');
                $uploadedFiles[] = [
                    'index' => $file['index'],
                    'url' => $shareUrl . '/preview',
                    'shareUrl' => $shareUrl,
                    'originalName' => $file['originalName'],
                    'mimeType' => $file['mimeType'],
                    'extension' => $file['extension'],
                    'size' => $file['size'],
                    'width' => $file['width'],
                    'height' => $file['height'],
                    'sha256' => $file['sha256'],
                ];
            }

            return $uploadedFiles;
        } catch (Throwable $throwable) {
            $this->rollback($artifacts);
            if ($throwable instanceof NextCloudImageUploadException) {
                throw $throwable;
            }

            throw new NextCloudImageUploadException(
                500,
                'INTERNAL_ERROR',
                'Unexpected image upload error',
                $throwable
            );
        }
    }

    private function normalizeFilesField($filesField)
    {
        if (!is_array($filesField)) {
            throw new NextCloudImageUploadException(400, 'MISSING_IMAGES', 'At least one image is required');
        }

        foreach (['name', 'tmp_name', 'error', 'size'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $filesField)) {
                throw new NextCloudImageUploadException(400, 'INVALID_UPLOAD_STRUCTURE', 'Invalid images upload structure');
            }
        }

        $isMulti = is_array($filesField['name']);
        if (!$isMulti) {
            foreach (['tmp_name', 'error', 'size'] as $fieldName) {
                if (is_array($filesField[$fieldName])) {
                    throw new NextCloudImageUploadException(400, 'INVALID_UPLOAD_STRUCTURE', 'Invalid images upload structure');
                }
            }

            return [[
                'name' => $filesField['name'],
                'tmp_name' => $filesField['tmp_name'],
                'error' => $filesField['error'],
                'size' => $filesField['size'],
            ]];
        }

        $fileCount = count($filesField['name']);
        if ($fileCount < 1) {
            throw new NextCloudImageUploadException(400, 'MISSING_IMAGES', 'At least one image is required');
        }
        if ($fileCount > self::MAX_FILE_COUNT) {
            throw new NextCloudImageUploadException(413, 'TOO_MANY_FILES', 'A maximum of 5 images is allowed');
        }

        $expectedKeys = range(0, $fileCount - 1);
        foreach (['name', 'tmp_name', 'error', 'size'] as $fieldName) {
            if (!is_array($filesField[$fieldName]) || array_keys($filesField[$fieldName]) !== $expectedKeys) {
                throw new NextCloudImageUploadException(400, 'INVALID_UPLOAD_STRUCTURE', 'Invalid images upload structure');
            }
        }

        $files = [];
        foreach ($expectedKeys as $index) {
            foreach (['name', 'tmp_name', 'error', 'size'] as $fieldName) {
                if (is_array($filesField[$fieldName][$index])) {
                    throw new NextCloudImageUploadException(400, 'INVALID_UPLOAD_STRUCTURE', 'Nested image uploads are not supported');
                }
            }
            $files[] = [
                'name' => $filesField['name'][$index],
                'tmp_name' => $filesField['tmp_name'][$index],
                'error' => $filesField['error'][$index],
                'size' => $filesField['size'][$index],
            ];
        }

        return $files;
    }

    private function throwForUploadError($uploadError, $index)
    {
        $uploadError = (int) $uploadError;
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            throw new NextCloudImageUploadException(413, 'FILE_TOO_LARGE', "Image {$index} exceeds the upload limit");
        }
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            throw new NextCloudImageUploadException(400, 'MISSING_IMAGES', "Image {$index} was not provided");
        }
        if (in_array($uploadError, [UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION], true)) {
            throw new NextCloudImageUploadException(500, 'INTERNAL_ERROR', "Server could not receive image {$index}");
        }

        throw new NextCloudImageUploadException(400, 'UPLOAD_FAILED', "Image {$index} upload failed");
    }

    private function getMimeDefinition($mimeType)
    {
        $definitions = [
            'image/jpeg' => [
                'mimeType' => 'image/jpeg',
                'extension' => 'jpg',
                'imageType' => IMAGETYPE_JPEG,
            ],
            'image/png' => [
                'mimeType' => 'image/png',
                'extension' => 'png',
                'imageType' => IMAGETYPE_PNG,
            ],
        ];

        if (defined('IMAGETYPE_WEBP')) {
            $definitions['image/webp'] = [
                'mimeType' => 'image/webp',
                'extension' => 'webp',
                'imageType' => constant('IMAGETYPE_WEBP'),
            ];
        }

        return $definitions[$mimeType] ?? null;
    }

    private function normalizeOriginalName($name)
    {
        if (!is_string($name) || $name === '') {
            return 'image';
        }

        $name = basename(str_replace('\\', '/', $name));
        return $name !== '' ? $name : 'image';
    }

    private function validatePreparedFile(array $file, $position)
    {
        foreach (
            ['index', 'tmpPath', 'originalName', 'mimeType', 'extension', 'size', 'width', 'height', 'sha256']
            as $requiredKey
        ) {
            if (!array_key_exists($requiredKey, $file)) {
                throw new NextCloudImageUploadException(
                    500,
                    'INTERNAL_ERROR',
                    "Prepared image {$position} is incomplete"
                );
            }
        }

        if (!in_array($file['extension'], ['jpg', 'png', 'webp'], true)) {
            throw new NextCloudImageUploadException(500, 'INTERNAL_ERROR', 'Prepared image extension is invalid');
        }
    }

    private function ensureFolders($folderPath)
    {
        $parts = explode('/', trim($folderPath, '/'));
        $currentPath = '';

        foreach ($parts as $part) {
            $this->assertOperationDeadline();
            $currentPath .= '/' . $part;
            $result = $this->decodeApiResponse($this->api->createFolder($currentPath));
            if ($this->responseTimedOut($result)) {
                throw new NextCloudImageUploadException(504, 'UPLOAD_TIMEOUT', 'Image upload deadline exceeded');
            }

            $httpCode = (int) ($result['httpCode'] ?? 0);
            $folderCreated = $httpCode === 201 && (int) ($result['status'] ?? 0) === 1;
            $folderAlreadyExists = $httpCode === 405;
            if (!$folderCreated && !$folderAlreadyExists) {
                throw new NextCloudImageUploadException(502, 'NEXTCLOUD_FOLDER_FAILED', 'Failed to prepare NextCloud folder');
            }
        }
    }

    private function rollback(array $artifacts)
    {
        for ($index = count($artifacts) - 1; $index >= 0; $index--) {
            $artifact = $artifacts[$index];
            $shareIds = [];
            if ($artifact['shareId'] !== null && (string) $artifact['shareId'] !== '') {
                $shareIds[] = $artifact['shareId'];
            } else {
                $shareIds = $this->findShareIdsForPath($artifact['remotePath']);
            }

            foreach ($shareIds as $shareId) {
                try {
                    $result = $this->decodeApiResponse($this->cleanupApi->deleteShare($shareId));
                    if (!$this->isSuccessfulHttpResult($result, [200, 204])) {
                        $this->logCleanupResponseFailure('share', $result);
                    }
                } catch (Throwable $throwable) {
                    $this->logCleanupFailure('share', $throwable);
                }
            }

            try {
                $result = $this->decodeApiResponse($this->cleanupApi->deleteFile($artifact['remotePath']));
                if (!$this->isSuccessfulHttpResult($result, [200, 204])) {
                    $this->logCleanupResponseFailure('file', $result);
                }
            } catch (Throwable $throwable) {
                $this->logCleanupFailure('file', $throwable);
            }
        }
    }

    private function findShareIdsForPath($remotePath)
    {
        try {
            $result = $this->decodeApiResponse($this->cleanupApi->getShares($remotePath));
            if (!$this->isSuccessfulHttpResult($result, [200]) || !is_array($result['data'] ?? null)) {
                $this->logCleanupResponseFailure('share lookup', $result);
                return [];
            }

            $shares = $result['data'];
            if (isset($shares['id'])) {
                $shares = [$shares];
            }

            $shareIds = [];
            foreach ($shares as $share) {
                if (is_array($share) && isset($share['id']) && (string) $share['id'] !== '') {
                    $shareIds[] = $share['id'];
                }
            }
            return $shareIds;
        } catch (Throwable $throwable) {
            $this->logCleanupFailure('share lookup', $throwable);
            return [];
        }
    }

    private function decodeApiResponse($response)
    {
        if (is_array($response)) {
            return $response;
        }
        if (!is_string($response) || $response === '') {
            return [];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function isSuccessfulHttpResult(array $result, array $acceptedHttpCodes)
    {
        return (int) ($result['status'] ?? 0) === 1
            && in_array((int) ($result['httpCode'] ?? 0), $acceptedHttpCodes, true);
    }

    private function responseTimedOut(array $result)
    {
        return !empty($result['timedOut']) || (int) ($result['httpCode'] ?? 0) === 504;
    }

    private function assertOperationDeadline()
    {
        if ($this->nowMilliseconds() >= $this->operationDeadlineMs) {
            throw new NextCloudImageUploadException(504, 'UPLOAD_TIMEOUT', 'Image upload deadline exceeded');
        }
    }

    private function nowMilliseconds()
    {
        return (int) call_user_func($this->clockMilliseconds);
    }

    private function isValidHttpsUrl($url)
    {
        return is_string($url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }

    private function validateNextCloudConfiguration()
    {
        global $sugar_config;
        $config = $sugar_config['next-cloud'] ?? null;
        if (!is_array($config)) {
            throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'NextCloud is not configured');
        }

        foreach (['user', 'password', 'endpoint', 'base_url_ocs'] as $key) {
            if (!isset($config[$key]) || !is_string($config[$key]) || trim($config[$key]) === '') {
                throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'NextCloud is not configured');
            }
        }

        foreach (['endpoint', 'base_url_ocs'] as $urlKey) {
            if (!$this->isValidHttpsUrl($config[$urlKey])) {
                throw new NextCloudImageUploadException(500, 'CONFIGURATION_ERROR', 'NextCloud URLs must use HTTPS');
            }
        }
    }

    private function initializeApiClients()
    {
        if ($this->api !== null && $this->cleanupApi !== null) {
            return;
        }

        $this->validateNextCloudConfiguration();
        $remainingOperationMs = max(1, $this->operationDeadlineMs - $this->nowMilliseconds());
        $this->api = new APINextCloud([
            'verify_ssl' => true,
            'follow_redirects' => false,
            'connect_timeout_ms' => min(5000, $remainingOperationMs),
            'timeout_ms' => $remainingOperationMs,
            'deadline_at_ms' => $this->operationDeadlineMs,
        ]);
        $this->cleanupApi = new APINextCloud([
            'verify_ssl' => true,
            'follow_redirects' => false,
            'connect_timeout_ms' => min(1500, max(1, $this->cleanupReserveMs)),
            'timeout_ms' => max(1, $this->cleanupReserveMs),
            'deadline_at_ms' => $this->hardDeadlineMs,
        ]);
    }

    private function logCleanupFailure($target, Throwable $throwable)
    {
        $message = 'NextCloud image upload rollback failed for ' . $target . ': ' . $throwable->getMessage();
        if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
            $GLOBALS['log']->warn($message);
        } else {
            error_log($message);
        }
    }

    private function logCleanupResponseFailure($target, array $result)
    {
        $message = sprintf(
            'NextCloud cleanup returned an error for %s: status=%d http_code=%d timed_out=%s',
            $target,
            (int) ($result['status'] ?? 0),
            (int) ($result['httpCode'] ?? 0),
            !empty($result['timedOut']) ? 'yes' : 'no'
        );

        if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
            $GLOBALS['log']->warn($message);
        } else {
            error_log($message);
        }
    }
}

/** Server-to-server receiver for Chat document attachments. */
final class ChatFileUploadWebhook
{
    const MAX_FILE_COUNT = 5;
    const MAX_FILE_SIZE = 27262976;
    const MAX_BATCH_SIZE = 5 * 27262976;

    private static $extensions = [
        'pdf' => 'application/pdf', 'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain', 'csv' => 'text/csv', 'zip' => 'application/zip', 'rar' => 'application/vnd.rar',
    ];

    public static function fail($status, $code, $message, $requestId = null)
    {
        self::respond($status, false, $code, $message, $requestId, [], []);
    }

    public static function respond($status, $success, $code, $message, $requestId, array $urls, array $files)
    {
        http_response_code($status);
        echo json_encode(ChatUploadWebhookDispatcher::responsePayload($success, $code, $message, $requestId, $urls, $files), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    public static function normalizedFiles($field)
    {
        if (!is_array($field) || !isset($field['name'], $field['tmp_name'], $field['error'], $field['size'])) throw new RuntimeException('MISSING_FILES');
        if (!is_array($field['name'])) return [['name' => $field['name'], 'tmp_name' => $field['tmp_name'], 'error' => $field['error'], 'size' => $field['size']]];
        $count = count($field['name']);
        if ($count < 1 || $count > self::MAX_FILE_COUNT) throw new RuntimeException('TOO_MANY_FILES');
        $files = [];
        foreach (range(0, $count - 1) as $index) {
            foreach (['name', 'tmp_name', 'error', 'size'] as $key) {
                if (!isset($field[$key][$index]) || is_array($field[$key][$index])) throw new RuntimeException('INVALID_UPLOAD_STRUCTURE');
            }
            $files[] = ['name' => $field['name'][$index], 'tmp_name' => $field['tmp_name'][$index], 'error' => $field['error'][$index], 'size' => $field['size'][$index]];
        }
        return $files;
    }

    public static function prepare(array $files)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $total = 0;
        $prepared = [];
        foreach ($files as $index => $file) {
            if ((int) $file['error'] === UPLOAD_ERR_INI_SIZE || (int) $file['error'] === UPLOAD_ERR_FORM_SIZE) throw new RuntimeException('FILE_TOO_LARGE');
            if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('INVALID_UPLOAD');
            $size = filesize($file['tmp_name']);
            if ($size === false || $size < 1) throw new RuntimeException('EMPTY_FILE');
            if ($size > self::MAX_FILE_SIZE) throw new RuntimeException('FILE_TOO_LARGE');
            $total += $size;
            if ($total > self::MAX_BATCH_SIZE) throw new RuntimeException('BATCH_TOO_LARGE');
            $name = basename(str_replace('\\\\', '/', (string) $file['name']));
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!isset(self::$extensions[$extension])) throw new RuntimeException('UNSUPPORTED_FILE_TYPE');
            $mime = (string) $finfo->file($file['tmp_name']);
            $allowedMimes = [self::$extensions[$extension], 'application/octet-stream'];
            if (in_array($extension, ['docx', 'xlsx', 'pptx', 'zip'], true)) $allowedMimes[] = 'application/zip';
            if ($extension === 'rar') $allowedMimes[] = 'application/x-rar-compressed';
            if (!in_array($mime, $allowedMimes, true)) throw new RuntimeException('UNSUPPORTED_FILE_TYPE');
            $hash = hash_file('sha256', $file['tmp_name']);
            if (!is_string($hash) || !preg_match('/^[a-f0-9]{64}$/', $hash)) throw new RuntimeException('INTERNAL_ERROR');
            $prepared[] = ['index' => $index, 'tmpPath' => $file['tmp_name'], 'name' => $name ?: 'attachment.' . $extension, 'extension' => $extension, 'mimeType' => self::$extensions[$extension], 'size' => (int) $size, 'sha256' => $hash];
        }
        return $prepared;
    }

    private static function decode($response)
    {
        $decoded = is_array($response) ? $response : json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function ensureFolders(APINextCloud $api, $path)
    {
        $current = '';
        foreach (explode('/', trim($path, '/')) as $part) {
            $current .= '/' . $part;
            $result = self::decode($api->createFolder($current));
            if (!in_array((int) ($result['httpCode'] ?? 0), [201, 405], true)) throw new RuntimeException('NEXTCLOUD_FOLDER_FAILED');
        }
    }

    public static function upload(array $files)
    {
        $api = new APINextCloud(['verify_ssl' => true, 'follow_redirects' => false, 'connect_timeout_ms' => 5000, 'timeout_ms' => 25000]);
        $folder = '/bmvmb/chat_uploads/' . date('Y/m/d');
        self::ensureFolders($api, $folder);
        $uploaded = [];
        try {
            foreach ($files as $file) {
                $baseName = pathinfo($file['name'], PATHINFO_FILENAME) ?: 'attachment';
                $timestamp = str_replace('.', '', sprintf('%.6F', microtime(true)));
                $storedName = $baseName . '_' . $timestamp . '.' . $file['extension'];
                $remotePath = $folder . '/' . $storedName;
                $put = self::decode($api->uploadFile($file['tmpPath'], $remotePath, ['prevent_overwrite' => true]));
                if ((int) ($put['httpCode'] ?? 0) !== 201 || (int) ($put['status'] ?? 0) !== 1) throw new RuntimeException('NEXTCLOUD_UPLOAD_FAILED');
                $share = self::decode($api->createShare($remotePath, 1));
                $data = is_array($share['data'] ?? null) ? $share['data'] : [];
                $url = isset($data['url']) ? rtrim((string) $data['url'], '/') : '';
                if ((int) ($share['status'] ?? 0) !== 1 || (int) ($share['httpCode'] ?? 0) !== 200 || stripos($url, 'https://') !== 0) throw new RuntimeException('NEXTCLOUD_SHARE_FAILED');
                $uploaded[] = ['remotePath' => $remotePath, 'shareId' => $data['id'] ?? null, 'index' => $file['index'], 'url' => $url . '/download', 'name' => $storedName, 'mimeType' => $file['mimeType'], 'size' => $file['size'], 'sha256' => $file['sha256']];
            }
            return array_map(static function ($file) {
                return ['index' => $file['index'], 'url' => $file['url'], 'name' => $file['name'], 'mimeType' => $file['mimeType'], 'size' => $file['size'], 'sha256' => $file['sha256']];
            }, $uploaded);
        } catch (Throwable $error) {
            foreach ($uploaded as $file) {
                if ($file['shareId'] !== null) $api->deleteShare($file['shareId']);
                $api->deleteFile($file['remotePath']);
            }
            throw $error;
        }
    }
}

if (!empty($entryUploadWebhookLibraryOnly)) {
    return;
}

function handleChatFileUploadWebhook()
{
    header('Content-Type: application/json; charset=utf-8');
    $requestId = null;
    try {
        $context = ChatUploadWebhookDispatcher::requestContext(
            'file',
            static function ($status, $code, $message, $failedRequestId = null) {
                ChatFileUploadWebhook::fail($status, $code, $message, $failedRequestId);
            }
        );
        $headers = $context['headers'];
        $clientId = $context['clientId'];
        $requestId = $context['requestId'];
        $timestamp = $context['timestamp'];
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > ChatFileUploadWebhook::MAX_BATCH_SIZE + 1048576) ChatFileUploadWebhook::fail(413, 'BATCH_TOO_LARGE', 'File batch exceeds 130 MiB', $requestId);
        $files = ChatFileUploadWebhook::prepare(ChatFileUploadWebhook::normalizedFiles($_FILES['files'] ?? null));
        $manifest = implode("\n", array_merge(['file-upload-v1', $clientId, $requestId, $timestamp, (string) count($files)], array_map(static function ($file) {
            return $file['index'] . ':' . $file['size'] . ':' . $file['sha256'];
        }, $files)));
        if (!ChatUploadWebhookDispatcher::hasValidSignature($headers, $manifest, $context['authenticator'])) ChatFileUploadWebhook::fail(401, 'UNAUTHORIZED', 'Unauthorized', $requestId);
        $uploaded = ChatFileUploadWebhook::upload($files);
        ChatFileUploadWebhook::respond(200, true, 'OK', 'Files uploaded successfully', $requestId, array_column($uploaded, 'url'), $uploaded);
    } catch (RuntimeException $error) {
        $code = $error->getMessage();
        $map = [
            'MISSING_FILES' => [400, 'MISSING_FILES', 'At least one file is required'],
            'TOO_MANY_FILES' => [413, 'TOO_MANY_FILES', 'A maximum of 5 files is allowed'],
            'INVALID_UPLOAD_STRUCTURE' => [400, 'INVALID_UPLOAD_STRUCTURE', 'Invalid files upload structure'],
            'INVALID_UPLOAD' => [400, 'INVALID_UPLOAD', 'Invalid HTTP upload'],
            'EMPTY_FILE' => [400, 'EMPTY_FILE', 'Empty files are not allowed'],
            'FILE_TOO_LARGE' => [413, 'FILE_TOO_LARGE', 'Each file must not exceed 26 MiB'],
            'BATCH_TOO_LARGE' => [413, 'BATCH_TOO_LARGE', 'File batch exceeds 130 MiB'],
            'UNSUPPORTED_FILE_TYPE' => [415, 'UNSUPPORTED_FILE_TYPE', 'Unsupported file type'],
        ];
        $entry = $map[$code] ?? [502, $code, 'File upload failed'];
        ChatFileUploadWebhook::fail($entry[0], $entry[1], $entry[2], $requestId);
    } catch (Throwable $error) {
        if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->error('Chat file upload failed: ' . get_class($error));
        ChatFileUploadWebhook::fail(500, 'INTERNAL_ERROR', 'File upload failed', $requestId);
    }
}

$requestHeaders = ChatUploadWebhookDispatcher::requestHeaders();
$uploadKind = ChatUploadWebhookDispatcher::resolveKind($requestHeaders);
$requestId = ChatUploadWebhookDispatcher::headerValue($requestHeaders, 'X-Request-Id', 'HTTP_X_REQUEST_ID');
if ($uploadKind === null) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    echo json_encode(ChatUploadWebhookDispatcher::responsePayload(false, 'INVALID_UPLOAD_KIND', 'X-Upload-Kind must be image or file', $requestId ?: null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($uploadKind === 'file') {
    handleChatFileUploadWebhook();
}

// Server-to-server image upload receiver. The chat_websocket client uses:
// $sugar_config['webhook']['image_upload']['auth_key|signature_key'].
header('Content-Type: application/json; charset=utf-8');

$responseRequestId = null;
$clientIdForLog = '';
$fileCountForLog = 0;
$fileHashesForLog = [];
$startedAt = microtime(true);

$logResponse = static function ($httpCode, $resultCode) use (
    &$clientIdForLog,
    &$responseRequestId,
    &$fileCountForLog,
    &$fileHashesForLog,
    $startedAt
) {
    $message = sprintf(
        'Image upload webhook client=%s request_id=%s files=%d hashes=%s status=%d code=%s elapsed_ms=%d',
        $clientIdForLog !== '' ? $clientIdForLog : '-',
        $responseRequestId !== null ? $responseRequestId : '-',
        $fileCountForLog,
        !empty($fileHashesForLog) ? implode(',', $fileHashesForLog) : '-',
        $httpCode,
        $resultCode,
        (int) round((microtime(true) - $startedAt) * 1000)
    );

    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $logMethod = $httpCode >= 500 ? 'error' : 'info';
        $GLOBALS['log']->{$logMethod}($message);
    } else {
        error_log($message);
    }
};

$sendResponse = static function ($httpCode, array $payload, $resultCode) use ($logResponse) {
    $body = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($body === false) {
        $httpCode = 500;
        $resultCode = 'RESPONSE_ENCODING_FAILED';
        $body = '{"success":false,"error":1,"code":"RESPONSE_ENCODING_FAILED","message":"Unable to encode response","requestId":null,"urls":[],"files":[]}';
    }

    http_response_code($httpCode);
    $logResponse($httpCode, $resultCode);
    echo $body;
    exit;
};

$respondError = static function ($httpCode, $errorCode, $message) use ($sendResponse, &$responseRequestId) {
    $sendResponse(
        $httpCode,
        ChatUploadWebhookDispatcher::responsePayload(false, $errorCode, $message, $responseRequestId),
        $errorCode
    );
};

$sanitizeLogValue = static function ($value) {
    if (!is_string($value)) {
        return '';
    }

    $sanitized = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $value);
    return substr((string) $sanitized, 0, 64);
};

try {
    $context = ChatUploadWebhookDispatcher::requestContext(
        'image',
        static function ($status, $code, $message) use ($respondError) {
            $respondError($status, $code, $message);
        }
    );
    $headers = $context['headers'];
    $clientId = $context['clientId'];
    $requestId = $context['requestId'];
    $timestampMs = $context['timestamp'];
    $clientIdForLog = $sanitizeLogValue($clientId);
    $responseRequestId = $requestId;

    $imagesField = $_FILES['images'] ?? null;
    if (is_array($imagesField) && array_key_exists('name', $imagesField)) {
        $fileCountForLog = is_array($imagesField['name']) ? count($imagesField['name']) : 1;
    }

    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($imagesField === null && $contentLength > NextCloudImageUploadService::MAX_BATCH_SIZE) {
        $respondError(413, 'BATCH_TOO_LARGE', 'Image batch exceeds 80 MiB');
    }

    $service = new NextCloudImageUploadService();
    $prepared = $service->validateAndPrepareFiles($imagesField);
    $fileCountForLog = count($prepared);
    foreach ($prepared as $preparedFile) {
        if (is_array($preparedFile) && isset($preparedFile['sha256']) && is_string($preparedFile['sha256'])) {
            $fileHashesForLog[] = substr($preparedFile['sha256'], 0, 12);
        }
    }

    $canonicalManifest = NextCloudImageUploadService::buildCanonicalManifest(
        $clientId,
        $requestId,
        $timestampMs,
        $prepared
    );

    if (!ChatUploadWebhookDispatcher::hasValidSignature($headers, $canonicalManifest, $context['authenticator'])) {
        $respondError(401, 'UNAUTHORIZED', 'Unauthorized');
    }

    $files = $service->uploadPreparedFiles($prepared);
    if (!is_array($files) || count($files) !== count($prepared)) {
        throw new RuntimeException('Image upload service returned an incomplete result');
    }

    $urls = [];
    foreach ($files as $file) {
        if (!is_array($file) || !isset($file['url']) || !is_string($file['url']) || $file['url'] === '') {
            throw new RuntimeException('Image upload service returned an invalid result');
        }
        $urls[] = $file['url'];
    }

    $sendResponse(
        200,
        ChatUploadWebhookDispatcher::responsePayload(
            true,
            'OK',
            'Images uploaded successfully',
            $requestId,
            $urls,
            $files
        ),
        'SUCCESS'
    );
} catch (NextCloudImageUploadException $exception) {
    $httpStatus = (int) $exception->getHttpStatus();
    if ($httpStatus < 400 || $httpStatus > 599) {
        $httpStatus = 500;
    }

    $errorCode = $exception->getErrorCode();
    if (!is_string($errorCode) || $errorCode === '') {
        $errorCode = 'INTERNAL_ERROR';
    }

    $respondError($httpStatus, $errorCode, $exception->getMessage());
} catch (Throwable $throwable) {
    $exceptionLog = sprintf(
        'Image upload webhook exception type=%s line=%d file=%s',
        get_class($throwable),
        $throwable->getLine(),
        $throwable->getFile()
    );

    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->error($exceptionLog);
    } else {
        error_log($exceptionLog);
    }

    $respondError(500, 'INTERNAL_ERROR', 'Internal server error');
}
