<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    require_once "custom/include/helpers/api/APIZaloOA.php";
    
    $oa_id = isset($_GET['oa_id']) ? $_GET['oa_id'] : '';
    $code  = isset($_GET['code']) ? $_GET['code'] : '';
    $refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if(!empty($oa_id) && !empty($code) && $refer === 'https://oauth.zaloapp.com/') {
        $zaloOA = new APIZaloOA();
        $zaloOA->get_new_token_auth($code);

        header("Location: index.php?module=EC_Zalo&action=index");
        exit();
    }
}

echo json_encode([
    "error" => 1,
    "message" => "Not found",
]);
header("HTTP/1.0 404 Not found");
exit();
?>