<?php
try {
    require_once("modules/EC_Flight_Bookings/PhuongNamAPI.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $db, $current_user;
        $requestData = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = isset($requestData['action']) ? $requestData['action'] : "";

        if($action == 'get_info_to_auto_book') {
            $booking_id = $requestData['booking_id'] ?? '';
            $list_journey_id = $requestData['list_journey_id'] ?? [];
            $list_passenger_id = $requestData['list_passenger_id'] ?? [];
            $arrMapAirlineCode = ['VJA' => 'VJ', 'VNA' => 'VN', 'VNP' => 'VN', 'BBA' => 'QH', 'VTA' => 'VU'];

            if(!empty($booking_id) && !empty($list_journey_id) && !empty($list_passenger_id)) {
                // Thông tin hành trình
                $dataJourneys = [];
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
                    WHERE iti.booking_id = '$booking_id' 
                        AND iti.deleted = 0
                        AND iti.add_type = 0
                    ORDER BY iti.direction, iti.date_entered, iti.departure_date";
                $res_1 = $db->query($sql_1);
                while ($row = $db->fetchByAssoc($res_1)) {
                    // Lượt đi
                    if ($row['direction'] == '0') {
                        if ($stt_dep == 0 && in_array($row['id'], $list_journey_id)) {
                            $flightDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                            $dataJourneys['dep'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'flightDate'  	=> $flightDate,
                                'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                                'flightNo'	    => $row['flight_number'],
                                'ticketClass'   => $row['ticket_class']
                            ];
                        } elseif(isset($dataJourneys['dep'])) $dataJourneys['dep']['desCode'] = $row['arrival'];
                        $stt_dep++;
                    }

                    // Lượt về
                    if ($row['direction'] == '1') {
                        if ($stt_ret == 0 && in_array($row['id'], $list_journey_id)) {
                            $flightDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                            $dataJourneys['ret'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'flightDate'  	=> $flightDate,
                                'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                                'flightNo'	    => $row['flight_number'],
                                'ticketClass'	=> $row['ticket_class']
                            ];
                        } elseif(isset($dataJourneys['ret'])) $dataJourneys['ret']['desCode'] = $row['arrival'];
                        $stt_ret++;
                    }
                }

                // Thông tin hành khách
                $dataPassengers = [];
                $in_list_passenger_id = "'".implode("','", $list_passenger_id)."'";
                $sql_2 = "SELECT p.id
                        ,p.type
                        ,p.salutation
                        ,p.name
                        ,p.birthday
                        ,p.cic
                        ,p.passport_number
                    FROM ec_booking_passengers p
                    WHERE p.booking_id = '$booking_id' 
                        AND p.id IN ($in_list_passenger_id)
                        AND p.deleted = 0
                    ORDER BY p.type";
                $res_2 = $db->query($sql_2);
                while ($row = $db->fetchByAssoc($res_2)) {
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
                if(empty($dataPassengers)) $dataPassengers = $sql_2;

                // Thông tin chi tiết vé
                $dateFareDetails = [];
                $sql_3 = "SELECT bkd.id
                        ,bkd.direction
                        ,bkd.passenger_type
                        ,bkd.quantity
                        ,bkd.unit_price AS fare
                        ,bkd.tax_and_fee AS tax
                        ,(IFNULL(bkd.airport_fee, 0) + IFNULL(bkd.admin_fee, 0)) AS fee
                    FROM ec_booking_details bkd
                    WHERE bkd.booking_id = '$booking_id' AND bkd.deleted = 0
                    ORDER BY bkd.direction, bkd.passenger_type";
                $res_3 = $db->query($sql_3);
                while ($row = $db->fetchByAssoc($res_3)) {
                    $direction_name = $row['direction'] == '1' ? 'ret' : 'dep';

                    $dateFareDetails[$direction_name][$row['passenger_type']] = [
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

                // Thông tin liên hệ
                $dataContact = []; 
                $booking = new EC_Flight_Bookings();
                $booking->retrieve($booking_id);
                $dataContact['name'] = $booking->contact_name;
                $dataContact['email'] = $booking->email_reservation;
                $dataContact['phone'] = trim($booking->phone);
                
                echo json_encode([
                    'status' => 1,
                    'message' => 'Success',
                    'data' => [
                        'contact' => $dataContact,
                        'journeys' => $dataJourneys,
                        'passengers' => $dataPassengers,
                        'fareDetails' => $dateFareDetails
                    ]
                ]);
                exit(); 
            }

            echo json_encode([
                'status' => 0,
                'message' => 'Invalid params',
                'data' => [
                    'contact' => $booking_id,
                    'list_journey_id' => $list_journey_id,
                    'list_passenger_id' => $list_passenger_id
                ]
            ]);
            exit();
        }
        elseif($action == 'research') {
            $phuongnamapi = new PhuongNamAPI();

            $requestData    = json_decode(file_get_contents('php://input'), true) ?? [];
            $airlineCode    = $requestData['airlineCode'] ?? '';
            $depCode        = $requestData['depCode'] ?? '';
            $desCode        = $requestData['desCode'] ?? '';
            $flightDate     = $requestData['flightDate'] ?? ''; // d-m-Y H:i
            $ticketClass    = $requestData['ticketClass'] ?? '';
            $flightNo       = $requestData['flightNo'] ?? '';
            $adt            = (int)($requestData['adt'] ?? 1);
            $chd            = (int)($requestData['chd'] ?? 0);
            $inf            = (int)($requestData['inf'] ?? 0);
            $adtPrice       = $requestData['adtPrice'] ?? 0;
            $chdPrice       = $requestData['chdPrice'] ?? 0;
            $infPrice       = $requestData['infPrice'] ?? 0;
            $cabin          = $phuongnamapi->detectCabin($airlineCode, $ticketClass); // Detect cabin from ticket class
            
            $json = $phuongnamapi->searchFlights($airlineCode, $depCode, $desCode, date('Y-m-d', strtotime($flightDate)), '', $adt, $chd, $inf, $cabin);
            $arr = json_decode($json, true);

            // Recheck data
            if(isset($arr['error']) && $arr['error'] == 0) {
                $fareSumAmount = 0;

                $result = [];
                $flights = $arr['data']['dep'] ?? [];
                foreach($flights as $f) {
                    if($f['flightNo'] != $flightNo) continue;

                    // Standard data for automatic booking in the next step
                    $standardData = [
                        "SystemCode" => $f['airlineCode'],
                        "TransactionId" => $f['transactionID'] ?? '',
                        "FlightNumber" => preg_replace('/\D/', '', $flightNo),
                        "FarePricings" => [
                            [
                                "FareBasis" => $f['fareClass'],
                                "PassengerTypeId" => 1,
                                "FareSumAmount" => $f["adtPrice"] * $adt
                            ]
                        ],
                    ];
                    if($chd > 0) {
                        $standardData["FarePricings"][] = [
                            "FareBasis" => $f['fareClass'],
                            "PassengerTypeId" => 6,
                            "FareSumAmount" => $f["chdPrice"] * $chd
                        ];
                    }
                    if($inf > 0) {
                        $standardData["FarePricings"][] = [
                            "FareBasis" => $f['fareClass'],
                            "PassengerTypeId" => 5,
                            "FareSumAmount" => $f["infPrice"] * $inf
                        ];
                    }

                    // Data needs to be updated in BM
                    $updateData = [];
                    if(date('Y-m-d H:i', strtotime($flightDate)) != ($f['depDate'] . ' ' .$f['depTime'])) {
                        $updateData['flightDate'] = date('d-m-Y H:i', strtotime($f['depDate'] . ' ' . $f['depTime']));
                    }
                    if(isset($f["adtPrice"]) && $f["adtPrice"] != $adtPrice) {
                        $updateData['adtFare'] = [
                            "fare"   => $f["adtFare"],
                            "tax"    => $f["adtTax"],
                            "fee"    => $f["adtFee"],
                            "price"  => $f["adtPrice"]
                        ];
                    }
                    if($chd > 0 && isset($f["chdPrice"]) && $f["chdPrice"] != $chdPrice) {
                        $updateData['chdFare'] = [
                            "fare"   => $f["chdFare"],
                            "tax"    => $f["chdTax"],
                            "fee"    => $f["chdFee"],
                            "price"  => $f["chdPrice"]
                        ];
                    }
                    if($inf > 0 && isset($f["infPrice"]) && $infPrice != $f["infPrice"]) {
                        $updateData['infFare'] = [
                            "fare"   => $f["infFare"],
                            "tax"    => $f["infTax"],
                            "fee"    => $f["infFee"],
                            "price"  => $f["infPrice"]
                        ];
                    }

                    echo json_encode([
                        "status" => empty($updateData) ? 1 : 0,
                        "message" => empty($updateData) ? "Matched information" : "Unmatched information",
                        "data" => [
                            "standardData" => $standardData,
                            "updateData" => $updateData
                        ]
                    ]);
                    exit();
                }
                echo json_encode([
                    "status" => 0,
                    "message" => "Flight not found",
                    "data" => $flights
                ]);
                exit();
            }
            else {
                echo json_encode([
                    "status" => 0,
                    "message" => $arr['message'] ?? "Fail",
                    "data" => $arr
                ]);
                exit();
            }
        }
        elseif($action == 'update_data') {
            $bookingID = $requestData['bookingID'] ?? '';
            $direction = $requestData['direction'] ?? null;

            if(is_null($direction) || empty($bookingID)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "bookingID" => $bookingID,
                        "direction" => $direction
                    ]
                ]);
                exit();
            }

            // Update fares
            $basePrice = null;
            $passengerTypes = ['adt', 'chd', 'inf'];
            foreach($passengerTypes as $i => $type) {
                $k = $type . "Fare";
                if(isset($requestData[$k]) && !empty($requestData[$k])) {
                    $fare = $requestData[$k]["fare"];
                    $tax = $requestData[$k]["tax"];
                    $fee = $requestData[$k]["fee"];
                    $price = $requestData[$k]["price"];

                    $sql = "UPDATE ec_booking_details
                        SET unit_price = $fare
                            ,tax_and_fee = $tax
                            ,airport_fee = ($fee - admin_fee)
                            ,total_bought_price = $price * quantity
                            ,total_price = ($price + service_fee) * quantity
                        WHERE booking_id = '$bookingID'
                            AND direction = '$direction'
                            AND passenger_type = '$i'
                            AND deleted = 0";
                    $db->query($sql);

                    if($type == 'adt') $basePrice = $fare;
                }
            }

            // Update flight date
            if(isset($requestData['flightDate']) && !empty($requestData['flightDate'])) {
                $fdate = date('Y-m-d H:i:00', strtotime($requestData['flightDate']));

                $sql = "UPDATE ec_booking_itineraries
                        SET departure_date = '$fdate'
                            ". (!is_null($basePrice) ? " ,base_price = $basePrice " : '') ."
                        WHERE booking_id = '$bookingID'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";
                $db->query($sql);
            }

            echo json_encode(["status" => 1, "message" => "Update success"]);
            exit();
        }

        echo json_encode(["status" => 0, "message" => "Nothing to do"]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["status" => 0, "message" => "Method Not Allowed"]);
    exit();
}
catch(Throwable $th) {
    http_response_code(500);
    echo json_encode(["status" => 0, "message" => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}"]);
    exit();
}