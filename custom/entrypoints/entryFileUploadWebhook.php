<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/include/helpers/api/APINextCloud.php';

/** Server-to-server receiver for Chat document attachments. */
final class ChatFileUploadWebhook
{
    const MAX_FILE_COUNT = 5;
    const MAX_FILE_SIZE = 27262976; // 26 MiB
    const MAX_BATCH_SIZE = 27262976; // 26 MiB

    private static $extensions = [
        'pdf' => 'application/pdf', 'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain', 'csv' => 'text/csv', 'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
    ];

    public static function fail($status, $code, $message, $requestId = null)
    {
        self::respond($status, false, $code, $message, $requestId, [], []);
    }

    public static function respond($status, $success, $code, $message, $requestId, array $urls, array $files)
    {
        http_response_code($status);
        echo json_encode([
            'success' => $success,
            'error' => $success ? 0 : 1,
            'code' => $code,
            'message' => $message,
            'requestId' => $requestId,
            'urls' => $urls,
            'files' => $files,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    public static function headerValue(array $headers, $name, $serverName)
    {
        $value = $headers[strtolower($name)] ?? ($_SERVER[$serverName] ?? '');
        return is_string($value) ? trim($value) : '';
    }

    public static function normalizedFiles($field)
    {
        if (!is_array($field) || !isset($field['name'], $field['tmp_name'], $field['error'], $field['size'])) {
            throw new RuntimeException('MISSING_FILES');
        }
        if (!is_array($field['name'])) {
            return [[
                'name' => $field['name'], 'tmp_name' => $field['tmp_name'],
                'error' => $field['error'], 'size' => $field['size'],
            ]];
        }
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
            if ((int) $file['error'] === UPLOAD_ERR_INI_SIZE || (int) $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                throw new RuntimeException('FILE_TOO_LARGE');
            }
            if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('INVALID_UPLOAD');
            $size = filesize($file['tmp_name']);
            if ($size === false || $size < 1) throw new RuntimeException('EMPTY_FILE');
            if ($size > self::MAX_FILE_SIZE) throw new RuntimeException('FILE_TOO_LARGE');
            $total += $size;
            if ($total > self::MAX_BATCH_SIZE) throw new RuntimeException('BATCH_TOO_LARGE');
            $name = basename(str_replace('\\', '/', (string) $file['name']));
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

    public static function decode($response)
    {
        $decoded = is_array($response) ? $response : json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function ensureFolders(APINextCloud $api, $path)
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
        // Share the existing chat upload tree with image uploads.
        $folder = '/bmvmb/chat_uploads/' . date('Y/m/d');
        self::ensureFolders($api, $folder);
        $uploaded = [];
        try {
            foreach ($files as $file) {
                // Keep files directly in the daily chat upload folder. A
                // microsecond timestamp prevents collisions while retaining a
                // recognizable original name in Nextcloud.
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
                return [
                    'index' => $file['index'],
                    'url' => $file['url'],
                    'name' => $file['name'],
                    'mimeType' => $file['mimeType'],
                    'size' => $file['size'],
                    'sha256' => $file['sha256'],
                ];
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

header('Content-Type: application/json; charset=utf-8');
$requestId = null;
try {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case(is_array($headers) ? $headers : [], CASE_LOWER);
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') ChatFileUploadWebhook::fail(405, 'METHOD_NOT_ALLOWED', 'Method not allowed');
    if (!preg_match('/^multipart\/form-data(?:\s*;|$)/i', ChatFileUploadWebhook::headerValue($headers, 'Content-Type', 'CONTENT_TYPE'))) ChatFileUploadWebhook::fail(415, 'UNSUPPORTED_MEDIA_TYPE', 'Content-Type must be multipart/form-data');
    $authenticator = \custom\services\Webhook\WebhookAuthenticator::fromConfig('file_upload');
    if ($authenticator === null) ChatFileUploadWebhook::fail(500, 'CONFIGURATION_ERROR', 'File upload webhook is not configured');
    $clientId = ChatFileUploadWebhook::headerValue($headers, 'X-Client-Id', 'HTTP_X_CLIENT_ID');
    if ($clientId !== 'chat_websocket' || !$authenticator->verifyAuthKey($headers)) ChatFileUploadWebhook::fail(401, 'UNAUTHORIZED', 'Unauthorized');
    $requestId = ChatFileUploadWebhook::headerValue($headers, 'X-Request-Id', 'HTTP_X_REQUEST_ID');
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/D', $requestId)) ChatFileUploadWebhook::fail(400, 'INVALID_REQUEST_ID', 'X-Request-Id must be a lowercase UUID');
    $timestamp = ChatFileUploadWebhook::headerValue($headers, 'X-Timestamp', 'HTTP_X_TIMESTAMP');
    if (!preg_match('/^[0-9]{13}$/D', $timestamp) || abs(((int) floor(microtime(true) * 1000)) - (int) $timestamp) > 300000) ChatFileUploadWebhook::fail(401, 'TIMESTAMP_EXPIRED', 'Request timestamp is outside the allowed time window', $requestId);
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > ChatFileUploadWebhook::MAX_BATCH_SIZE + 1048576) ChatFileUploadWebhook::fail(413, 'BATCH_TOO_LARGE', 'File batch exceeds 26 MiB', $requestId);
    $files = ChatFileUploadWebhook::prepare(ChatFileUploadWebhook::normalizedFiles($_FILES['files'] ?? null));
    $manifest = implode("\n", array_merge(['file-upload-v1', $clientId, $requestId, $timestamp, (string) count($files)], array_map(static function ($file) { return $file['index'] . ':' . $file['size'] . ':' . $file['sha256']; }, $files)));
    if (!$authenticator->verifySignature($headers, $manifest)) ChatFileUploadWebhook::fail(401, 'UNAUTHORIZED', 'Unauthorized', $requestId);
    $uploaded = ChatFileUploadWebhook::upload($files);
    ChatFileUploadWebhook::respond(200, true, 'OK', 'Files uploaded successfully', $requestId, array_column($uploaded, 'url'), $uploaded);
} catch (RuntimeException $error) {
    $code = $error->getMessage();
    $map = ['MISSING_FILES' => [400, 'MISSING_FILES', 'At least one file is required'], 'TOO_MANY_FILES' => [413, 'TOO_MANY_FILES', 'A maximum of 5 files is allowed'], 'INVALID_UPLOAD_STRUCTURE' => [400, 'INVALID_UPLOAD_STRUCTURE', 'Invalid files upload structure'], 'INVALID_UPLOAD' => [400, 'INVALID_UPLOAD', 'Invalid HTTP upload'], 'EMPTY_FILE' => [400, 'EMPTY_FILE', 'Empty files are not allowed'], 'FILE_TOO_LARGE' => [413, 'FILE_TOO_LARGE', 'Each file must not exceed 26 MiB'], 'BATCH_TOO_LARGE' => [413, 'BATCH_TOO_LARGE', 'File batch exceeds 26 MiB'], 'UNSUPPORTED_FILE_TYPE' => [415, 'UNSUPPORTED_FILE_TYPE', 'Unsupported file type']];
    $entry = $map[$code] ?? [502, $code, 'File upload failed'];
    ChatFileUploadWebhook::fail($entry[0], $entry[1], $entry[2], $requestId);
} catch (Throwable $error) {
    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->error('Chat file upload failed: ' . get_class($error));
    ChatFileUploadWebhook::fail(500, 'INTERNAL_ERROR', 'File upload failed', $requestId);
}
