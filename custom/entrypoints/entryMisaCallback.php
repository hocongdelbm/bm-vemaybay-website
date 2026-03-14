<?php

// ============================================================
// AMIS Kế toán - Callback Endpoint
// ============================================================

header('Content-Type: application/json');

// Chỉ nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logInfo("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);

    http_response_code(405);
    echo json_encode(['success' => false, 'error_message' => 'Method Not Allowed']);
    exit;
}

// ============================================================
// 1. Đọc raw body từ MISA gửi lên
// ============================================================
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!$payload) {
    logError($payload);

    http_response_code(400);
    echo json_encode(['success' => false, 'error_message' => 'Invalid JSON']);
    exit;
}

// ============================================================
// 2. Xác thực chữ ký (signature) bằng SHA256HMAC
//    Key = app_id do MISA cấp cho bạn
// ============================================================
global $sugar_config;
$APP_ID = $sugar_config['misa']['app_id'] ?? '';
if (empty($APP_ID)) logInfo("App ID misa is empty");
define('APP_ID', $APP_ID);

$receivedSignature = $payload['signature'] ?? '';
$dataToVerify      = $payload['data'] ?? '';

// MISA ký data bằng HMAC-SHA256 với key là app_id
$expectedSignature = hash_hmac('sha256', $dataToVerify, APP_ID);
// if (!hash_equals($expectedSignature, $receivedSignature)) {
//     $payload['APP_ID_CONFIG'] = $APP_ID;
//     logError($payload);

//     http_response_code(401);
//     echo json_encode(['success' => false, 'error_message' => 'Invalid signature']);
//     exit;
// }

// ============================================================
// 3. Kiểm tra kết quả xử lý từ AMIS
// ============================================================
$success        = $payload['success']        ?? false;
$errorCode      = $payload['error_code']     ?? '';
$errorMessage   = $payload['error_message']  ?? '';
$orgCompanyCode = $payload['org_company_code'] ?? '';
$dataType       = $payload['data_type']      ?? 0;
$rawData        = $payload['data']           ?? '{}';

// Parse data (là JSON string lồng bên trong)
$data = json_decode($rawData, true);

// ============================================================
// 4. Xử lý theo data_type
// data_type phổ biến:
//   1  = Kết quả tạo/đồng bộ danh mục
//   6  = Kết quả đồng bộ chứng từ (phiếu xuất kho, hóa đơn...)
//   Xem đầy đủ tại: Danh sách các loại kết quả trả về
// ============================================================

if ($success) {
    // Xử lý thành công
    switch ($dataType) {
        case 1:
            handleDictionaryResult($orgCompanyCode, $data);
            break;
        case 6:
            handleVoucherResult($orgCompanyCode, $data);
            break;
        default:
            handleGenericResult($orgCompanyCode, $dataType, $data);
            break;
    }
} else {
    // AMIS báo lỗi — log lại để debug
    // logError($orgCompanyCode, $dataType, $errorCode, $errorMessage, $rawData);
    logError($payload);
}

// ============================================================
// 5. Trả về cho AMIS Kế toán — BẮT BUỘC theo chuẩn này
// ============================================================
echo json_encode([
    'success'       => true,
    'error_code'    => '',
    'error_message' => '',
]);
exit;

// ============================================================
// Các hàm xử lý nghiệp vụ
// ============================================================
function handleDictionaryResult(string $orgCompanyCode, ?array $data): void
{
    // Xử lý kết quả đồng bộ danh mục (đối tượng, vật tư, kho...)
    // $data chứa danh sách danh mục đã được tạo/cập nhật trong AMIS
    logInfo("[$orgCompanyCode] Dictionary synced", $data);

    // Ví dụ: cập nhật mapping ID trong DB của bạn
    // updateLocalDictionaryMapping($orgCompanyCode, $data);
}

function handleVoucherResult(string $orgCompanyCode, ?array $data): void
{
    // Xử lý kết quả tạo chứng từ
    // $data['voucher'] chứa danh sách chứng từ đã được ghi nhận
    $vouchers = $data['voucher'] ?? [];
    foreach ($vouchers as $voucher) {
        $orgRefId  = $voucher['org_refid']  ?? '';
        $refnoFinance = $voucher['refno_finance'] ?? '';
        logInfo("[$orgCompanyCode] Voucher created: $orgRefId => $refnoFinance", $voucher);

        // Ví dụ: cập nhật trạng thái đơn hàng trong DB của bạn
        // markOrderAsSynced($orgCompanyCode, $orgRefId, $refnoFinance);
    }
}

function handleGenericResult(string $orgCompanyCode, int $dataType, ?array $data): void
{
    logInfo("[$orgCompanyCode] Callback received for data_type=$dataType", $data);
}

// ============================================================
// Các hàm xử lý Lưu log
// ============================================================

function writeLog(string $filename, string $line): void
{
    $curYear = date('Y');
    $curMonth = date('m');
    $path = 'secure_sessions/misa_logs';
    $filepath = "$path/$curYear/$curMonth/" . $filename;

    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($filepath, $line . PHP_EOL, FILE_APPEND);
}

function logInfo(string $message, ?array $context = null): void
{
    $line = date('Y-m-d H:i:s') . " [INFO] $message";
    if ($context) {
        $line .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    writeLog('amis_callback.log', $line);
}

function logError(array $payload): void
{
    $org      = $payload['org_company_code'] ?? '';
    $dataType = $payload['data_type'] ?? 0;
    $code     = $payload['error_code'] ?? '';
    $msg      = $payload['error_message'] ?? '';
    $raw      = json_encode($payload, JSON_UNESCAPED_UNICODE);

    $line = date('Y-m-d H:i:s') . " [ERROR] org=$org dataType=$dataType code=$code msg=$msg raw=$raw";
    writeLog('amis_callback_error.log', $line);
}
