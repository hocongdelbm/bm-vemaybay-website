<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('Content-Type: application/json');

if (empty($_SESSION['authenticated_user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Not authenticated'));
    exit;
}

if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('success' => false, 'message' => 'No file uploaded'));
    exit;
}

if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
    echo json_encode(array('success' => false, 'message' => 'Ảnh vượt quá 2MB'));
    exit;
}

$allowedMimes = array(
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
);

$tmpPath = $_FILES['logo']['tmp_name'];
$mime = mime_content_type($tmpPath);

if (!isset($allowedMimes[$mime])) {
    echo json_encode(array('success' => false, 'message' => 'Định dạng ảnh không hợp lệ'));
    exit;
}

require_once 'custom/include/helpers/api/APINextCloud.php';

$api = new APINextCloud();

$folderParts = array(
    'bmvmb',
    'bmvmb/modules',
    'bmvmb/modules/ec_airlines',
    'bmvmb/modules/ec_airlines/logos',
);
foreach ($folderParts as $part) {
    $api->createFolder($part);
}

$ext = $allowedMimes[$mime];
$remoteFileName = 'bmvmb/modules/ec_airlines/logos/logo_' . create_guid() . '.' . $ext;

$uploadResult = json_decode($api->uploadFile($tmpPath, $remoteFileName), true);
if (empty($uploadResult['status']) || (int) $uploadResult['status'] !== 1) {
    $GLOBALS['log']->error('UploadAirlineLogo: Upload failed - ' . json_encode($uploadResult));
    echo json_encode(array('success' => false, 'message' => 'Tải lên NextCloud thất bại'));
    exit;
}

$shareResult = json_decode($api->createShare($remoteFileName, 1), true);
if (empty($shareResult['status']) || (int) $shareResult['status'] !== 1) {
    $GLOBALS['log']->error('UploadAirlineLogo: Share creation failed - ' . json_encode($shareResult));
    echo json_encode(array('success' => false, 'message' => 'Tạo link chia sẻ thất bại'));
    exit;
}

$shareUrl = rtrim($shareResult['data']['url'] ?? '', '/') . '/preview';

echo json_encode(array('success' => true, 'url' => $shareUrl));
