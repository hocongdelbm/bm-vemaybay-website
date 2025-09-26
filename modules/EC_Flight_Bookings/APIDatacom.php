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
            if ($curl === false) return json_encode([
                "status" => 0,
                "message" => "System error",
                "data" => null,
                "description" => "cURL failed to initialize in BM"
            ]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 210);
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API Fare System",
                    "data" => null,
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            return $response;
        }
        catch(Exception $e) {
            return json_encode([
                "status" => 0,
                "message" => "{$e->getCode()}: {$e->getMessage()}",
                "data" => null
            ]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    /**
     * Pay for booking
     * 
     * @param string $bookingCode PNR
     * @param string $systemCode
     * @return string JSON {status, message, data}
     */
    public function payBooking($bookingCode, $systemCode) {
        try {
            if(!$bookingCode || !$systemCode || empty($bookingCode) || empty($systemCode)) {
                return json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "data" => null
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $requestBody = [
                "System" => $systemCode,
                "BookingCode" => $bookingCode,
                "SendEmail" => true,
                "Session" => ""
            ];

            $curl = curl_init();
            if ($curl === false) return json_encode([
                "status" => 0,
                "message" => "System error",
                "data" => null,
                "description" => "cURL failed to initialize in BM",
            ]);
            curl_setopt($curl, CURLOPT_URL,  "$this->ENDPOINT/booking/$this->API_NAME/payBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 16);
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API Fare System",
                    "data" => null,
                    "description" => "cURL error $errorno: $error"
                ]);
            }
            
            return $response;
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
     * @param string $bookingCode PNR
     * @param string $systemCode VJ, VN, 1A, 1G,...
     * @param string $airlineCode VJ, VN, PG,...
     * @return string JSON {status, message, data}
     */
    public function getBooking($bookingCode, $systemCode = '', $airlineCode = '') {
        try {
            if(!is_string($bookingCode) || strlen($bookingCode) != 6) {
                return json_encode([
                    "status" => 0,
                    "message" => trim("Invalid booking code $bookingCode"),
                    "data" => null
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];

            $url = "$this->ENDPOINT/getBooking?pnr=$bookingCode";
            if(!empty($systemCode)) $url .= "&systemCode=$systemCode";
            if(!empty($airlineCode)) $url .= "&airlineCode=$airlineCode";

            $curl = curl_init();
            if ($curl === false) return json_encode([
                "status" => 0,
                "message" => "System error",
                "data" => null,
                "description" => "cURL failed to initialize in BM"
            ]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
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
                    "message" => "Can't connect to API Fare System",
                    "data" => null,
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            $responseArr = json_decode($response, true);
            if($httpcode != 200) {
                return json_encode([
                    "status" => 0,
                    "message" => $responseArr["message"] ?? "Getting booking failed",
                    "data" => $responseArr,
                    "description" => "HTTP error $httpcode"
                ]);
            }
            return $response;
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
     * @param string $bookingCode PNR
     * @param string $bookingId
     * @return string JSON {status, message, data}
     */
    public function getBaggageInfo($bookingCode, $bookingId) {
        try {
            if(!$bookingCode || !$bookingId || empty($bookingCode) || empty($bookingId)) {
                return json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "data" => null
                ]);
            }

            $headers = [
                "Content-Type: application/json",
                "API-Key: $this->API_BOOKING_KEY"
            ];
            $requestBody = [
                "BookingInfo" => [
                    "BookingCode" => $bookingCode,
                    "BookingId" => $bookingId
                ]
            ];

            $curl = curl_init();
            if($curl === false) return json_encode([
                "status" => 0,
                "message" => "System error",
                "data" => null,
                "description" => "cURL failed to initialize in BM"
            ]);
            curl_setopt($curl, CURLOPT_URL, "$this->ENDPOINT/booking/$this->API_NAME/payBooking");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 24);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 70);
            $response = curl_exec($curl); // JSON
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response === false || $errorno) {
                return json_encode([
                    "status" => 0,
                    "message" => "Can't connect to API Fare System",
                    "data" => null,
                    "description" => "cURL error $errorno: $error"
                ]);
            }

            return $response;
        }
        catch(Exception $e) {
            return json_encode([
                "status" => 0,
                "message" => "{$e->getCode()}: {$e->getMessage()}",
                "data" => null
            ]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
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

        // BookingStatus: OK, TICKETED

        $bookingData["SystemCode"]  = $data["System"] ?? "";
        $bookingData["AirlineCode"] = $data["Airline"] ?? "";
        $bookingData["BookingCode"] = $data["GdsCode"] ?? "";
        if(empty($bookingData["BookingCode"])) $bookingData["BookingCode"] = $data["BookingCode"] ?? "";
        $bookingData["BookingId"]       = $data["BookingId"] ?? "";
        $bookingData["BookingStatusId"] = 100; // PHUONGNAM
        $bookingData["BookingDate"]     = $this->convertDatetime($data["TimePurchase"]);
        $bookingData["BookingExpired"]  = $this->convertDatetime($data["ExpirationTime"]);
        $bookingData["TotalAmount"]     = $data["TotalPrice"] ?? 0;
        $bookingData["PaidAmount"]      = $data["PaidAmount"] ?? 0;
        $bookingData["UnPaidAmount"]    = $bookingData["TotalAmount"] - $bookingData["PaidAmount"];
        $bookingData["Contact"] = [
            "Title"     => $data["GuestContact"]["Title"] ?? "",
            "Name"      => $data["GuestContact"]["Name"] ?? "",
            "Email"     => $data["GuestContact"]["Email"] ?? "",
            "Phone"     => ($data["GuestContact"]["Area"] ?? "") . ($data["GuestContact"]["Phone"] ?? ""),
            "Address"   => $data["GuestContact"]["Address"] ?? "",
        ];
        $bookingData["IsPaid"] = false;
        $bookingData["IsVoid"] = false;
        $bookingData["IsRefund"] = false;
        $bookingData["IsEdit"] = false;

        // List passenger
        $bookingData["ListPassenger"] = [];
        $passengers = $data["ListPassenger"] ?? [];
        foreach ($passengers as $p) {
            $bookingData["ListPassenger"][] = [
                "Id"            => $p["Index"],
                "Type"          => strtolower($p["Type"]), // adt, chd, inf
                "Title"         => $p["Title"] ?? "",
                "Gender"        => $this->getGenderTypeText($p["Gender"]), // M, F
                "LastName"      => $p["Surname"],
                "FirstName"     => $p["GivenName"],
                "MiddleName"    => "",
                "DateOfBirth"   => $p["DateOfBirth"], // d-m-Y
                "Age"           => $this->getAge($p["DateOfBirth"]),
                "Email"         => "",
                "Phone"         => "",
                "Passport"      => $p["Passport"],
                "ParentId"      => $p["ParentId"],
                "IdConfirmed"   => "",
                "ListBaggage"   => $p["ListBaggage"],
                "ListPreSeat"   => $p["ListPreSeat"],
                "ListService"   => $p["ListService"],
            ];
        }

        // List flight and fare
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
                    $bookingData["ListFare"][(string)$ff["Leg"] . $farePassType] = [
                        "DirectionText" => count($data["ListFlightFare"]) > 1 ? ($ff["Leg"] == 1 ? "Lượt về" : "Lượt đi") : "",
                        "Type"          => $farePassType,
                        "BaseFare"      => $fareBase,
                        "VAT"           => $vat,
                        "AirportFee"    => $airportFee,
                        "OtherFee"      => $otherFee,
                        "Price"         => $price
                    ];
                }
                // else {
                //     $bookingData["ListFare"][$farePassType]["BaseFare"] += $fareBase;
                //     $bookingData["ListFare"][$farePassType]["VAT"] += $vat;
                //     $bookingData["ListFare"][$farePassType]["AirportFee"] += $airportFee;
                //     $bookingData["ListFare"][$farePassType]["OtherFee"] += $otherFee;
                //     $bookingData["ListFare"][$farePassType]["Price"] += $price;
                // }
            }

            foreach(($ff["ListFlight"] ?? []) as $flight) {
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
                    "FlightDuration"        => $flight["Duration"] * 60,
                    "DepartureDate"         => date("d-m-Y", strtotime($flight["StartDate"])),
                    "DepartureTime"         => date("H:i", strtotime($flight["StartDate"])),
                    "ArrivalDate"           => date("d-m-Y", strtotime($flight["EndDate"])),
                    "Arrivaltime"           => date("H:i", strtotime($flight["EndDate"])),
                    "CabinName"             => $cabin,
                    "FareClass"             => $flight["ListSegment"][0]["FareClass"] ?? "",
                    "AirCratf"              => $flight["ListSegment"][0]["Equipment"] ?? "",
                    "Terminal"              => $flight["ListSegment"][0]["StartTerminal"] ?? "",
                ];
            }
        }
        if(empty($bookingData["ListFare"])) $bookingData["ListFare"] = $ff["FareInfo"];

        return $bookingData;
    }

    /**
     * Standardize list baggage data for additional purchases
     * 
     * @param array $data Data from API getBaggageInfo
     * @param int $direction 0:Departure ; 1:Return
     * @return array
     */
    public function standardizeListBaggageData($data, $direction = 0) {
        $listBaggageData = [];
        foreach (($data["ListBaggage"] ?? []) as $bag) {
            if($bag["Leg"] == $direction) {
                $listBaggageData[] = [
                    // Common properties (Using for displaying)
                    "Name"          => $bag["Name"],
                    "Description"   => $bag["Description"],
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
        $birthDate = new DateTime($birthdate); // Create a DateTime object for the birthdate
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
}