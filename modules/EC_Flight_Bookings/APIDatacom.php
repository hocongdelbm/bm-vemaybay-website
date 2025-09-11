<?php
class APIDatacom {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;
    public $SUPPLIER_ID;

    public function __construct() {
        global $sugar_config;
        $this->ENDPOINT         = $sugar_config['api_datacom']['Endpoint'] ?? '';
        $this->API_SEARCH_KEY   = $sugar_config['api_datacom']['SearchKey'] ?? '';
        $this->API_BOOKING_KEY  = $sugar_config['api_datacom']['BookingKey'] ?? '';
        $this->SUPPLIER_ID      = $sugar_config['api_datacom']['SupplierID'] ?? '';
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
                    'api' => 'Datacom',
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
                    "message" => "Can not connect to $url",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $arr = is_string($json) ? json_decode($json, true) : $json;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => $arr['message'] ?? "Can not connect to $url",
                    "description" => "HTTP error $httpcode",
                    "data" => $arr
                ]);
            }

            $arr = json_decode($json, true);
            $arr['status'] = (int)!$arr['error']; // Convert key error to status
            unset($arr['error']);
            return json_encode($arr);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }
}