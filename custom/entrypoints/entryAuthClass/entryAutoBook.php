<?php
class entryAutoBookClass extends entryClass {
    public $mappingSystemCodeName;
    public $arrMapAirlineCode;

    public function __construct() {
        global $sugar_config;
        $this->mappingSystemCodeName = [
            'VJ' => 'Vietjet Air',
            'VN' => 'Vietnam Airlines',
            'QH' => 'Bamboo Airways',
            'VU' => 'Vietravel Airlines'
        ];
        $this->arrMapAirlineCode = [
            'VJA' => 'VJ',
            'VNA' => 'VN',
            'VNP' => 'VN',
            'BBA' => 'QH',
            'VTA' => 'VU'
        ];
    }

    public function getDataAutoBook($params = []) {
        $bookingId          = $params['bookingId'] ?? '';
        $listItineraryId    = $params['listItineraryId'] ?? []; // ec_booking_itineraries
        $listPassengerId    = $params['listPassengerId'] ?? []; // ec_booking_passengers
        $listDetailId       = $params['listDetailId'] ?? []; // ec_booking_details

        if(!empty($bookingId) && !empty($listItineraryId) && !empty($listPassengerId) && !empty($listDetailId)) {
            global $db;

            // Thông tin hành trình
            $dataItineraries = [];
            $stt_dep = $stt_ret = 0;
            $sql_1 = "SELECT iti.id
                    ,iti.departure
                    ,iti.arrival
                    ,iti.departure_date AS departure_date
                    ,iti.base_price
                    ,iti.ticket_class
                    ,iti.direction
                    ,iti.airline_code
                    ,iti.flight_number
                FROM ec_booking_itineraries iti
                WHERE iti.booking_id = '$bookingId'
                    AND iti.deleted = 0
                    AND iti.add_type = 0
                ORDER BY iti.direction, iti.date_entered, iti.departure_date";
            $res_1 = $db->query($sql_1);
            while ($row = $db->fetchByAssoc($res_1)) {
                // Lượt đi
                if ($row['direction'] == '0') {
                    if ($stt_dep == 0 && in_array($row['id'], $listItineraryId)) {
                        $within24h = 0;
                        if(strtotime($row['departure_date']) - time() < 86400) $within24h = 1;
                        $flightDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                        $dataItineraries['dep'] = [
                            'id'            => $row['id'],
                            'depCode' 	    => $row['departure'],
                            'desCode'	    => $row['arrival'],
                            'flightDate'  	=> $flightDate,
                            'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                            'flightNo'	    => $row['flight_number'],
                            'ticketClass'   => $row['ticket_class'],
                            'within24h'     => $within24h
                        ];
                    } elseif(isset($dataItineraries['dep'])) $dataItineraries['dep']['desCode'] = $row['arrival'];
                    $stt_dep++;
                }

                // Lượt về
                if ($row['direction'] == '1') {
                    if ($stt_ret == 0 && in_array($row['id'], $listItineraryId)) {
                        $within24h = 0;
                        if(strtotime($row['departure_date']) - time() < 86400) $within24h = 1;
                        $flightDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                        $dataItineraries['ret'] = [
                            'id'            => $row['id'],
                            'depCode' 	    => $row['departure'],
                            'desCode'	    => $row['arrival'],
                            'flightDate'  	=> $flightDate,
                            'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                            'flightNo'	    => $row['flight_number'],
                            'ticketClass'	=> $row['ticket_class'],
                            'within24h'     => $within24h
                        ];
                    } elseif(isset($dataItineraries['ret'])) $dataItineraries['ret']['desCode'] = $row['arrival'];
                    $stt_ret++;
                }
            }

            if(isset($dataItineraries['dep']) && isset($dataItineraries['ret'])
                && $dataItineraries['dep']['airlineCode'] != $dataItineraries['ret']['airlineCode']
                && ($dataItineraries['dep']['airlineCode'] == 'QH' || $dataItineraries['ret']['airlineCode'] == 'QH')
            ) {
                return ["status" => 0, "message" => "Hãng Bamboo phải autobook riêng"];
            }

            // Thông tin hành khách
            $adtCount = $chdCount = $infCount = 0;
            $dataPassengers = [];
            $sql_2 = "SELECT p.id
                    ,p.type
                    ,p.salutation
                    ,p.name
                    ,p.birthday
                    ,p.cic
                    ,p.passport_number
                FROM ec_booking_passengers p
                WHERE p.booking_id = '$bookingId' AND p.deleted = 0
                ORDER BY p.type";
            $res_2 = $db->query($sql_2);
            while ($row = $db->fetchByAssoc($res_2)) {
                if(in_array($row['id'], $listPassengerId)) {
                    if($row['type'] === '0') $adtCount++;
                    elseif($row['type'] === '1') $chdCount++;
                    elseif($row['type'] === '2') $infCount++;

                    $dataPassengers[$row['id']] = [
                        'id' => $row['id'],
                        'type' => $row['type'], // 0:Adt ; 1:Chd ; 2:Inf
                        'salutation' => $row['salutation'] == 0 ? 'Mr' : 'Ms', // 0:Mr ; 1:Ms
                        'name'=> $row['name'],
                        'birthday' => !is_null($row['birthday']) && !empty($row['birthday']) ? date('d-m-Y', strtotime($row['birthday'])) : '',
                        'cic' => $row['cic'] ?? '',
                        'passportNumber' => $row['passport_number'] ?? ''
                    ];
                }
            }

            // Thông tin chi tiết vé
            $dataFareDetails = [];
            $sql_3 = "SELECT bkd.id
                    ,bkd.direction
                    ,bkd.passenger_type
                    ,bkd.quantity
                    ,bkd.unit_price AS fare
                    ,bkd.tax_and_fee AS tax
                    ,(IFNULL(bkd.airport_fee, 0) + IFNULL(bkd.admin_fee, 0)) AS fee
                FROM ec_booking_details bkd
                WHERE bkd.booking_id = '$bookingId' AND bkd.deleted = 0
                ORDER BY bkd.direction, bkd.passenger_type, bkd.date_entered";
            $res_3 = $db->query($sql_3);
            while ($row = $db->fetchByAssoc($res_3)) {
                if(in_array($row['id'], $listDetailId)) {
                    if($row['passenger_type'] === '0' && $adtCount < 1) continue;
                    elseif($row['passenger_type'] === '1' && $chdCount < 1) continue;
                    elseif($row['passenger_type'] === '2' && $infCount < 1) continue;

                    $direction_name = $row['direction'] == '1' ? 'ret' : 'dep';
                    $dataFareDetails[$direction_name][$row['passenger_type']] = [
                        'id' => $row['id'],
                        'fare'  => $row['fare'],
                        'tax'   => $row['tax'],
                        'fee'   => $row['fee'],
                        'price' => $row['fare'] + $row['tax'] + $row['fee'],
                        'qty'   => $row['quantity'],
                        'fareFormat'  => format_number($row['fare']),
                        'taxFormat'   => format_number($row['tax']),
                        'feeFormat'   => format_number($row['fee']),
                        'priceFormat' => format_number($row['fare'] + $row['tax'] + $row['fee']),
                    ];
                }
            }

            // Thông tin liên hệ
            $dataContact = []; 
            $booking = new EC_Flight_Bookings();
            $booking->retrieve($bookingId);
            $dataContact['name'] = $booking->contact_name;
            $dataContact['email'] = $booking->email_reservation;
            $dataContact['phone'] = trim($booking->phone);
            $dataContact['title'] = $booking->contact_title == '0' ? 'Mr.' : 'Ms.';
            $dataContact['address'] = !empty($booking->address) ? trim($booking->address) : '48/8 Lam Sơn, P. Gia Định';
            
            return [
                'status' => 1,
                'message' => 'Success',
                'data' => [
                    'contact' => $dataContact,
                    'itineraries' => $dataItineraries,
                    'passengers' => $dataPassengers,
                    'fareDetails' => $dataFareDetails
                ]
            ];
        }

        return [
            "status" => 0,
            "message" => "Dữ liệu không hợp lệ",
            "params" => [
                "bookingId" => $bookingId,
                "listItineraryId" => $listItineraryId,
                "listPassengerId" => $listPassengerId,
                "listDetailId" => $listDetailId,
            ]
        ];
    }

