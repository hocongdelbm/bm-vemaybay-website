<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : "";

    if($action == 'update-flight-fare') {
        $depCode = isset($_POST['depCode']) ? strtoupper($_POST['depCode']) : "";
        $arvCode = isset($_POST['arvCode']) ? strtoupper($_POST['arvCode']) : "";
        $depDate = isset($_POST['depDate']) ? $_POST['depDate'] : "";
        $retDate = isset($_POST['retDate']) ? $_POST['retDate'] : "";

        if(empty($depCode) || empty($arvCode) || empty($depDate)) {
            echo json_encode([
                "error" => 1,
                "message" => "Dữ liệu không hợp lệ",
            ]);
        }

        $post_data = [
            "api_key"    => 'lbmcgHPmfwmug@9FopPpK6KQfnsSx,gL10QWjE8I9Mnm5nWq7@',
            "airline"    => 'VJ',
            "depCode"    => $depCode,
            "arvCode"    => $arvCode,
            "departDate" => $depDate,
            "returnDate" => $retDate,
            "adt"        => 1,
            "chd"        => 0,
            "inf"        => 0,
            "is_live"    => 1
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://data01.timchuyenbay.net/api/v2/get_flight');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        $json = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        echo $json;
        exit();
    }

    echo json_encode([
        "error" => 1,
        "message" => "Nothing to do",
    ]);
    exit();
}
echo json_encode([
    "error" => 1,
    "message" => "Invalid method",
]);
exit();