<?php
class APIDatacom {
    private $ENDPOINT;
    private $API_SEARCH_KEY;
    private $API_BOOKING_KEY;

    public function __construct() {
        global $sugar_config;
        $this->ENDPOINT = $sugar_config['api_autobook']['Endpoint'] ?? '';
        $this->API_SEARCH_KEY = $sugar_config['api_autobook']['SearchKey'] ?? '';
        $this->API_BOOKING_KEY = $sugar_config['api_autobook']['BookingKey'] ?? '';
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
                    'api' => 'DATACOM',
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
            return json_encode($arr);
        }
        catch(Exception $e) {
            return json_encode(["status" => 0, "message" => "{$e->getCode()}: {$e->getMessage()}", "data" => null]);
        }
        finally {
            if(isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }

    public function booking() {
        // {
        //     "RequestInfo": {
        //         "PrivateKey": "string",
        //         "ApiAccount": "string",
        //         "ApiPassword": "string",
        //         "UserToken": "string",
        //         "Currency": "string",
        //         "Language": "string",
        //         "IpAddress": "string"
        //     },
        //     "Forced": true,
        //     "AgentId": "string",
        //     "System": "string",
        //     "Channel": "string",
        //     "RequestKey": "string",
        //     "GuestContact": {
        //         "Title": "string",
        //         "Name": "string",
        //         "Area": "string",
        //         "Phone": "string",
        //         "Email": "string",
        //         "Address": "string",
        //         "Remark": "string",
        //         "Language": "string",
        //         "ReceiveEmail": true
        //     },
        //     "AgentContact": {
        //         "Title": "string",
        //         "Name": "string",
        //         "Area": "string",
        //         "Phone": "string",
        //         "Email": "string",
        //         "Address": "string",
        //         "Remark": "string",
        //         "Language": "string",
        //         "ReceiveEmail": true
        //     },
        //     "ListPassenger": [
        //         {
        //         "Index": 0,
        //         "ParentId": 0,
        //         "NameId": "string",
        //         "Type": "string",
        //         "Title": "string",
        //         "Gender": 0,
        //         "GivenName": "string",
        //         "Surname": "string",
        //         "DateOfBirth": "string",
        //         "PassengerId": "string",
        //         "Passport": {
        //             "Index": "string",
        //             "DocumentType": "string",
        //             "DocumentCode": "string",
        //             "DocumentExpiry": "string",
        //             "Nationality": "string",
        //             "IssueCountry": "string"
        //         },
        //         "ListBaggage": [
        //             {
        //             "System": "string",
        //             "Airline": "string",
        //             "Value": "string",
        //             "Type": "string",
        //             "PaxType": "string",
        //             "Name": "string",
        //             "Description": "string",
        //             "Price": 0,
        //             "Currency": "string",
        //             "Leg": 0,
        //             "StartPoint": "string",
        //             "EndPoint": "string",
        //             "FlightNumber": "string",
        //             "StatusCode": "string",
        //             "Confirmed": true,
        //             "Session": "string"
        //             }
        //         ],
        //         "ListPreSeat": [
        //             {
        //             "System": "string",
        //             "Airline": "string",
        //             "Value": "string",
        //             "Type": "string",
        //             "PaxType": "string",
        //             "Name": "string",
        //             "Description": "string",
        //             "Price": 0,
        //             "Currency": "string",
        //             "Leg": 0,
        //             "StartPoint": "string",
        //             "EndPoint": "string",
        //             "FlightNumber": "string",
        //             "StatusCode": "string",
        //             "Confirmed": true,
        //             "Session": "string"
        //             }
        //         ],
        //         "ListService": [
        //             {
        //             "System": "string",
        //             "Airline": "string",
        //             "Value": "string",
        //             "Type": "string",
        //             "PaxType": "string",
        //             "Name": "string",
        //             "Description": "string",
        //             "Price": 0,
        //             "Currency": "string",
        //             "Leg": 0,
        //             "StartPoint": "string",
        //             "EndPoint": "string",
        //             "FlightNumber": "string",
        //             "StatusCode": "string",
        //             "Confirmed": true,
        //             "Session": "string"
        //             }
        //         ],
        //         "ListFareInfo": [
        //             {
        //             "Code": "string",
        //             "Amount": 0,
        //             "Currency": "string"
        //             }
        //         ],
        //         "ListMembership": [
        //             {
        //             "Index": "string",
        //             "Airline": "string",
        //             "MembershipID": "string",
        //             "MembershipType": "string"
        //             }
        //         ]
        //         }
        //     ],
        //     "ListAirOption": [
        //         {
        //         "Session": "string",
        //         "SessionType": "string",
        //         "AirlineOptionId": 0,
        //         "FareOptionId": 0,
        //         "FlightOptionId": 0,
        //         "Tourcode": "string",
        //         "CAcode": "string",
        //         "VIPText": "string",
        //         "Remark": "string",
        //         "AccountCode": "string",
        //         "BookerCode": "string"
        //         }
        //     ],
        //     "Option": {
        //         "IssueTicket": true,
        //         "SeparateBooking": true,
        //         "SendEmail": true,
        //         "AgentId": "string",
        //         "MemberId": "string",
        //         "RefId": "string"
        //     },
        //     "Payment": {
        //         "PaymentMethod": "string",
        //         "PaymentGateway": "string"
        //     },
        //     "Invoice": {
        //         "CompanyName": "string",
        //         "CompanyCity": "string",
        //         "CompanyCountry": "string",
        //         "CompanyAddress": "string",
        //         "CompanyPostCode": "string",
        //         "CompanyTaxCode": "string",
        //         "ReceiverName": "string",
        //         "ReceiverPhone": "string",
        //         "ReceiverEmail": "string",
        //         "ReceiverAddress": "string",
        //         "Remark": "string"
        //     },
        //     "ServiceFee": {
        //         "FeeAdt": 0,
        //         "FeeChd": 0,
        //         "FeeInf": 0,
        //         "Currency": "string"
        //     },
        //     "ListESim": [
        //         {
        //         "ProductId": "string",
        //         "Name": "string",
        //         "Price": 0,
        //         "Quantity": 0,
        //         "StartUsingDate": "2025-09-18T10:02:58.488Z",
        //         "Currency": "string",
        //         "Country": "string",
        //         "Journey": "string"
        //         }
        //     ]
        // }
    }
}