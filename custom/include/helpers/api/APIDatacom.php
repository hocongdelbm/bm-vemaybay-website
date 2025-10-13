<?php
class APIDatacom {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;
    private $API_NAME;
    private $HEADER;

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
            if ($curl === false) return json_encode(["status" => 0, "message" => "System error", "description" => "cURL failed to initialize in BM"]);
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
                    "message" => "Can't connect to API Fare System",
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $arr = is_string($json) ? json_decode($json, true) : $json;

            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => $arr['message'] ?? "Can't connect to API Fare System",
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
     * @return string Original JSON response from agency
     */
    public function book($requestBody) {
        $path = "booking/{$this->API_NAME}/book";
        $header = [
            "Content-Type: application/json",
            "API-Key: {$this->API_BOOKING_KEY}",
        ];
        return $this->sendRequest('POST', $path, json_encode($requestBody), $header, [CURLOPT_TIMEOUT => 200 + 10]);
    }

    /**
     * Get booking detail
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param string $airlineCode
     * @return string JSON
     */
    public function getBooking($bookingCode, $systemCode = '', $airlineCode = '') {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6) {
            return json_encode([
                "status" => 0,
                "message" => trim("PNR $bookingCode không hợp lệ"),
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }

        $path = "getBooking?pnr=$bookingCode";
        if(!empty($systemCode)) $path .= "&systemCode=$systemCode";
        if(!empty($airlineCode)) $path .= "&airlineCode=$airlineCode";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];

        return $this->sendRequest('GET', $path, null, $header);
    }

    /**
     * Get baggage info by booking code
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param string $bookingId
     * @return string JSON {status, message, data}
     */
    public function getBaggageInfo($bookingCode, $systemCode, $bookingId) {
        if(!$bookingCode || strlen($bookingCode) != 6
            || !$bookingId || empty($bookingId)
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
            "bookingId" => $bookingId,
            "systemCode" => $systemCode,
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header);
    }

