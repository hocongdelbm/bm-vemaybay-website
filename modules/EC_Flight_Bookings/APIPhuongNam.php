<?php
class APIPhuongNam {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;

    public function __construct() {
        global $sugar_config;
        $this->ENDPOINT = $sugar_config['api_autobook']['Endpoint'] ?? '';
        $this->API_SEARCH_KEY = $sugar_config['api_autobook']['SearchKey'] ?? '';
        $this->API_BOOKING_KEY = $sugar_config['api_autobook']['BookingKey'] ?? '';
    }

    public function searchFlights($airlineCode, $depCode, $desCode, $departDate, $returnDate = '', $adt = 1, $chd = 0, $inf = 0, $options = []) {
        try {
            // Header
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_SEARCH_KEY",
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
                    'cabin' => $cabin,
                    'api' => 'PN'
                ],
            ];

            // URL
            $url = $isInter ? "$this->ENDPOINT/getInterFlights" : "$this->ENDPOINT/getFlights";

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
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
                return json_encode(["status" => 0, "message" => "Can't connect to API", "description" => "cURL error $errorno: $error"]);
            }

            $arr = is_string($json) ? json_decode($json, true) : $json;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "data" => $arr,
                    "description" => "HTTP error $httpcode"
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

    /**
     * Verify flight info before booking
     * 
     * @param array $requestBody
     * @return string JSON {status, message, data, description}
     */
    public function verify($requestBody) {
        try {
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/verify");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 180);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status"    => 0,
                    "message"   => "Verify failed",
                    "data"      => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? ("Verify failed with ID " . $responseArr['ID']),
                    "data"      => $responseArr['Data'] ?? []
                ]);
            }
            
            return json_encode([
                "status"     => 1,
                "message"   => "Verify success",
                "data"      => $responseArr['Data'] ?? []
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Booking (or issuing tickets if flight within 24 hours)
     * 
     * @param array $requestBody
     * @return string JSON {status, message, data, description}
     */
    public function booking($requestBody) {
        try {
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/booking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200 && $httpcode != 201) {
                return json_encode([
                    "status" => 0,
                    "message" => "Booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? ("Booking failed with ID " . $responseArr['ID']),
                    "data"      => $responseArr
                ]);
            }

            return json_encode([
                "status"    => 1,
                "message"   => "Booking success",
                "data"      => $responseArr['Data'] ?? []
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Get booking detail
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function getBooking($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/getBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                'systemCode' => $systemCode,
                'bookingCode' => $bookingCode
            ]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 180);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Getting booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Getting booking failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            if(!isset($responseArr['Data']) || !$responseArr['Data'] || empty($responseArr['Data'])) {
                return json_encode([
                    "status"     => 0,
                    "message"   => "No data for $bookingCode in $systemCode",
                    "data"      => [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Get booking status on airlines
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function getBookingStatus($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/getBookingStatus");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                'systemCode' => $systemCode,
                'bookingCode' => $bookingCode
            ]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 24);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Getting booking status failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Getting booking status failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }
    
    /**
     * Sync booking data from airlines
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function syncBooking($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/syncBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                'systemCode' => $systemCode,
                'bookingCode' => $bookingCode
            ]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 24);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Sync booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Sync booking failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Pay for booking
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function payForBooking($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/payForBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }
            
            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Payment failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status" => 0,
                    "message" => $responseArr["Message"] ?? ("Payment failed with ID " . $responseArr['ID']),
                    "ErrorMessage" => $responseArr['ErrorMessage'] ?? "",
                ]);
            }

            return json_encode(["status" => 1, "message" => "Success"]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Get baggage info by booking code
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function getBaggageInfo($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];
            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode
            ];

            $curl = curl_init();
            if($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/getBaggageInfo");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 24);
            curl_setopt($curl, CURLOPT_TIMEOUT, 90);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Getting baggage info failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Getting baggage info failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            if(!isset($responseArr['Data']) || !$responseArr['Data'] || empty($responseArr['Data'])) {
                return json_encode([
                    "status"     => 0,
                    "message"   => "No baggage info for $bookingCode in $systemCode",
                    "data"      => [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Get seat maps info by booking code
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function getSeatMapsInfo($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];
            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode
            ];

            $curl = curl_init();
            if($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/getSeatMapsInfo");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 90);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Getting seat maps failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Getting seat maps failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            if(!isset($responseArr['Data']) || !$responseArr['Data'] || empty($responseArr['Data'])) {
                return json_encode([
                    "status"     => 0,
                    "message"   => "No seat maps data for $bookingCode in $systemCode",
                    "data"      => [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Adding baggage to booking
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @param array $services List services
     * @return string JSON {status, message, data}
     */
    public function addBaggage($systemCode, $bookingCode, $services) {
        try {
            if(!$systemCode || !$bookingCode || !$services || empty($systemCode) || empty($bookingCode) || empty($services)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode,
                        'services' => $services,
                    ]
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];
            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode,
                "Services" => $services
            ];

            $curl = curl_init();
            if($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/addBaggage");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Adding baggage failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? "Adding baggage failed",
                    "data"      => $responseArr['Data'] ?? [],
                ]);
            }

            return json_encode([
                "status"     => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? [],
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Get baggage
     * 
     * @param array $requestBody
     * @return string JSON {status, message, data}
     */
    public function getBaggage($requestBody) {
        try {
            return '';
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/getBaggage");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 24);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200 && $httpcode != 201) {
                return json_encode([
                    "status" => 0,
                    "message" => "Getting baggage info failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status"     => 0,
                    "message"   => $responseArr["Message"] ?? ("Getting baggage info failed with ID " . $responseArr['ID']),
                    "data"      => $responseArr['Data'] ?? []
                ]);
            }

            return json_encode([
                "status"    => 1,
                "message"   => "Success",
                "data"      => $responseArr['Data'] ?? []
            ]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Check price booking for VN before payment (Not working)
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function checkPriceBooking($systemCode, $bookingCode) {
        try {
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode,
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/checkPriceBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 90);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }
            
            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Checking price failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status" => 0,
                    "message" => $responseArr["Message"] ?? ("Checking price failed with ID " . $responseArr['ID']),
                    "data" => $responseArr['Data'] ?? [],
                ]);
            }

            return json_encode(["status" => 1, "message" => "Success", "data" => $responseArr['Data'] ?? []]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Cancel booking (Not working)
     * 
     * @param string $systemCode VJ, VN, QH, VU,...
     * @param string $bookingCode PNR
     * @return string JSON {status, message, data}
     */
    public function cancelBooking($systemCode, $bookingCode) {
        try {
            if(!$systemCode || !$bookingCode || empty($systemCode) || empty($bookingCode)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid params',
                    'params' => [
                        'systemCode' => $systemCode,
                        'bookingCode' => $bookingCode
                    ]
                ]);
            }
           
            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $requestBody = [
                "SystemCode" => $systemCode,
                "BookingCode" => $bookingCode,
                "CarrierCode" => ""
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL Failed to initialize"]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/cancelBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API",
                    "description" => "cURL error $errorno: $error"
                ]);
            }
            
            $responseArr = is_string($response) ? json_decode($response, true) : $response;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => "Cancel booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }

            if($responseArr['ID'] != 1) {
                return json_encode([
                    "status" => 0,
                    "message" => $responseArr["Message"] ?? ("Cancel booking failed with ID " . $responseArr['ID']),
                    "data" => $responseArr["Data"] ?? []
                ]);
            }

            return json_encode(["status" => 1, "message" => "Cancel booking success", "data" => $responseArr["Data"] ?? []]);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Detecting cabin (class) for searching flights
     * 
     * @param string $airlineCode VJ, VN, QH, VU
     * @param string $ticketClass
     * @return string M,W,C,F
     */
    public function detectCabin($airlineCode, $ticketClass) {
        if($airlineCode == 'VJ') {
            // M: Phổ thông
            // W: Phổ thông đặc biệt (Deluxe, Skyboss)
            // C: thương gia: Business
            $cMatches = ['boss', 'bus'];
            $wMatches = ['dlx', 'deluxe', 'sboss', 'sky'];
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            foreach ($cMatches as $c) if (stripos($ticketClass, $c) !== false) return 'C';
            return 'M';
        }
        elseif($airlineCode == 'VN') {
            // M: Phổ thông
            // W: Phổ thông đặc biệt
            // C: Thương gia
            // F: Hạng nhất
            $last_character = substr($ticketClass, -1);
            if(in_array($last_character, ['J','C','D', 'I'])) return 'C';
            elseif(in_array($last_character, ['W','Z','U'])) return 'W';
            elseif(in_array($last_character, ['B','M','S','H','K','L','Q','N','R','T','E','P','A','G'])) return 'M';
            else return 'M';
        }
        elseif($airlineCode == 'QH') {
            // M: Phổ thông (Economy Smart, Economy Saver, Hot Deal)
            // W: Phổ thông đặc biệt (Economy Flex)
            // C: thương gia (Business Smart, Business Flex)
            $cMatches = ['buz', 'bus'];
            $wMatches = ['flex'];
            foreach ($cMatches as $c) if (stripos($ticketClass, $c) !== false) return 'C';
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            return 'M';
        }
        elseif($airlineCode == 'VU') {
            // M: Phổ thông (Economy Saver, Economy Flex)
            // W: Phổ thông đặc biệt (Economy Premium)
            $wMatches = ['pre'];
            foreach ($wMatches as $w) if (stripos($ticketClass, $w) !== false) return 'W';
            return 'M';
        }
    }

    /**
     * Mapping passenger type ID
     * 
     * @param int|string $type 0, 1, 2
     * @return int
     */
    public function mappingPassengerType($type) {
        $arr = [1, 6, 5]; // (1: Người lớn, 6: Trẻ em, 5: Em bé)
        $type = (int)$type;
        return $arr[$type] ?? $type;
    }

    public function getAge($birthdate) {
        $birthDate = new DateTime($birthdate); // Create a DateTime object for the birthdate
        $currentDate = new DateTime(); // Current date and time
        $age = $currentDate->diff($birthDate); // Difference between current date and birthdate
        return $age->y; // Return the age in years
    }

    public function getLastName($fullname) {
        if (!is_string($fullname) || empty($fullname)) return $fullname;
        return explode(' ', $fullname)[0];
    }

    public function getFirstName($fullname) {
        if (!is_string($fullname) || empty($fullname)) return $fullname;
        $arr = explode(' ', $fullname);
        $n = count($arr);
        $result = '';
        for ($i = 1 ; $i < $n; $i++) $result .= $arr[$i] . ' ';
        return trim($result);
    }

    public function getOnlyFirstName($fullName) {
        $parts = preg_split('/\s+/', trim($fullName));
        return end($parts);
    }
}