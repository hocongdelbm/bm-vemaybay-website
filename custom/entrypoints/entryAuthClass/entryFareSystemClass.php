<?php
require_once "custom/entrypoints/entryAuthClass/entryClass.php";

/**
 * Class entryFareSystemClass
 * 
 * Using for searching and updating flight information
 */
class entryFareSystemClass extends entryClass {
    private $enpoint;
    private $key;

    public function __construct() {
        parent::__construct();
        global $sugar_config;
        $this->enpoint = $sugar_config['api_autobook']['Endpoint'];
        $this->key = $sugar_config['api_autobook']['SearchKey'];
    }
    
    function searchFlightBM($params = []) {   
        // Log request để debug
        $GLOBALS['log']->fatal("searchFlight called with params: " . json_encode($params, JSON_UNESCAPED_UNICODE));        
        
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['departDate']) ? $params['departDate'] : '';
        $returnDate = isset($params['returnDate']) ? $params['returnDate'] : '';
        $isLive = $params['isLive'];
        
        // Validate required fields
        if (empty($airlineCode) || empty($depCode) || empty($desCode) || empty($departDate)) {
            return json_encode([
                'error' => 1,
                'message' => 'Missing required parameters',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Chuẩn bị data để gửi đến API
        $postData = [
            "airlineCode" => $airlineCode,
            "depCode" => $depCode,
            "desCode" => $desCode,
            "departDate" => $departDate,
            "returnDate" => $returnDate,
            "adt" => 1,
            "chd" => 1,
            "inf" => 1,
            "options" => [
                "isLive" => $isLive
            ]
        ];

        // Khởi tạo CURL
        $curl = curl_init();
        $url = $this->enpoint.'/getFlights';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'API-Key: '.$this->key,
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        // Kiểm tra lỗi CURL
        if ($error) {
            $GLOBALS['log']->error("CURL Error: " . $error);
            return json_encode([
                'error' => 1,
                'message' => 'CURL Error: ' . $error,
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Kiểm tra HTTP status code
        if ($httpCode !== 200) {
            $GLOBALS['log']->fatal("API returned status code: " . $httpCode);
            $GLOBALS['log']->fatal("Response: " . $response);
            return json_encode([
                'error' => 1,
                'message' => 'API returned status code: ' . $httpCode,
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Parse response để kiểm tra
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $GLOBALS['log']->fatal("JSON decode error: " . json_last_error_msg());
            return json_encode([
                'error' => 1,
                'message' => 'Invalid JSON response from API',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Log successful response
        $GLOBALS['log']->fatal("API Response received successfully");
        
        // Trả về response từ API
        return $response;
    }

    
    public function updateTicket($params = []) {    
        // Log request để debug
        $GLOBALS['log']->fatal("updateTicket called with params: " . json_encode($params, JSON_UNESCAPED_UNICODE));
        
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['depDate']) ? $params['depDate'] : '';
        $flightNo = isset($params['flightNo']) ? trim($params['flightNo']) : '';
        $fare = isset($params['fare']) ? intval($params['fare']) : 0;
        
        // Validate required fields
        if (empty($airlineCode)) {
            return json_encode([
                'error' => 1,
                'message' => 'Thiếu mã hãng hàng không (airlineCode)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        if (empty($depCode)) {
            return json_encode([
                'error' => 1,
                'message' => 'Thiếu mã sân bay đi (depCode)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        if (empty($desCode)) {
            return json_encode([
                'error' => 1,
                'message' => 'Thiếu mã sân bay đến (desCode)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        if (empty($departDate)) {
            return json_encode([
                'error' => 1,
                'message' => 'Thiếu ngày khởi hành (depDate)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        if (empty($flightNo)) {
            return json_encode([
                'error' => 1,
                'message' => 'Thiếu số hiệu chuyến bay (flightNo)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        if ($fare <= 0) {
            return json_encode([
                'error' => 1,
                'message' => 'Giá vé không hợp lệ (fare phải > 0)',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Validate endpoint và key
        if (empty($this->enpoint) || empty($this->key)) {
            return json_encode([
                'error' => 1,
                'message' => 'Chưa cấu hình API endpoint hoặc key',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Chuẩn bị data để gửi đến API
        $patchData = [
            "airlineCode" => $airlineCode,
            "depCode" => $depCode,
            "desCode" => $desCode,
            "depDate" => $departDate,
            "flightNo" => $flightNo,
            "updateData" => [
                "fare" => $fare
            ]
        ];
        
        // Log data gửi đi
        $GLOBALS['log']->fatal("Sending data to API: " . json_encode($patchData, JSON_UNESCAPED_UNICODE));
        
        // Khởi tạo CURL
        $curl = curl_init();
        
        // URL endpoint cho update
        $updateUrl = $this->enpoint. '/updateFlight';
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $updateUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($patchData),
            CURLOPT_HTTPHEADER => [
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30, // Timeout 30 giây
            CURLOPT_CONNECTTIMEOUT => 10, // Connection timeout 10 giây
            CURLOPT_SSL_VERIFYPEER => false, // Tắt verify SSL (nếu cần)
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($curl);
        
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        // Kiểm tra lỗi CURL
        if ($error) {
            $GLOBALS['log']->error("CURL Error: " . $error);
            return json_encode([
                'error' => 1,
                'message' => 'Lỗi kết nối API: ' . $error,
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        // Kiểm tra HTTP status code
        if ($httpCode !== 200 && $httpCode !== 201) {
            $GLOBALS['log']->fatal("API returned status code: " . $httpCode);
            $GLOBALS['log']->fatal("Response: " . $response);
            
            // Thông báo lỗi theo status code
            $errorMessage = 'API trả về mã lỗi: ' . $httpCode;
            switch ($httpCode) {
                case 400:
                    $errorMessage = 'Dữ liệu gửi lên không hợp lệ (400 Bad Request)';
                    break;
                case 401:
                    $errorMessage = 'API Key không hợp lệ (401 Unauthorized)';
                    break;
                case 403:
                    $errorMessage = 'Không có quyền truy cập (403 Forbidden)';
                    break;
                case 404:
                    $errorMessage = 'Không tìm thấy endpoint (404 Not Found)';
                    break;
                case 500:
                    $errorMessage = 'Lỗi máy chủ API (500 Internal Server Error)';
                    break;
                case 503:
                    $errorMessage = 'API tạm thời không khả dụng (503 Service Unavailable)';
                    break;
            }
            
            return json_encode([
                'error' => 1,
                'message' => $errorMessage,
                'data' => [
                    'httpCode' => $httpCode,
                    'response' => $response
                ]
            ], JSON_UNESCAPED_UNICODE);
        }
        return $response;
        
    }
}
?>