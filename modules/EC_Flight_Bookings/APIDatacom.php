<?php
class APIDatacom {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;
    private $API_NAME;

    public function __construct() {
        global $sugar_config;
        $this->ENDPOINT = $sugar_config['api_autobook']['Endpoint'] ?? '';
        $this->API_SEARCH_KEY = $sugar_config['api_autobook']['SearchKey'] ?? '';
        $this->API_BOOKING_KEY = $sugar_config['api_autobook']['BookingKey'] ?? '';
        $this->API_NAME = 'datacom';
    }

    /**
     * Search flights
     * 
     * @param string $airlineCode VN, VJ, QH, VU, 1S, 1A, 1G, FO, AA
     * @param string $depCode
     * @param string $desCode
     * @param string $departDate yyyy-mm-dd
     * @param string $returnDate yyyy-mm-dd
     * @param int $adt
     * @param int $chd
     * @param int $inf
     * @param array $options
     * @param string JSON
     */
    public function searchFlights($airlineCode, $depCode, $desCode, $departDate, $returnDate = '', $adt = 1, $chd = 0, $inf = 0, $options = []) {
        try {
            // Header
            $header = [
                "Content-Type: application/json",
                "API-Key: {$this->API_SEARCH_KEY}",
            ];

            // Request body
            $cabin = $options['cabin'] ?? '';
            $isInter = (int)($options['isInter'] ?? 0);
            $requestBody = [
                "airlineCode"   => $airlineCode,
                "depCode"       => $depCode,
                "desCode"       => $desCode,
                "departDate"    => $departDate,
                "returnDate"    => $returnDate,
                "adt"           => $adt,
                "chd"           => $chd,
                "inf"           => $inf,
                "options"       => [
                    'api' => strtoupper($this->API_NAME),
                    'cabin' => $cabin,
                ],
            ];

            // URL
            $url = $isInter ? "$this->ENDPOINT/getInterFlights" : "$this->ENDPOINT/getFlights";

            // Execute
            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            $json = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($json === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $arr = is_string($json) ? json_decode($json, true) : $json;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => $arr['message'] ?? "Can't connect to API",
                    "description" => "HTTP error $httpcode",
                    "data" => $arr
                ]);
            }

            $arr = json_decode($json, true);
            $arr['status'] = (int)!$arr['error']; // Convert key error to status
            unset($arr['error']);
            $arr['requestBody'] = $requestBody;
            return json_encode($arr);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Book a flight
     * 
     * @param array $requestBody
     * @return string Original response from agency
     */
    public function book($requestBody) {
        try {
            // Header
            $header = [
                "Content-Type: application/json",
                "API-Key: {$this->API_BOOKING_KEY}",
            ];

            // URL
            $url = "$this->ENDPOINT/booking/$this->API_NAME/book";

            // Execute
            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 16);
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "data" => null,
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            // Check and format response
            if($httpcode != 200 && $httpcode != 201) {
                return json_encode([
                    "status" => 0,
                    "message" => "Booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            $statusCode = $responseArr["StatusCode"] ?? "";
            $success    = $responseArr["Success"] ?? false;
            $message    = $responseArr["Message"] ?? "Booking failed";
            if($success === true && in_array($statusCode, ['000', '0000'])) {
                return json_encode([
                    "status"    => 1,
                    "message"   => "Booking success",
                    "data"      => $responseArr
                ]);
            }
            else {
                return json_encode([
                    "status"     => 0,
                    "message"   => $message,
                    "data"      => $responseArr
                ]);
            }
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Convert date format from agency to standard format
     * 
     * @param string $date dmY
     * @param string $format d-m-Y
     * @return string
     */
    public function convertDate($date, $format = 'd-m-Y') {
        $dateObj = DateTime::createFromFormat('dmY', $date);
        if ($dateObj) return $dateObj->format($format);
        return '';
    }

    /**
     * Convert datetime format from agency to standard format
     * 
     * @param string $datetime dmY Hi
     * @param string $format d-m-Y H:i
     * @return string
     */
    public function convertDatetime($datetime, $format = 'd-m-Y H:i') {
        $datetimeObj = DateTime::createFromFormat('dmY Hi', $datetime);
        if ($datetimeObj) return $datetimeObj->format($format);
        return '';
    }
}