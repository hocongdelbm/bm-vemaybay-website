<?php
class APIPhuongNam {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;
    private $API_NAME;

    public function __construct() {
        global $sugar_config;
        $this->ENDPOINT = $sugar_config['api_autobook']['Endpoint'] ?? '';
        $this->API_SEARCH_KEY = $sugar_config['api_autobook']['SearchKey'] ?? '';
        $this->API_BOOKING_KEY = $sugar_config['api_autobook']['BookingKey'] ?? '';
        $this->API_NAME = 'phuongnam';
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

        $path = $isInter ? "getInterFlights" : "getFlights";
        $timeout = $isInter ? 180 : 120;
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_SEARCH_KEY",
        ];

        $res = $this->sendRequest('POST', $path, json_encode($requestBody), $header, [CURLOPT_TIMEOUT => $timeout + 10]);

        $resArr = json_decode($res, true);
        if(!isset($resArr['status']) && isset($resArr['error'])) {
            $resArr['status'] = (int)!$resArr['error'];
            unset($resArr['error']);
            return json_encode($resArr);
        }
        return $res;
    }

    /**
     * Verify flight info before booking
     * 
     * @param array $requestBody
     * @return string JSON
     */
    public function verify($requestBody) {
        $path = "booking/{$this->API_NAME}/verify";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        return $this->sendRequest('POST', $path, json_encode($requestBody), $header, [CURLOPT_TIMEOUT => 180 + 10]);
    }

    /**
     * Create booking (or issue ticket if flight within 24 hours)
     * 
     * @param array $requestBody
     * @return string JSON
     */
    public function book($requestBody) {
        $path = "booking/{$this->API_NAME}/book";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        return $this->sendRequest('POST', $path, json_encode($requestBody), $header, [CURLOPT_TIMEOUT => 200 + 10]);
    }

    /**
     * Get booking detail
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode VJ, VN, 1A,...
     * @return string JSON {status, message, data}
     */
    public function getBooking($bookingCode, $systemCode = '') {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6) {
            return json_encode([
                "status" => 0,
                "message" => trim("PNR $bookingCode không hợp lệ"),
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }

        $path = "getBooking?pnr=$bookingCode";
        if(!empty($systemCode)) $path .= "&systemCode=$systemCode";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];

        return $this->sendRequest('GET', $path, null, $header);
    }

    /**
     * Get baggage info
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode VJ, VN, QH, VU,...
     * @return string JSON {status, message, data}
     */
    public function getBaggageInfo($bookingCode, $systemCode) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6
            || !is_string($systemCode) || empty($systemCode)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }

        $path = "booking/{$this->API_NAME}/getBaggageInfo";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode" => $bookingCode,
            "systemCode" => $systemCode
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header);
    }

    /**
     * Pay for booking
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode VJ, VN, QH, VU,...
     * @return string JSON
     */
    public function payBooking($bookingCode, $systemCode) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6
            || !is_string($systemCode) || empty($systemCode)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }

        $path = "booking/{$this->API_NAME}/payBooking";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "systemCode" => $systemCode,
            "bookingCode" => $bookingCode
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 200 + 10]);
    }

    /**
     * Add baggage
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param array $baggageData
     * 
     * @return string JSON
     */
    public function addBaggage($bookingCode, $systemCode, $baggageData) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || empty($systemCode)
            || !is_array($baggageData) || empty($baggageData)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }

        $path = "booking/{$this->API_NAME}/addBaggage";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode" => $bookingCode,
            "systemCode" => $systemCode,
            "baggageData" => $baggageData["Value"] ?? []
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 120 + 10]);
    }

    /**
     * Cancel booking
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * 
     * @return string JSON
     */
    public function cancelBooking($bookingCode, $systemCode) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || strlen($systemCode) != 2
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Invalid params",
                "data" => null
            ]);
        }
        
        $path = "booking/{$this->API_NAME}/cancelBooking";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode" => $bookingCode,
            "systemCode"  => $systemCode
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 150 + 10]);
    }

    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $path
     * @param array|string $requestBody
     * @param array $header
     * @param array $curlOptions
     * 
     * @return string JSON
     */
    private function sendRequest($method, $path, $requestBody = null, $header = [], $curlOptions = []) {
        try {
            $curl = curl_init();
            if ($curl === false) {
                return json_encode([
                    "status" => 0,
                    "httpCode" => 500,
                    "message" => "System error",
                    "data" => null,
                    "description" => "cURL failed to initialize in BM"
                ]);
            }
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/$path");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if(!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20 + 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 90 + 10);
            foreach ($curlOptions as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                return json_encode([
                    "status" => 0,
                    "httpCode" => 500,
                    "message" => "Can't connect to Fare System",
                    "data" => null,
                    "description" => "cURL error $errorNo: $error"
                ]);
            }

            $responseArr = json_decode($response, true);
            if ($httpCode < 200 || $httpCode >= 300) {
                return json_encode([
                    "status" => 0,
                    "httpCode" => $httpCode,
                    "message" => $responseArr["message"] ?? "Failed to send request to Fare System",
                    "data" => null,
                    "description" => $responseArr
                ]);
            }

            return $response;
        }
        catch (Throwable $th) {
            $message = "Error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            return json_encode([
                "status" => 0,
                "httpCode" => 500,
                "message" => "An exception error has occurred in BM",
                "data" => null,
                "description" => $message
            ]);
        }
        finally {
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Standardize booking data for issueing ticket
     * 
     * @param array $data Data from API getBooking
     * @return array
     */
    public function standardizeBookingData($data) {
        $bookingData = [];

        $bookingData["TransactionId"]   = $data["TransactionId"] ?? "";
        $bookingData["SystemCode"]      = $data["SystemCode"] ?? "";
        $bookingData["AirlineCode"]     = $data["CarrierCode"] ?? "";
        $bookingData["AirlineName"]     = $data["CarrierName"] ?? "";
        $bookingData["BookingCode"]     = $data["BookingCode"] ?? "";
        $bookingData["BookingId"]       = $data["BookingId"] ?? "";
        $bookingData["BookingStatusId"] = $data["BookingStatusId"];
        $bookingData["BookingStatus"]   = $this->mappingBookingStatus($data["BookingStatusId"]);
        $bookingData["BookingDate"]     = date('Y-m-d H:i', strtotime($data["BookingDate"])); // 2025-09-27T10:30:40.167
        $bookingData["BookingExpired"]  = date('Y-m-d H:i', strtotime($data["BookingExpired"])); // 2025-09-27T14:31:00
        $bookingData["TicketNumber"]    = $data["TicketNumber"];
        $bookingData["TotalAmount"]     = $data["TotalAmount"] ?? 0;
        $bookingData["PaidAmount"]      = $data["PaidAmount"] ?? 0;
        $bookingData["UnPaidAmount"]    = $data["UnPaidAmount"] ?? 0;
        $bookingData["Contact"] = [
            "Title"     => $data["ContactTitle"] ?? "",
            "Name"      => $data["ContactName"] ?? "",
            "Email"     => $data["ContactEmail"] ?? "",
            "Phone"     => $data["ContactPhone"] ?? "",
            "Address"   => $data["ContactAddress"] ?? "",
        ];
        $bookingData["IsPaid"]      = $data["IsPaid"] ?? false;
        $bookingData["IsVoid"]      = $data["IsVoid"] ?? false;
        $bookingData["IsRefund"]    = $data["IsRefund"] ?? false;
        $bookingData["IsEdit"]      = $data["IsEdit"] ?? false;

        // List passenger
        $bookingData["ListPassenger"] = [];
        $passengers = $data["Customers"] ?? [];
        $baggages = $data["Baggages"] ?? [];
        foreach($passengers as $p) {
            // Get purchase baggage
            $listBaggage = [];
            foreach($baggages as $bag) {
                if($p["PersonOrgId"] == $bag["PersonOrgId"]) {
                    $listBaggage[] = [
                        "FlightId"      => $bag["FlightId"],
                        "SegmentId"     => $bag["SegmentId"],
                        "Name"          => $bag["ServiceName"],
                        "Type"          => $bag["ServiceType"],
                        "Code"          => $bag["BaggageCode"],
                        "Description"   => $bag["BaggageDescription"] ?? "",
                        "TotalAmount"   => $bag["TotalAmount"],
                    ];
                }
            }

            $bookingData["ListPassenger"][] = [
                "Id"            => $p["PersonOrgId"],
                "Type"          => $this->getPassengerTypeText($p["PassengerTypeId"]), // adt, chd, inf
                "Title"         => $p["Title"] ?? "",
                "Gender"        => $p["Gender"], // M, F
                "LastName"      => $p["LastName"],
                "FirstName"     => $p["FirstName"],
                "MiddleName"    => $p["MiddleName"] ?? "",
                "DateOfBirth"   => date('d-m-Y', strtotime($p["BirthDay"])), // Y-m-d
                "Age"           => $p["Age"],
                "Email"         => $p["Email"] ?? "",
                "Phone"         => $p["Phone"] ?? "",
                "Passport"      => $p["DocumentNo"] ?? "",
                "ParentId"      => $p["ParentGuestId"] ?? null,
                "IdConfirmed"   => $p["PersonOrgIdConfirmed"] ?? null, // Using for QH
                "ListBaggage"   => $listBaggage,
                "ListPreSeat"   => [],
                "ListService"   => [],
            ];
        }

        // List flight
        $countListFlight = 0;
        $bookingData["ListFlight"] = [];
        foreach(($data["Flights"] ?? []) as $ff) {
            $countListFlight++;
            $bookingData["ListFlight"][] = [
                "FlightId"              => $ff["FlightId"],
                "SegmentId"             => $ff["SegmentId"],
                "Origin"                => $ff["Origin"],
                "OriginName"            => $ff["OriginName"],
                "OriginCityName"        => $ff["OriginCityName"],
                "Destination"           => $ff["Destination"],
                "DestinationName"       => $ff["DestinationName"],
                "DestinationCityName"   => $ff["DestinationCityName"],
                "AirlineCode"           => $ff["CarrierCode"],
                "CarrierCode"           => $ff["OperatingCode"],
                "FlightNumber"          => $ff["FlightNumber"],
                "FlightDuration"        => $ff["FlightDuration"],
                "DepartureDate"         => $ff["DepartureDate"],
                "DepartureTime"         => $ff["DepartureTime"],
                "ArrivalDate"           => $ff["ArrivalDate"] ?? "",
                "Arrivaltime"           => $ff["Arrivaltime"],
                "CabinName"             => $ff["CabinName"],
                "FareClass"             => $ff["FareClass"],
                "AirCratf"              => $ff["AirCraftType"],
                "Terminal"              => "",
            ];
        }

        // List fare
        $bookingData["ListFare"] = [];
        foreach(($data["SumCharge"]["FareCharges"] ?? []) as $fare) {
            $farePassType = $this->getPassengerTypeText($fare["PassengerTypeId"]); // adt, chd, inf
            $bookingData["ListFare"][$farePassType] = [
                "DirectionText" => $countListFlight > 1 ? "Khứ hồi" : "",
                "Type"          => $farePassType,
                "BaseFare"      => $fare["FareBaseAmount"],
                "VAT"           => $fare["VATAmount"],
                "AirportFee"    => $fare["AirportFeesAmount"],
                "OtherFee"      => $fare["TaxAmount"],
                "Price"         => $fare["TotalAmount"]
            ];
        }

        return $bookingData;
    }

    /**
     * Standardize list baggage data for additional purchases
     * 
     * @param array $data Data from API getBaggageInfo
     * @param array $passengerInfo Info who purchase baggage
     * @param int $direction 0:Departure ; 1:Return
     * @return array
     */
    public function standardizeListBaggageData($data, $passengerInfo, $direction = 0) {
        $listBaggageData = [];
        foreach (($data[$direction]["ListService"] ?? []) as $bag) {
            if($bag["PersonOrgId"] != $passengerInfo['Id']) continue;

            $listBaggageData[] = [
                // Common properties (Using for displaying)
                "Name"          => $this->translateBaggage($bag["ServiceName"] ?? ""),
                "Description"   => $this->translateBaggage($bag["ServiceDescription"] ?? ""),
                "Amount"        => $bag["ServiceAmount"] ?? 0,
                "VAT"           => $bag["ServiceVATAmount"] ?? 0,
                "TotalAmount"   => $bag["ServiceTotalAmount"] ?? 0,
                "Origin"        => $bag["Origin"],
                "Destination"   => $bag["Destination"],
                "Value"         => [
                    "ServiceKey" => $bag["ServiceKey"],
                    "PersonOrgId" => (string)($bag["PersonOrgId"] ?? ""),
                    "PersonOrgIdConfirmed" => (string)($bag["PersonOrgIdConfirmed"] ?? ""),
                ]
            ];
        }
        return $listBaggageData;
    }
 
    /**
     * Mapping booking status
     * 
     * @param int $statusCode BookingStatusId
     * @return string
     */
    public function mappingBookingStatus($statusCode) {
        switch ($statusCode) {
            case 100:
                return 'holding';
            case 200:
                return 'cancelled';
            case 300:
                return 'completed';
            case 400:
                return 'error';
            case 320:
                return 'change-paid';
            case 350:
                return 'change-payment';
            case 330:
                return 'special';
            case 329:
                return 'manual-update';
            case 210:
                return 'trip-cancelled';
            case 304:
                return 'ticket-error';
            default:
                return 'unknown';
        }
    }

    /**
     * Check booking has ticket issued yet?
     * 
     * @param string $status
     * @return bool
     */
    public function isIssueTicket($status) {
        if(in_array($status, ['completed', 'change-paid'])) return true;
        return false;
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

    /**
     * Mapping passenger type text
     * 
     * @param int $typeNum 1:Người lớn; 6:Trẻ em; 5:Em bé
     * @return string adt, chd, inf
     */
    public function getPassengerTypeText($typeNum) {
        switch ($typeNum) {
            case 5:
                return 'inf';
            case 6:
                return 'chd';
            case 1:
            default:
                return 'adt';
        }
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

    /**
     * Translate baggage text from En to Vi
     * 
     * @param string $string English string
     * @return string Vietnamese string
     */
    public function translateBaggage($string) {
        $string = strtolower(trim($string));
        $string = str_replace("checked baggage", "Hành lý ký gửi", $string);
        $string = str_replace("Oversize piece", "Kiện quá khổ", $string);
        $string = str_replace("baggage", "Hành lý", $string);
        return $string;
    }
}