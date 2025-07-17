<?php
try {
    require_once("modules/EC_Flight_Bookings/PhuongNamAPI.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $db, $current_user;
        $mappingSystemCodeName = ['VJ' => 'Vietjet Air', 'VN' => 'Vietnam Airlines', 'QH' => 'Bamboo Airways', 'VU' => 'Vietravel Airlines']; 
        $requestData = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = isset($requestData['action']) ? $requestData['action'] : "";

        if($action == 'get_info_to_auto_book') {
            $bookingId = $requestData['bookingId'] ?? '';
            $listItineraryId = $requestData['listItineraryId'] ?? [];
            $listPassengerId = $requestData['listPassengerId'] ?? [];
            $arrMapAirlineCode = ['VJA' => 'VJ', 'VNA' => 'VN', 'VNP' => 'VN', 'BBA' => 'QH', 'VTA' => 'VU'];

            if(!empty($bookingId) && !empty($listItineraryId) && !empty($listPassengerId)) {
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

                            $dataJourneys['dep'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'flightDate'  	=> $flightDate,
                                'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                                'flightNo'	    => $row['flight_number'],
                                'ticketClass'   => $row['ticket_class'],
                                'within24h'     => $within24h
                            ];
                        } elseif(isset($dataJourneys['dep'])) $dataJourneys['dep']['desCode'] = $row['arrival'];
                        $stt_dep++;
                    }

                    // Lượt về
                    if ($row['direction'] == '1') {
                        if ($stt_ret == 0 && in_array($row['id'], $listItineraryId)) {
                            $within24h = 0;
                            if(strtotime($row['departure_date']) - time() < 86400) $within24h = 1;
                            $flightDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                            $dataJourneys['ret'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'flightDate'  	=> $flightDate,
                                'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                                'flightNo'	    => $row['flight_number'],
                                'ticketClass'	=> $row['ticket_class'],
                                'within24h'     => $within24h
                            ];
                        } elseif(isset($dataJourneys['ret'])) $dataJourneys['ret']['desCode'] = $row['arrival'];
                        $stt_ret++;
                    }
                }

                if(isset($dataJourneys['dep']) && isset($dataJourneys['ret']) && $dataJourneys['dep']['airlineCode'] != $dataJourneys['ret']['airlineCode']) {
                    echo json_encode([
                        'status' => 0,
                        'message' => 'Chỉ được autobook 1 hãng duy nhất',
                    ]);
                    exit();
                }

                // Thông tin hành khách
                $adtCount = $chdCount = $infCount = 0;
                $dataPassengers = [];
                $inListPassengerId = "'".implode("','", $listPassengerId)."'";
                $sql_2 = "SELECT p.id
                        ,p.type
                        ,p.salutation
                        ,p.name
                        ,p.birthday
                        ,p.cic
                        ,p.passport_number
                    FROM ec_booking_passengers p
                    WHERE p.booking_id = '$bookingId' 
                        AND p.id IN ($inListPassengerId)
                        AND p.deleted = 0
                    ORDER BY p.type";
                $res_2 = $db->query($sql_2);
                while ($row = $db->fetchByAssoc($res_2)) {
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
                    WHERE bkd.booking_id = '$bookingId' AND bkd.deleted = 0
                    ORDER BY bkd.direction, bkd.passenger_type";
                $res_3 = $db->query($sql_3);
                while ($row = $db->fetchByAssoc($res_3)) {
                    if($row['passenger_type'] === '0' && $adtCount < 1) continue;
                    elseif($row['passenger_type'] === '1' && $chdCount < 1) continue;
                    elseif($row['passenger_type'] === '2' && $infCount < 1) continue;

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
                $booking->retrieve($bookingId);
                $dataContact['name'] = $booking->contact_name;
                $dataContact['email'] = $booking->email_reservation;
                $dataContact['phone'] = trim($booking->phone);
                $dataContact['title'] = $booking->contact_title == '0' ? 'Mr.' : 'Ms.';
                $dataContact['address'] = !empty($booking->address) ? trim($booking->address) : '';
                
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
                'params' => [
                    'bookingId' => $bookingId,
                    'listItineraryId' => $listItineraryId,
                    'listPassengerId' => $listPassengerId
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
            if(isset($arr['status']) && $arr['status'] == 1) {
                $fareSumAmount = 0;

                $result = [];
                $flights = $arr['data']['dep'] ?? [];
                foreach($flights as $f) {
                    if(!isset($f['transactionID']) || $f['flightNo'] != $flightNo) continue;

                    // Standard data for automatic booking in the next step
                    $standardData = [
                        "SystemCode" => $f['airlineCode'],
                        "TransactionId" => $f['transactionID'] ?? '',
                        "FlightNumber" => preg_replace('/\D/', '', $f['flightNo']),
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
                        $updateData['arrivalDate'] = date('d-m-Y H:i', strtotime($f['arvDate'] . ' ' . $f['arvTime']));
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
                        ,'flights'=>$flights
                    ]);
                    exit();
                }

                echo json_encode([
                    "status" => 0,
                    "message" => "Flight $flightNo not found",
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
            $bookingId = $requestData['bookingId'] ?? '';
            $direction = $requestData['direction'] ?? null;

            if(is_null($direction) || empty($bookingId)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "bookingId" => $bookingId,
                        "direction" => $direction
                    ]
                ]);
                exit();
            }

            global $sugar_config;
            $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
            $statusUpdate = true;

            // Update fares
            $basePrice = null;
            $passengerTypes = ['adt', 'chd', 'inf'];
            foreach($passengerTypes as $i => $type) {
                $k = $type . "Fare";
                if(isset($requestData[$k]) && !empty($requestData[$k])) {
                    $fare   = $requestData[$k]["fare"];
                    $tax    = $requestData[$k]["tax"];
                    $fee    = $requestData[$k]["fee"];
                    $vatFee = $fee*$sugar_config['vat_percentage'];
                    $price  = $requestData[$k]["price"];

                    $sql = "UPDATE ec_booking_details
                        SET unit_price = $fare
                            ,tax_and_fee = $tax
                            ,airport_fee        = IF($fee > admin_fee, $fee - admin_fee, 0)
                            ,admin_fee          = IF($fee > admin_fee, admin_fee, $fee)
                            ,vat_admin          = IF($fee > admin_fee, vat_admin, $vatFee)
                            ,admin_fee_no_vat   = IF($fee > admin_fee, admin_fee_no_vat, $fee - $vatFee)
                            ,total_bought_price = $price * quantity
                            ,total_price = ($price + service_fee) * quantity
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND passenger_type = '$i'
                            AND deleted = 0";
                    if(!$db->query($sql)) $statusUpdate = false;
                    if($type == 'adt') $basePrice = $fare;
                }
            }

            // Update total number in booking (Don't update total amount)
            $total_bought_amount = $db->getOne("SELECT SUM(total_bought_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $subtotal_amount = $db->getOne("SELECT SUM(total_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $sql = "UPDATE ec_flight_bookings
                    SET total_bought_amount = IF($total_bought_amount > 0, $total_bought_amount, total_bought_amount)
                        ,subtotal_amount = IF($subtotal_amount > 0, $subtotal_amount, subtotal_amount)
                    WHERE id = '$bookingId' AND deleted = 0";
            $db->query($sql);

            // Update flight date
            if(isset($requestData['flightDate']) && !empty($requestData['flightDate'])) {
                $fdate = date('Y-m-d H:i:00', strtotime($requestData['flightDate']));
                $adate = date('Y-m-d H:i:00', strtotime($requestData['arrivalDate']));

                $sql = "UPDATE ec_booking_itineraries
                        SET departure_date = '$fdate'
                            ,arrival_date = '$adate'
                            ". (!is_null($basePrice) ? " ,base_price = $basePrice " : '') ."
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";
                if(!$db->query($sql)) $statusUpdate = false;
            }
            // Update base price
            elseif(!is_null($basePrice)) {
                $sql = "UPDATE ec_booking_itineraries
                        SET base_price = IF($basePrice <> base_price, $basePrice, base_price)
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";
                $db->query($sql);
            }

            if($statusUpdate === true) echo json_encode(["status" => 1, "message" => "Update success"]);
            else echo json_encode(["status" => 0, "message" => "Update fail"]);
            exit();
        }
        elseif($action == 'verify') {
            $bookingId = $requestData['bookingId'] ?? '';
            $flights = $requestData['flights'] ?? [];
            $listPassengerId = $requestData['listPassengerId'] ?? [];
            $isWithin24h = isset($requestData['isWithin24h']) ? (int)$requestData['isWithin24h'] : 0;

            if(!$flights || !is_array($flights) || empty($flights) || empty($bookingId) || !is_array($listPassengerId) || empty($listPassengerId)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "bookingId" => $bookingId,
                        "flights" => $flights,
                        "listPassengerId" => $listPassengerId,
                    ]
                ]);
                exit();
            }

            if($isWithin24h === 1) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Vé cận vui lòng tạm thời xuất qua web portal"
                ]);
                exit();
            }
            
            $phuongnamapi = new PhuongNamAPI();

            // Get airline code
            $airlineCode = '';
            foreach($flights as $f) $airlineCode = $f['SystemCode'];

            // Contact info
            $booking = new EC_Flight_Bookings();
            $booking->retrieve($bookingId);
            $contactName = trim($booking->contact_name);
            $contactTitle = $booking->contact_title == '0' ? 'Mr' : 'Ms';
            $contactPhone = trim($booking->phone);
            $contactEmail = trim($booking->email_reservation);
            $contactAddress = !empty($booking->address) ? trim($booking->address) : '';
            $contactRequiredFields = [
                "contactTitle" => "Danh xưng liên hệ (Mr/Ms)",
                "contactName" => "Họ tên người liên hệ",
                "contactPhone" => "Số điện thoại liên hệ",
                "contactEmail" => "Email liên hệ (Email đặt chỗ)",
                "contactAddress" => "Địa chỉ liên hệ",
            ];
            foreach($contactRequiredFields as $key => $name) {
                if(empty($$key)) {
                    echo json_encode(["status" => 0, "message" => "$name là bắt buộc"]);
                    exit();
                }
                elseif($key == "contactAddress" && $airlineCode == 'VJ' && strlen($contactAddress) > 50) {
                    echo json_encode(["status" => 0, "message" => "Vietjet địa chỉ liên hệ tối đa 50 ký tự"]);
                    exit();
                }
            }

            // Passengers info
            $customerInfos = [];
            $inListPassengerId = "'".implode("','", $listPassengerId)."'";
            $sql_pass = "SELECT p.id
                    ,p.type
                    ,p.salutation
                    ,p.name
                    ,p.birthday
                    ,p.cic
                    ,p.passport_number
                FROM ec_booking_passengers p
                WHERE p.booking_id = '$bookingId' 
                    AND p.id IN ($inListPassengerId)
                    AND p.deleted = 0
                ORDER BY p.type";
            $num = 1;
            $res_pass = $db->query($sql_pass);
            while ($row = $db->fetchByAssoc($res_pass)) {
                $gender = $row['salutation'] == 0 ? 'M' : 'F';
                $title = null;
                if($row['type'] === '0') $title = $row['salutation'] === '0' ? 'Mr' : 'Ms';
                $birthday = !is_null($row['birthday']) && !empty($row['birthday']) && strtotime($row['birthday']) ? $row['birthday'] : null;
                $passport = ($row['cic'] && !empty($row['cic'])) ? $row['cic'] : ($row['passport_number'] ?? null);

                $lastName  = $phuongnamapi->getLastName($row['name'] ?? '');
                $firstName = $phuongnamapi->getFirstName($row['name'] ?? '');
                if($airlineCode == 'QH' && $row['type'] === '2') {
                    $firstName = $phuongnamapi->getOnlyFirstName($row['name'] ?? '');
                }

                $customerInfos[] = [
                    "PersonOrgId" => (string)$num,
                    "PersonOrgIdConfirmed" => null,
                    "PersonOrgCode" => null,
                    "CustomerKey" => null,
                    "PassengerTypeId" => $phuongnamapi->mappingPassengerType($row['type']),
                    "FirstName"     => $firstName,
                    "LastName"      => $lastName,
                    "Age"           => $phuongnamapi->getAge($birthday),
                    "BirthDay"      => $birthday,
                    "Gender"        => $gender,
                    "Title"         => $title,
                    "Phone"         => $row['type'] != '2' ? $contactPhone : null,
                    "Email"         => $row['type'] != '2' ? $contactEmail : null,
                    "AddressFull"   => null,
                    "IsContract"    => true,
                    "RowNumber"     => $num,
                    "PassportType"      => null,
                    "PassportCode"      => null,
                    "Passport"          => $row['type'] != '2' ? $passport : null,
                    "PassportIssuer"    => null,
                    "PassportExpired"   => null,
                    "Nationality"       => null,
                    "ParentGuestId" => null,
                    "ParentGuestIdConfirmed" => null, // QH uses
                    "SortOrder" => $num,
                    "ParentGuestCode" => null, // VJ uses
                    "LoyaltyNumber" => null
                ];

                $num++;
            }
            
            $requestBody = [
                "UserId" => null,
                "UserCode" => null,
                "UserFullName" => null,
                "TransactionId" => null,
                "IsIssueTicket" => (bool)$isWithin24h, // Issue immediately
                "Itinerary" => count($flights), // 1:Một chiều 2:Khứ hồi, 3:Đa chặng
                "ContactTitle" => $contactTitle,
                "ContactName" => $contactName,
                "ContactPhone" => $contactPhone,
                "ContactEmail" => $contactEmail,
                "ContactAddress" => $contactAddress,
                "PromotionCode" => "",
                "PromoCode" => "",
                "IsCombine" => false,
                "Flights" => $flights,
                "CustomerInfos" => $customerInfos,
            ];

            $response = $phuongnamapi->verify($requestBody); // JSON
            $responseArr = json_decode($response, true);

            // Preparing request body for next step
            if($responseArr['status'] == 1) {
                $responseData = $responseArr['data'] ?? [];
                $isVerifyFailed = true;
                $verifyFailedMessage = '';

                // VN combines round trips with the same session verification and fare pricing
                if($airlineCode == 'VN' && count($flights) == 2) {
                    $sessionVerify = $responseArr['data'][0]['SessionVerify'] ?? '';
                    $verifyData = $responseArr['data'][0]['VerifyData'] ?? [];

                    if(!empty($sessionVerify) && is_array($verifyData) && !empty($verifyData)) {
                        foreach($requestBody['Flights'] as $i => $f) {
                            $requestBody['Flights'][$i]['VerifySession'] = $sessionVerify;
                        }
                    }
                    else {
                        $isVerifyFailed = false;
                        $verifyFailedMessage = $responseArr['data'][0]['Message'] ?? '';
                    }
                }
                else {
                    foreach($requestBody['Flights'] as $i => $f) {
                        foreach($responseData as $res) {
                            if(!isset($res['SessionVerify']) || !$res['SessionVerify'] || empty($res['SessionVerify'])) {
                                $isVerifyFailed = false;
                                $verifyFailedMessage = $res['Message'] ?? '';
                                break;
                            }

                            if($f['SystemCode'] == $res['SystemCode']) {
                                $requestBody['Flights'][$i]['VerifySession'] = $res['SessionVerify'] ?? '';
                            }

                            // Bamboo has to update request body into verify data
                            if($airlineCode == 'QH' && isset($res['VerifyData']) && !empty($res['VerifyData'])) {
                                // Verify customers info data
                                $verifyCustomerInfos = $res['VerifyData']['CustomerInfos'] ?? [];
                                if(is_array($verifyCustomerInfos) && !empty($verifyCustomerInfos)) $requestBody['CustomerInfos'] = $verifyCustomerInfos;

                                // Verify flights data
                                $verifyFlights = [];
                                foreach($res['VerifyData']['Flights'] as $i => $f) {
                                    $verifyFlights[$i] = $f;
                                    $verifyFlights[$i]['FarePricings'] = $requestBody['Flights'][$i]['FarePricings'];
                                }
                                if(is_array($verifyFlights) && !empty($verifyFlights)) $requestBody['Flights'] = $verifyFlights;
                            }
                        }
                    }
                }
                $responseArr['requestBody'] = $requestBody; // Update request body

                if(!$isVerifyFailed) {
                    $responseArr['status'] = 0;
                    $responseArr['message'] = $verifyFailedMessage;
                }
            }

            $responseArr['isWithin24h'] = $isWithin24h;
            echo json_encode($responseArr);
            exit();
        }
        elseif($action == 'booking') {
            $bookingId = $requestData['bookingId'] ?? '';
            $listPassengerId = $requestData['listPassengerId'] ?? [];
            $requestBody = $requestData['requestBody'] ?? [];
            
            if(!$requestBody || !is_array($requestBody) || empty($requestBody) || empty($bookingId) || !is_array($listPassengerId) || empty($listPassengerId)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "requestBody" => $requestBody,
                        "listPassengerId" => $listPassengerId,
                        "bookingId" => $bookingId,
                    ]
                ]);
                exit();
            }

            global $sugar_config;

            // Booking type: oneway (Một chiều), roundtrip (Khứ hồi cùng hãng) , twoway (Khứ hồi 2 hãng khác nhau)
            $bookingType = null;
            $countItinerary = count($requestBody['Flights']);
            if($countItinerary == 1) $bookingType = 'oneway';
            elseif($countItinerary == 2) {
                if($requestBody['Flights'][0]['SystemCode'] != $requestBody['Flights'][1]['SystemCode']) $bookingType = 'twoway';
                else $bookingType = 'roundtrip';
            }

            $phuongnamapi = new PhuongNamAPI();
            $response = $phuongnamapi->booking($requestBody);
            $responseArr = json_decode($response, true);

            // Save to BM
            if($responseArr['status'] == 1) {
                $inListPassengerId = "'".implode("','", $listPassengerId)."'";
                foreach($responseArr['data'] as $i => $f) {
                    if(isset($f["ID"]) && $f["ID"] == 1) {
                        // $f["TransactionId"];
                        $bookingCode = explode(":", $f["BookingCode"]); // "VJ: XUBK2G"
                        $systemCode = trim($bookingCode[0] ?? ''); // Airline code
                        $systemName = $mappingSystemCodeName[$systemCode] ?? 'Quốc tế'; // Airline name
                        $pnr = trim($bookingCode[1] ?? '');
                        $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);

                        // Send to Mattermost
                        try {
                            $fullname = trim($current_user->last_name.' '.$current_user->first_name);
                            $linkBooking = ($sugar_config['host_name'] ?? '') ."/index.php?module=EC_Flight_Bookings&action=DetailView&record=$bookingId";
                            $m = "Giữ chỗ $systemName: ".Mattermost::markdownLink($linkBooking, $pnr)." bởi **$fullname**";
                            if(isset($requestBody['IsIssueTicket']) && $requestBody['IsIssueTicket'] == true) $m = "Xuất vé cận $systemName: **$pnr** bởi **$fullname**";
                            $m .= "\n- Transaction ID: " . ($f["TransactionId"] ?? '');
                            Mattermost::sendMessage($sugar_config['mattermost']['channel_id_api_phuong_nam'] ?? '', $m);
                        }
                        catch(Throwable $th) {}

                        if($bookingType == 'roundtrip') {
                            // Update PNR
                            $sql = "UPDATE ec_booking_passengers
                                    SET pnr_outbound = '$pnr'
                                        ,pnr_inbound = '$pnr'
                                        ,modified_user_id = '$current_user->id'
                                        ,date_modified = '$dateModified'
                                    WHERE booking_id = '$bookingId'
                                        AND id IN ($inListPassengerId)
                                        AND deleted = 0";
                            if(!$db->query($sql)) {
                                $m = "**RUN QUEYRY FAIL**";
                                $m .= "`$sql`";
                                Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $m);
                            }

                            // Update supplier
                            $sql = "UPDATE ec_booking_details
                                    SET supplier_id = '$phuongnamapi->SUPPLIER_ID'
                                        ,modified_user_id = '$current_user->id'
                                        ,date_modified = '$dateModified'
                                    WHERE booking_id = '$bookingId' AND deleted = 0";
                            if(!$db->query($sql)) {
                                $m = "**RUN QUEYRY FAIL**";
                                $m .= "`$sql`";
                                Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $m);
                            }
                        }
                        else {
                            // Update PNR
                            $colNamePNR = $i == 0 ? 'pnr_outbound' : 'pnr_inbound';
                            $sql = "UPDATE ec_booking_passengers
                                    SET $colNamePNR = '$pnr'
                                        ,modified_user_id = '$current_user->id'
                                        ,date_modified = '$dateModified'
                                    WHERE booking_id = '$bookingId'
                                        AND id IN ($inListPassengerId)
                                        AND deleted = 0";
                            if(!$db->query($sql)) {
                                $m = "**RUN QUEYRY FAIL**";
                                $m .= "`$sql`";
                                Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $m);
                            }

                            // Update supplier
                            $sql = "UPDATE ec_booking_details
                                    SET supplier_id = '$phuongnamapi->SUPPLIER_ID'
                                        ,modified_user_id = '$current_user->id'
                                        ,date_modified = '$dateModified'
                                    WHERE booking_id = '$bookingId' AND direction = '$i' AND deleted = 0";
                            if(!$db->query($sql)) {
                                $m = "**RUN QUEYRY FAIL**";
                                $m .= "`$sql`";
                                Mattermost::sendMessage($sugar_config['mattermost']['channel_id_logs'] ?? '', $m);
                            }
                        }
                    }
                }
            }
            
            echo json_encode($responseArr);
            exit();
        }
        elseif($action == 'get_booking') {
            $systemCode = $requestData['systemCode'] ?? '';
            $bookingCode = $requestData['bookingCode'] ?? '';

            if(empty($systemCode) || empty($bookingCode)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "systemCode" => $systemCode,
                        "bookingCode" => $bookingCode,
                    ]
                ]);
                exit();
            }

            $phuongnamapi = new PhuongNamAPI();
            $response = $phuongnamapi->getBooking($systemCode, $bookingCode);
            $responseArr = json_decode($response, true);

            // // Get seat map
            // try {
            //     $responseSeatMap = $phuongnamapi->getSeatMapsInfo($systemCode, $bookingCode);
            //     $seatMapArr = json_decode($responseSeatMap, true);
            //     if(isset($seatMapArr['status']) && $seatMapArr['status'] === 1) $responseArr['data']['seatMap'] = $seatMapArr['data'];
            // }
            // catch(Exception $e) {}

            // try {
            //     // Tất cả người lớn dùng chung 1 chi tiết vé theo từng chặng
            //     $passInfo = [];
            //     $sql_pass = "SELECT id as pass_id, type, booking_id, pnr_outbound, pnr_inbound
            //             FROM ec_booking_passengers
            //             WHERE (pnr_outbound = '$bookingCode' OR pnr_inbound = '$bookingCode')
            //                 AND type <> '2'
            //                 AND deleted = 0";
            //     $res_pass = $db->query($sql_pass);
            //     while ($row = $db->fetchByAssoc($res_pass)) {
            //         $pass_id        = $row['id'] ?? '';
            //         $passenger_type = $row['type'] ?? '';
            //         $booking_id     = $row['booking_id'] ?? '';
            //         $pnr_outbound   = $row['pnr_outbound'] ?? '';
            //         $pnr_inbound    = $row['pnr_inbound'] ?? '';

            //         if(empty($booking_id) || (empty($pnr_outbound) && empty($pnr_inbound))) continue;
            //         if($bookingCode == $pnr_outbound) {
            //             if(!isset($passInfo[0]) || !isset($passInfo[0][$passenger_type])) {
            //                 $sql_detail = "SELECT total_bought_price
            //                     FROM ec_booking_details
            //                     WHERE booking_id = '$booking_id'
            //                         AND direction = '0'
            //                         AND passenger_type = '$passenger_type'
            //                         AND deleted = 0";
            //                 $fareSumAmount = $db->getOne($sql_detail) ?? 0;
                            
            //                 $passInfo[0][$passenger_type] = [
            //                     'fareSumAmount' => $fareSumAmount,
            //                 ];
            //             }
            //         }
            //         if($bookingCode == $pnr_inbound) {
            //             if(!isset($passInfo[1]) || !isset($passInfo[1][$passenger_type])) {
            //                 $sql_detail = "SELECT total_bought_price
            //                     FROM ec_booking_details
            //                     WHERE booking_id = '$booking_id'
            //                         AND direction = '1'
            //                         AND passenger_type = '$passenger_type'
            //                         AND deleted = 0";
            //                 $fareSumAmount = $db->getOne($sql_detail) ?? 0;
                            
            //                 $passInfo[1][$passenger_type] = [
            //                     'fareSumAmount' => $fareSumAmount,
            //                 ];
            //             }
            //         }
            //     }

            //     $requestBodyServices = [];
            //     foreach($responseArr['data']['Flights'] as $d => $f) {
            //         $fareBasic = $f["FareBasis"] ?? '';

            //         foreach($passInfo[$d] as $type => $p) {
            //             $tid = $phuongnamapi->mappingPassengerType($type);

            //             $requestBodyServices[$d][$tid] = [
            //                 "Flights" => [
            //                     [
            //                         "SystemCode"    => $systemCode,
            //                         "TransactionId" => $responseArr['data']['TransactionId'] ?? '',
            //                         "FlightNumber"  => $f['FlightNumber'],
            //                         "FarePricings"  => [
            //                             [
            //                                 "FareBasis" => $fareBasic,
            //                                 "PassengerTypeId" => $tid,
            //                                 "FareSumAmount" => (int)$p['fareSumAmount']
            //                             ]
            //                         ],
            //                         "VerifySession" => ""
            //                     ]
            //                 ],
            //                 "IsCombine" => false
            //             ];
            //         }
            //     }
            //     $responseArr['data']['requestBodyServices'] = $requestBodyServices;
            // }   
            // catch(Exception $e) {
            //     $responseArr['data']['requestBodyServices'] = $e->getMessage();
            // }

            // echo $response;
            echo json_encode($responseArr);
            exit();
        }
        elseif($action == 'pay_booking') {
            $systemCode = $requestData['systemCode'] ?? '';
            $bookingCode = $requestData['bookingCode'] ?? '';

            if(empty($systemCode) || empty($bookingCode)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "systemCode" => $systemCode,
                        "bookingCode" => $bookingCode,
                    ]
                ]);
                exit();
            }

            $phuongnamapi = new PhuongNamAPI();
            $response = $phuongnamapi->payForBooking($systemCode, $bookingCode);

            // Send to Mattermost
            try {
                $responseArr = json_decode($response, true);
                if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                    global $sugar_config;
                    $fullname = trim($current_user->last_name.' '.$current_user->first_name);
                    $linkBooking = ($sugar_config['host_name'] ?? '') ."/index.php?module=EC_Flight_Bookings&action=DetailView&record=$bookingId";
                    $systemName = $mappingSystemCodeName[$systemCode] ?? 'Quốc tế'; // Airline name

                    $m = "Xuất vé $systemName: **$bookingCode** bởi **$fullname**";
                    $m .= "\n- Transaction ID: " . ($f["TransactionId"] ?? '');
                    $m .= "\n" . Mattermost::markdownLink($linkBooking, "Mở booking $bookingCode");
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_api_phuong_nam'] ?? '', $m);
                }
            }
            catch(Throwable $th) {}

            echo $response;
            exit();
        }
        elseif($action == 'get_baggage_info') {
            $direction = (int)($requestData['direction'] ?? 0);
            $systemCode = $requestData['systemCode'] ?? '';
            $bookingCode = $requestData['bookingCode'] ?? '';

            if(empty($systemCode) || strlen($bookingCode) < 6) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "systemCode" => $systemCode,
                        "bookingCode" => $bookingCode,
                    ]
                ]);
                exit();
            }
            
            $phuongnamapi = new PhuongNamAPI();
            $response = $phuongnamapi->getBaggageInfo($systemCode, $bookingCode);
            $responseArr = json_decode($response, true);

            if($responseArr['status'] == 1 && is_array($responseArr['data']) && isset($responseArr['data'][$direction]) && !empty($responseArr['data'][$direction])) {
                $responseArr['data'] = $responseArr['data'][$direction];
            }
            else $responseArr['data'] = [];

            echo json_encode($responseArr);
            exit();
        }
        elseif($action == 'add_baggage') {
            $direction = $requestData['direction'] ?? 0;
            $systemCode = $requestData['systemCode'] ?? '';
            $bookingCode = $requestData['bookingCode'] ?? '';
            $serviceKey = $requestData['serviceKey'] ?? '';
            $personOrgId = $requestData['personOrgId'] ?? '';
            $personOrgIdConfirmed = $requestData['bookipersonOrgIdConfirmedngCode'] ?? '';

            if(empty($systemCode) || strlen($bookingCode) < 6 || empty($serviceKey) || empty($personOrgId)) {
                echo json_encode([
                    "status" => 0,
                    "message" => "Invalid params",
                    "params" => [
                        "systemCode" => $systemCode,
                        "bookingCode" => $bookingCode,
                        "services" => [[
                            "ServiceKey" => $serviceKey,
                            "PersonOrgId" => $personOrgId,
                            "PersonOrgIdConfirmed"=> $personOrgIdConfirmed
                        ]],
                    ]
                ]);
                exit();
            }

            $services = [[
                "ServiceKey" => $serviceKey,
                "PersonOrgId" => $personOrgId,
                "PersonOrgIdConfirmed"=> $personOrgIdConfirmed
            ]];
            $phuongnamapi = new PhuongNamAPI();
            $response = $phuongnamapi->addBaggage($systemCode, $bookingCode, $services);
            $responseArr = json_decode($response, true);

            // try {

            // }
            // catch {

            // }

            echo json_encode($responseArr);
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