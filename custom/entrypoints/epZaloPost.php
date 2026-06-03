<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'custom/include/helpers/api/APIZaloOA.php';

// ============================================================
// Zalo Post - Endpoint (No IP Whitelist, Auth by Api-Key)
// ============================================================
header('Content-Type: application/json');

// 1. Authenticate with Api-Key
global $sugar_config;
$headers = getallheaders();
$api_key = $headers['Api-Key'] ?? '';

if ($api_key !== ($sugar_config['api_key']['non_auth_entrypoint'] ?? '')) {
    http_response_code(401);
    echo json_encode([
        "status" => 0,
        "message" => "Unauthorized"
    ]);
    exit();
}

// 2. Read request body
$contentType = $headers['Content-Type'] ?? '';
$reqBody = null;

if (stripos($contentType, 'application/json') !== false) {
    $reqBody = json_decode(file_get_contents('php://input'), true);
} else {
    $reqBody = $_POST;
}

if (!$reqBody) {
    http_response_code(400);
    echo json_encode([
        "status" => 0,
        "message" => "Invalid JSON Payload"
    ]);
    exit();
}

// 3. Extract method and params
$method = $reqBody['method'] ?? '';
$params = $reqBody['params'] ?? [];

// 4. Handle requested method
switch ($method) {
    case 'createArticle':
        echo json_encode(createArticle($params), JSON_UNESCAPED_UNICODE);
        break;

    case 'uploadVideoArticle':
        echo json_encode(uploadVideoArticle($params), JSON_UNESCAPED_UNICODE);
        break;

    case 'verifyVideoArticleStatus':
        echo json_encode(verifyVideoArticleStatus($params), JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            "status" => 0,
            "message" => "Invalid method: $method"
        ]);
        break;
}
exit();

// ============================================================
// Logic Functions
// ============================================================

function executeZaloAction($params, callable $action, $successMessage) {
    try {
        $app_id = $params['app_id'] ?? '';
        $oa_id = $params['oa_id'] ?? '';
        $api = new APIZaloOA($app_id, $oa_id);
        
        if (empty($api->app) || empty($api->app->id)) {
            return formatResponse(0, "Không tìm thấy cấu hình Zalo App. Vui lòng truyền tham số app_id hợp lệ.");
        }

        unset($params['app_id'], $params['oa_id']);

        $response = $action($api, $params);

        if (is_array($response)) {
            return $response;
        }

        $responseArr = json_decode($response, true);
        
        if (isset($responseArr['error']) && $responseArr['error'] == 0) {
            return formatResponse(1, $successMessage, $responseArr['data'] ?? null);
        }
        
        return formatResponse(0, $responseArr['message'] ?? $api->get_error_description($responseArr['error'] ?? -200), $responseArr);
    }
    catch(Throwable $th) {
        $logId = LoggerHelper::generateLogId();
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }
        return formatResponse(0, "Exception error $logId", null, ['errorId' => $logId]);
    }
}

function formatResponse($status, $message, $data = null, $extra = []) {
    return array_merge([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ], $extra);
}

function createArticle($params) {
    return executeZaloAction($params, function($api, $params) {
        if(empty($params['type']) || !in_array($params['type'], ['normal', 'video'])) {
            return formatResponse(0, "Tham số type không hợp lệ (cần normal hoặc video)");
        }
        
        if(empty($params['title'])) {
            return formatResponse(0, "Thiếu tiêu đề bài viết (title)");
        }

        return $api->create_article($params);
    }, "Gửi yêu cầu tạo bài viết thành công");
}

function uploadVideoArticle($params) {
    return executeZaloAction($params, function($api, $params) {
        $file_path = $params['file_path'] ?? '';
        
        if (empty($file_path) || !file_exists($file_path)) {
            return formatResponse(0, "Thiếu đường dẫn file video (file_path) hoặc file không tồn tại.");
        }

        $file_size = filesize($file_path);
        if ($file_size > 50 * 1024 * 1024) {
            return formatResponse(0, "Kích thước video vượt quá 50MB.");
        }

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if (!in_array($extension, ['avi', 'mp4'])) {
            return formatResponse(0, "Định dạng file không hợp lệ (chỉ hỗ trợ avi, mp4).");
        }

        return $api->upload_video_article($file_path);
    }, "Tải video lên thành công.");
}

function verifyVideoArticleStatus($params) {
    return executeZaloAction($params, function($api, $params) {
        $token = $params['token'] ?? '';
        
        if (empty($token)) {
            return formatResponse(0, "Thiếu tham số token của video.");
        }

        return $api->verify_video_article($token);
    }, "Kiểm tra trạng thái video thành công.");
}
