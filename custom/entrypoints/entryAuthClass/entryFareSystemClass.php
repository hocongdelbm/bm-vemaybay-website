<?php
require_once "custom/entrypoints/entryClass.php";

/**
 * Class entryFareSystemClass
 * 
 * Using for searching and updating flight information
 */
class entryFareSystemClass extends entryClass
{
    private $endpoint;
    private $key;

    public function __construct()
    {
        parent::__construct();
        global $sugar_config;
        $this->endpoint = $sugar_config['api_autobook']['Endpoint'];
        $this->key = $sugar_config['api_autobook']['SearchKey'];
    }

    public function searchFlightBM($params = []) {
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['departDate']) ? $params['departDate'] : '';
        $returnDate = isset($params['returnDate']) ? $params['returnDate'] : '';
        $isLive = $params['isLive'] ?? false;

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
                "isLive" => $isLive,
                "promoCode" => "VJSALE20"
            ]
        ];

        // Khởi tạo CURL
        $curl = curl_init();
        $url = $this->endpoint . '/getFlights';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Trả về response từ API
        return $response;
    }
    public function searchInterFlightBM($params = []) {
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['departDate']) ? $params['departDate'] : '';
        $returnDate = isset($params['returnDate']) ? $params['returnDate'] : '';
        $isLive = $params['isLive'] ?? false;

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
                "isLive" => $isLive,
                "promoCode" => "VJSALE20"
            ]
        ];

        // Khởi tạo CURL
        $curl = curl_init();
        $url = $this->endpoint . '/getInterFlights';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Trả về response từ API
        return $response;
    }

    public function updateFlight($params = [])
    {
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['depDate']) ? $params['depDate'] : '';
        $flightNo = isset($params['flightNo']) ? trim($params['flightNo']) : '';
        $fare = isset($params['fare']) ? intval($params['fare']) : 0;

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

        // Khởi tạo CURL
        $curl = curl_init();

        // URL endpoint cho update
        $updateUrl = $this->endpoint . '/updateFlight';

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
            CURLOPT_SSL_VERIFYPEER => 0, // Tắt verify SSL (nếu cần)
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($curl);

        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $response;
    }

    public function updateFlightAll($params = [])
    {
        // Lấy parameters từ request
        $airlineCode = isset($params['airlineCode']) ? trim($params['airlineCode']) : '';
        $depCode = isset($params['depCode']) ? strtoupper(trim($params['depCode'])) : '';
        $desCode = isset($params['desCode']) ? strtoupper(trim($params['desCode'])) : '';
        $departDate = isset($params['depDate']) ? $params['depDate'] : '';
        $fareChange = isset($params['fareChange']) ? intval($params['fareChange']) : 0;

        // echo $params;
        // return $params;
        // exit;
        // Chuẩn bị data để gửi đến API
        $putData = [
            "airlineCode" => $airlineCode,
            "depCode" => $depCode,
            "desCode" => $desCode,
            "depDate" => $departDate,
            "updateData" => [
                "fareChange" => $fareChange
            ]
        ];

        $curl = curl_init();

        // URL endpoint cho update
        $updateAllUrl = $this->endpoint . '/massUpdateFlight';

        curl_setopt_array($curl, [
            CURLOPT_URL => $updateAllUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($putData),
            CURLOPT_HTTPHEADER => [
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function getBaggageOption($params = []) {
        $airlineCode = trim($params['airlineCode'] ?? '');
        $airlineMapping = [
            "VNA" => "VN",
            "BBA" => "QH",
            "VTA" => "VU",
            "VJA" => "VJ",
            "VNP" => "VN"
        ];

        if (isset($airlineMapping[$airlineCode])) {
            $airlineCode = $airlineMapping[$airlineCode];
        }

        $patchData = [
            "airlineCode" => $airlineCode
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "{$this->endpoint}/service/getOptionBaggage",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => json_encode($patchData),
            CURLOPT_HTTPHEADER => [
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $response;
    }

    /**
     * Get min price in month
     * 
     * @param array [depCode, desCode, month, year]
     * @return string JSON
     * @author DucPham
     */
    public function getMinPriceInMonth($params = []) {
        // Lấy parameters từ request
        $depCode = $params['depCode'] ?? '';
        $desCode = $params['desCode'] ?? '';
        $month   = $params['month'] ?? null;
        $year    = $params['year'] ?? date('Y');
        
        $reqBody = [
            "depCode" => $depCode,
            "desCode" => $desCode,
            "month" => $month,
            "year" => $year,
        ];

        // Khởi tạo CURL
        $curl = curl_init();
        $url = "{$this->endpoint}/cheapPrice/getMinPriceMonth";
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => json_encode($reqBody),
            CURLOPT_HTTPHEADER => [
                'API-Key: ' . $this->key,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // ✅ Handle errors before returning
        if ($error || $response === false) {
            return json_encode(['error' => true, 'message' => $error ?: 'cURL failed']);
        }

        if ($httpCode !== 200) {
            return json_encode(['error' => true, 'message' => "HTTP $httpCode"]);
        }

        return $response;
    }
}
?>