    public function getBooking() {

    }

    public function getBaggageInfo() {

    }

    public function research($params = []) {
        $agencyName     = $params['agency'] ?? '';
        $flightNo       = $params['flightNo'] ?? ''; // List
        $airlineCode    = $params['airlineCode'] ?? '';
        $depCode        = $params['depCode'] ?? '';
        $desCode        = $params['desCode'] ?? '';
        $depDate        = $params['depDate'] ?? '';
        $retDate        = $params['retDate'] ?? '';
        $adt            = (int)($params['adt'] ?? 1);
        $chd            = (int)($params['chd'] ?? 0);
        $inf            = (int)($params['inf'] ?? 0);
        $adtFare        = $params['adtFare'] ?? 0;
        $chdFare        = $params['chdFare'] ?? 0;
        $infFare        = $params['infFare'] ?? 0;
        $adtTax         = $params['adtTax'] ?? 0;
        $chdTax         = $params['chdTax'] ?? 0;
        $infTax         = $params['infTax'] ?? 0;
        $adtPrice       = $params['adtPrice'] ?? 0;
        $chdPrice       = $params['chdPrice'] ?? 0;
        $infPrice       = $params['infPrice'] ?? 0;

        $agency = new $agencyName();
        $json = $agency->searchFlights($airlineCode, $depCode, $desCode, date('Y-m-d', strtotime($depDate)), date('Y-m-d', strtotime($retDate)), $adt, $chd, $inf);
        $arr = json_decode($json, true);

        // // Recheck data
        // if(isset($arr['status']) && $arr['status'] == 1) {
        //     $fareSumAmount = 0;

        //     $result = [];
        //     $flights = $arr['data']['dep'] ?? [];
        //     foreach($flights as $f) {
        //         if(!isset($f['transactionID']) || $f['flightNo'] != $flightNo) continue;

        //         // Standard data for automatic booking in the next step
        //         $standardData = [
        //             "SystemCode" => $f['airlineCode'],
        //             "TransactionId" => $f['transactionID'] ?? '',
        //             "FlightNumber" => preg_replace('/\D/', '', $f['flightNo']),
        //             "FarePricings" => [
        //                 [
        //                     "FareBasis" => $f['fareClass'] ?? $f['fareBasis'],
        //                     "PassengerTypeId" => 1,
        //                     "FareSumAmount" => $f["adtPrice"] * $adt
        //                 ]
        //             ],
        //         ];
        //         if($chd > 0) {
        //             $standardData["FarePricings"][] = [
        //                 "FareBasis" => $f['fareClass'] ?? $f['fareBasis'],
        //                 "PassengerTypeId" => 6,
        //                 "FareSumAmount" => $f["chdPrice"] * $chd
        //             ];
        //         }
        //         if($inf > 0) {
        //             $standardData["FarePricings"][] = [
        //                 "FareBasis" => $f['fareClass'] ?? $f['fareBasis'],
        //                 "PassengerTypeId" => 5,
        //                 "FareSumAmount" => $f["infPrice"] * $inf
        //             ];
        //         }

        //         // Data needs to be updated in BM
        //         $updateData = [];
        //         if(date('Y-m-d H:i', strtotime($flightDate)) != ($f['depDate'] . ' ' .$f['depTime'])) {
        //             $updateData['flightDate'] = date('d-m-Y H:i', strtotime($f['depDate'] . ' ' . $f['depTime']));
        //             $updateData['arrivalDate'] = date('d-m-Y H:i', strtotime($f['arvDate'] . ' ' . $f['arvTime']));
        //         }
        //         // if(isset($f["adtPrice"]) && $f["adtPrice"] != $adtPrice) {
        //         //     $updateData['adtFare'] = [
        //         //         "fare"   => $f["adtFare"],
        //         //         "tax"    => $f["adtTax"],
        //         //         "fee"    => $f["adtFee"],
        //         //         "price"  => $f["adtPrice"]
        //         //     ];
        //         // }
        //         // if($chd > 0 && isset($f["chdPrice"]) && $f["chdPrice"] != $chdPrice) {
        //         //     $updateData['chdFare'] = [
        //         //         "fare"   => $f["chdFare"],
        //         //         "tax"    => $f["chdTax"],
        //         //         "fee"    => $f["chdFee"],
        //         //         "price"  => $f["chdPrice"]
        //         //     ];
        //         // }
        //         // if($inf > 0 && isset($f["infPrice"]) && $infPrice != $f["infPrice"]) {
        //         //     $updateData['infFare'] = [
        //         //         "fare"   => $f["infFare"],
        //         //         "tax"    => $f["infTax"],
        //         //         "fee"    => $f["infFee"],
        //         //         "price"  => $f["infPrice"]
        //         //     ];
        //         // }
        //         $vat_percentage = $sugar_config['flight_config']['vat_percentage'] ?? 0.08;
        //         if(isset($f["adtFare"]) && $f["adtFare"] != $adtFare) {
        //             $newFare = $f["adtFare"] ?? 0;
        //             $newTax  = isset($f["adtTax"]) && $f["adtTax"] > 0 ? $f["adtTax"] : $newFare*$vat_percentage;
        //             $updateData['adtFare'] = [
        //                 "fare"   => $newFare,
        //                 "tax"    => $newTax,
        //                 // "fee"    => $f["adtFee"],
        //                 "price"  => $adtPrice - $adtFare - $adtTax + $newFare + $newTax
        //             ];
        //         }
        //         if($chd > 0 && isset($f["chdFare"]) && $f["chdFare"] != $chdFare) {
        //             $newFare = $f["chdFare"] ?? 0;
        //             $newTax = isset($f["chdTax"]) && $f["chdTax"] > 0 ? $f["chdTax"] : $newFare*$vat_percentage;

        //             $updateData['chdFare'] = [
        //                 "fare"   => $newFare,
        //                 "tax"    => $newTax,
        //                 // "fee"    => $f["chdFee"],
        //                 "price"  => $chdPrice - $chdFare - $chdTax + $newFare + $newTax
        //             ];
        //         }
        //         if($inf > 0 && isset($f["infFare"]) && $f["infFare"] != $infFare) {
        //             $newFare = $f["infFare"] ?? 0;
        //             $newTax = isset($f["infTax"]) && $f["infTax"] > 0 ? $f["infTax"] : $newFare*$vat_percentage;
        //             $updateData['infFare'] = [
        //                 "fare"   => $newFare,
        //                 "tax"    => $newTax,
        //                 // "fee"    => $f["infFee"],
        //                 "price"  => $infPrice - $infFare - $infTax + $newFare + $newTax
        //             ];
        //         }

        //         echo json_encode([
        //             "status" => empty($updateData) ? 1 : 0,
        //             "message" => empty($updateData) ? "Matched information" : "Unmatched information",
        //             "data" => [
        //                 "standardData" => $standardData,
        //                 "updateData" => $updateData
        //             ]
        //             ,'flights'=>$flights
        //         ]);
        //         exit();
        //     }

        //     echo json_encode([
        //         "status" => 0,
        //         "message" => "Không tìm thấy chuyến bay $flightNo",
        //         "data" => $flights
        //     ]);
        //     exit();
        // }
        // else {
        //     return [
        //         "status" => 0,
        //         "message" => $arr['message'] ?? "Fail",
        //         "data" => $arr
        //     ];
        // }
    }

    public function updateDataBooking() {

    }

    public function verify() {

    }

    public function booking() {
        
    }

    public function payBooking() {

    }

    public function addBaggage() {

    }
}