    /**
     * Add baggage
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param string $airlineCode
     * @param array $baggageData
     * @param array $passengerData
     * 
     * @return string JSON
     */
    public function addBaggage($bookingCode, $systemCode, $airlineCode, $baggageData, $passengerData) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || empty($systemCode)
            || !is_string($airlineCode) || empty($airlineCode)
            || !is_array($baggageData) || empty($baggageData)
            || !is_array($passengerData) || empty($passengerData)
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
            "bookingCode"   => $bookingCode,
            "systemCode"    => $systemCode,
            "airlineCode"   => $airlineCode,
            "baggageData"   => $baggageData,
            "passengerData" => $passengerData
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 120 + 10]);
    }

    /**
     * Pay for booking
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
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
            "bookingCode" => $bookingCode,
            "systemCode"  => $systemCode,
            "sendEmail"   => true,
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 200 + 10]);
    }

    /**
     * Cancel booking
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param array $listSegmentId
     * 
     * @return string JSON
     */
    public function cancelBooking($bookingCode, $systemCode, $listSegmentId = []) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || empty($systemCode)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        $path = "booking/{$this->API_NAME}/cancelBooking";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode"   => $bookingCode,
            "systemCode"    => $systemCode,
            "listSegmentId" => $listSegmentId
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 120 + 10]);
    }

    /**
     * Void ticket
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param array $listTicket
     * 
     * @return string JSON
     */
    public function voidTicket($bookingCode, $systemCode, $listTicket = []) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || empty($systemCode)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        $path = "booking/{$this->API_NAME}/voidTicket";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode"   => $bookingCode,
            "systemCode"    => $systemCode,
            "listTicket"    => $listTicket
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 180 + 10]);
    }

    /**
     * Refund ticket
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @param array $listTicket
     * 
     * @return string JSON
     */
    public function refundTicket($bookingCode, $systemCode, $listTicket = []) {
        if(!is_string($bookingCode) || strlen($bookingCode) != 6 
            || !is_string($systemCode) || empty($systemCode)
        ) {
            return json_encode([
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "data" => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
        $path = "booking/{$this->API_NAME}/refundTicket";
        $header = [
            "Content-Type: application/json",
            "API-Key: $this->API_BOOKING_KEY"
        ];
        $requestBody = json_encode([
            "bookingCode"   => $bookingCode,
            "systemCode"    => $systemCode,
            "listTicket"    => $listTicket
        ]);

        return $this->sendRequest('POST', $path, $requestBody, $header, [CURLOPT_TIMEOUT => 180 + 10]);
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
                LoggerHelper::error("{$method} {$this->ENDPOINT}/{$path} cURL failed to initialize");
                return json_encode([
                    "status" => 0,
                    "httpCode" => 500,
                    "message" => "System error",
                    "data" => null,
                    "description" => "cURL failed to initialize in BM"
                ]);
            }
            curl_setopt($curl, CURLOPT_URL, "{$this->ENDPOINT}/{$path}");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if(!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 100);
            foreach ($curlOptions as $key => $value) {
                curl_setopt($curl, $key, $value);
            }
            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                LoggerHelper::error("{$method} {$this->ENDPOINT}/{$path} cURL error $errorNo: $error");
                return json_encode([
                    "status" => 0,
                    "httpCode" => 500,
                    "message" => "Can't connect to Fare System",
                    "data" => null,
                    "description" => "cURL error $errorNo: $error"
                ]);
            }

            $responseArr = json_decode($response, true);

            LoggerHelper::info("{$method} {$this->ENDPOINT}/{$path} $httpCode", [
                'request' => is_array($requestBody) ? $requestBody : (json_decode($requestBody, true) ?? $requestBody),
                'reponse' => $responseArr ?? $response
            ]);

            if ($httpCode < 200 || $httpCode >= 300) {
                return json_encode([
                    "status" => 0,
                    "httpCode" => $httpCode,
                    "message" => $responseArr["message"] ?? "Error $httpCode: Failed to handle request",
                    "data" => null,
                    "description" => $responseArr
                ]);
            }

            return $response;
        }
        catch (Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            LoggerHelper::error("{$method} {$this->ENDPOINT}/{$path} $message");
            return json_encode([
                "status" => 0,
                "httpCode" => 500,
                "message" => "An exception error has occurred",
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

        // BookingStatus: OK, TICKETED, CANCELED

        $bookingData["SystemCode"]  = $data["System"] ?? "";
        $bookingData["AirlineCode"] = $data["Airline"] ?? "";
        $bookingData["BookingCode"] = $data["GdsCode"] ?? "";
        if(empty($bookingData["BookingCode"])) $bookingData["BookingCode"] = $data["BookingCode"] ?? "";
        $bookingData["BookingId"]       = $data["BookingId"] ?? "";
        $bookingData["BookingStatusId"] = null;
        $bookingData["BookingStatus"]   = $this->mappingBookingStatus($data["BookingStatus"]);
        $bookingData["BookingDate"]     = $this->convertDatetime($data["TimePurchase"], 'Y-m-d H:i'); // dmY Hi
        $bookingData["BookingExpired"]  = $this->convertDatetime($data["ExpirationTime"], 'Y-m-d H:i'); // dmY Hi
        $bookingData["TotalAmount"]     = $data["TotalPrice"] ?? 0;
        $bookingData["PaidAmount"]      = $data["PaidAmount"] ?? 0;
        $bookingData["UnPaidAmount"]    = $bookingData["TotalAmount"] > 0 ? $bookingData["TotalAmount"] - $bookingData["PaidAmount"] : 0;

        $guestContactArea = $data["GuestContact"]["Area"] ?? "";
        $guestContactPhone = $data["GuestContact"]["Phone"] ?? "";
        $bookingData["Contact"] = [
            "Title"     => $data["GuestContact"]["Title"] ?? "",
            "Name"      => $data["GuestContact"]["Name"] ?? "",
            "Email"     => $data["GuestContact"]["Email"] ?? "",
            "Phone"     => strpos($guestContactPhone, $guestContactArea) !== false ? $guestContactPhone : ($guestContactArea . $guestContactPhone),
            "Address"   => $data["GuestContact"]["Address"] ?? "",
        ];

        // Recheck booking status timeout
        if($bookingData["BookingStatus"] == 'holding' && isset($bookingData["BookingExpired"]) && !empty($bookingData["BookingExpired"]) && strtotime($bookingData["BookingExpired"]) < time()) {
            $bookingData["BookingStatus"] = "timeout";
        }

        // Update action for booking
        $bookingData["IsPaid"] = $bookingData["IsVoid"] = $bookingData["IsRefund"] = false;
        if($bookingData["BookingStatus"] == "completed") {
            $bookingData["IsPaid"] = true;
            $bookingData["IsVoid"] = in_array($bookingData["SystemCode"], ["VN", "1A", "1G"]) ? true : false;
            if($bookingData["IsVoid"] && !empty($data["TimePurchase"]) && !is_null($data["TimePurchase"]) && $this->convertDatetime($data["TimePurchase"], 'Y-m-d') != date('Y-m-d')) {
                $bookingData["IsVoid"] = false;
            }
            $bookingData["IsRefund"] = true;
        }

        // List flight and fare
        $flightNumberList = [];
        $bookingData["ListFlight"] = [];
        $bookingData["ListFare"] = [];
        foreach(($data["ListFlightFare"] ?? []) as $ff) {
            $fareInfo = $ff["FareInfo"] ?? [];
            $cabin = ucwords(strtolower($fareInfo["CabinName"] ?? ""));

            foreach($fareInfo['ListFarePax'] as $fare) {
                $farePassType = strtolower($fare["PaxType"] ?? ''); // adt, chd, inf
                $fareBase = $fare["BaseFare"] ?? 0;
                $price = $fare["TotalFare"] ?? 0; 

                $vat = $airportFee = $otherFee = 0;
                foreach($fare["ListFareItem"] as $item) {
                    if($item["Code"] == "TICKET_VAT") $vat = $item["Amount"] ?? 0;
                    elseif($item["Code"] == "TICKET_TAX" && stripos($item["Name"], "Airport") !== false) $airportFee += $item["Amount"] ?? 0;
                    elseif($item["Code"] != "TICKET_FARE") $otherFee += $item["Amount"] ?? 0;
                }

                if(!isset($bookingData["ListFare"][$farePassType])) {
                    $directionText = "";
                    if(count($data["ListFlightFare"]) == 1) {
                        $directionText = trim(count($ff["ListFlight"] ?? []) . " chặng");
                    }
                    else {
                        $directionText = $ff["Leg"] == 1 ? "Lượt về" : "Lượt đi";
                    }

                    $bookingData["ListFare"][(string)$ff["Leg"] . $farePassType] = [
                        "DirectionText" => $directionText,
                        "Type"          => $farePassType,
                        "BaseFare"      => $fareBase,
                        "VAT"           => $vat,
                        "AirportFee"    => $airportFee,
                        "OtherFee"      => $otherFee,
                        "Price"         => $price
                    ];
                }
            }

            foreach(($ff["ListFlight"] ?? []) as $i => $flight) {
                $flightNumberList[$flight["StartPoint"].$flight["EndPoint"]] = $flight["FlightNumber"];
                $bookingData["ListFlight"][] = [
                    "FlightId"              => $flight["FlightId"],
                    "Origin"                => $flight["StartPoint"],
                    "OriginName"            => $flight["OriginName"] ?? "",
                    "OriginCityName"        => $flight["OriginCityName"] ?? "",
                    "Destination"           => $flight["EndPoint"],
                    "DestinationName"       => $flight["DestinationName"] ?? "",
                    "DestinationCityName"   => $flight["DestinationCityName"] ?? "",
                    "AirlineCode"           => $flight["Airline"],
                    "CarrierCode"           => $flight["Operator"],
                    "FlightNumber"          => $flight["FlightNumber"],
                    "FlightDuration"        => Flight::getNiceDuration($flight["Duration"] * 60),
                    "DepartureDate"         => date("Y-m-d", strtotime($flight["StartDate"])),
                    "DepartureTime"         => date("H:i", strtotime($flight["StartDate"])),
                    "ArrivalDate"           => date("Y-m-d", strtotime($flight["EndDate"])),
                    "Arrivaltime"           => date("H:i", strtotime($flight["EndDate"])),
                    "CabinName"             => $cabin,
                    "FareClass"             => trim(explode(",", $fareInfo["FareClass"])[$i] ?? $flight["ListSegment"][0]["FareClass"] ?? ""),
                    "AirCratf"              => $flight["ListSegment"][0]["Equipment"] ?? "",
                    "Terminal"              => $flight["ListSegment"][0]["StartTerminal"] ?? "",
                ];
            }
        }
        if(empty($bookingData["ListFare"])) $bookingData["ListFare"] = $ff["FareInfo"];

        // List passenger
        $bookingData["ListPassenger"] = [];
        foreach (($data["ListPassenger"] ?? []) as $p) {
            // Format list baggage
            $listBaggage = [];
            $listValueBaggage = []; 
            foreach($p["ListBaggage"] as $bag) {
                // Value to display
                $listBaggage[] = [
                    "FlightId"      => $bag["Leg"] + 1,
                    "SegmentId"     => null,
                    "Name"          => $this->translateBaggage($bag["Name"]),
                    "Type"          => $bag["Type"],
                    "Code"          => "",
                    "Description"   => $this->translateBaggage($bag["Description"] ?? ""),
                    "TotalAmount"   => $bag["Price"],
                ];

                // Value to use in API
                $bag["FlightNumber"] = $flightNumberList[$bag["StartPoint"].$bag["EndPoint"]] ?? '';
                $listValueBaggage[] = $bag;
            }
            $p["ListBaggage"] = $listValueBaggage;

            $bookingData["ListPassenger"][] = [
                "Id"            => (int)$p["NameId"],
                "Type"          => strtolower($p["Type"]), // adt, chd, inf
                "Title"         => $p["Title"] ?? "",
                "Gender"        => $this->getGenderTypeText($p["Gender"]), // M, F
                "LastName"      => $p["Surname"],
                "FirstName"     => $p["GivenName"],
                "MiddleName"    => "",
                "DateOfBirth"   => $this->convertDate($p["DateOfBirth"], 'Y-m-d'), // dmY
                "Age"           => $this->getAge($p["DateOfBirth"]),
                "Email"         => "",
                "Phone"         => "",
                "Passport"      => $p["Passport"],
                "ParentId"      => $p["ParentId"],
                "IdConfirmed"   => null,
                "ListBaggage"   => $listBaggage,
                "ListPreSeat"   => $p["ListPreSeat"],
                "ListService"   => $p["ListService"],
                "Value"         => $p // This is an attribute is used in API
            ];
        }

        // List ticket
        $bookingData["ListTicket"] = [];
        foreach (($data["ListTicket"] ?? []) as $tk) {
            $bookingData["ListTicket"][] = [
                "TicketNumber"  => $tk["TicketNumber"] ?? "",
                "TicketStatus"  => $tk["TicketStatus"] ?? "",
                "ServiceType"   => $tk["ServiceType"] ?? "",
                "Description"   => trim(($tk["FullName"] ?? "") . " " . ($tk["Remark"] ?? "")),
                "TotalAmount"   => $tk["Total"] ?? 0,
                "PassengerId"   => $tk["NameId"] ?? null,
                "Flight"        => $tk["ServiceType"] != "FLIGHT" ? $tk["StartPoint"] . "-" . $tk["EndPoint"] : "",
                "IssueDate"     => $tk["IssueDate"] // 2025-10-08T00:00:00
            ];
        }

        return $bookingData;
    }

    /**
     * Standardize list baggage data for additional purchases
     * 
     * @param array $data Data from API getBaggageInfo
     * @param string $origin Origin code
     * @param string $destination Destination code
     * @param string $flightNumber
     * @return array
     */
    public function standardizeListBaggageData($data, $origin, $destination, $flightNumber) {
        $listBaggageData = [];
        foreach (($data["ListBaggage"] ?? []) as $bag) {
            if($bag["StartPoint"] == $origin && $bag["EndPoint"] == $destination) {
                $bag["FlightNumber"] = $flightNumber;
                $listBaggageData[] = [
                    // Common properties (Using for displaying)
                    "Name"          => trim($bag["Name"]),
                    "Description"   => trim($bag["Description"]),
                    "Amount"        => $bag["Price"],
                    "VAT"           => 0,
                    "TotalAmount"   => $bag["Price"],
                    "Origin"        => $bag["StartPoint"],
                    "Destination"   => $bag["EndPoint"],
                    "Value"         => $bag // Use for purchasing
                ];
            }
        }
        return $listBaggageData;
    }

    /**
     * Mapping booking status
     * 
     * @param string $statusCode BookingStatus
     * @return string
     */
    public function mappingBookingStatus($statusCode) {
        switch (strtoupper($statusCode)) {
            case 'OK':
                return 'holding';
            case 'TICKETED':
                return 'completed';
            case 'CANCELED':
                return 'cancelled';
            default:
                return 'unknown';
        }
    }

    /**
     * Get gender type text
     * 
     * @param int $genderNum 0, 1
     * @param string F, M
     */
    public function getGenderTypeText($genderNum) {
        $arr = [0 => 'F', 1 => 'M'];
        return $arr[$genderNum] ?? 'Unknown';
    }

    /**
     * Get age by birthdate
     * 
     * @param string $birthdate
     * @return int
     */
    public function getAge($birthdate) {
        $birthDate = new DateTime($this->convertDate($birthdate)); // Create a DateTime object for the birthdate
        $currentDate = new DateTime(); // Current date and time
        $age = $currentDate->diff($birthDate); // Difference between current date and birthdate
        return $age->y; // Return the age in years
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

    /**
     * Translate baggage text from En to Vi
     * 
     * @param string $string English string
     * @return string Vietnamese string
     */
    public function translateBaggage($string) {
        $string = strtoupper(trim($string));
        $string = str_replace("PREPAID BAG", "Hành lý trả trước", $string);
        $string = str_replace("UPTO", "tối đa ", $string);
        return $string;
    }
}