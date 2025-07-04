<?php
try {
    require_once("modules/EC_Flight_Bookings/PhuongNamAPI.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $db, $current_user;
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
                        if ($stt_ret == 0 && in_array($row['id'], $listItineraryId)) {
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
                    WHERE bkd.booking_id = '$bookingId' AND bkd.deleted = 0
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

            // Update flight date
            if(isset($requestData['flightDate']) && !empty($requestData['flightDate'])) {
                $fdate = date('Y-m-d H:i:00', strtotime($requestData['flightDate']));

                $sql = "UPDATE ec_booking_itineraries
                        SET departure_date = '$fdate'
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

            // Thông tin liên hệ
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
            }

            // Thông tin hành khách
            $phuongnamapi = new PhuongNamAPI();
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

                $customerInfos[] = [
                    "PersonOrgId" => (string)$num,
                    "PersonOrgIdConfirmed" => null,
                    "PersonOrgCode" => null,
                    "CustomerKey" => null,
                    "PassengerTypeId" => $phuongnamapi->mappingPassengerType($row['type']),
                    "FirstName"     => $phuongnamapi->getFirstName($row['name'] ?? ''),
                    "LastName"      => $phuongnamapi->getLastName($row['name'] ?? ''),
                    "Age"           => $phuongnamapi->getAge($birthday),
                    "BirthDay"      => $birthday,
                    "Gender"        => $gender,
                    "Title"         => $title,
                    "Phone"         => $contactPhone,
                    "Email"         => $contactEmail,
                    "AddressFull"   => null,
                    "IsContract"    => true,
                    "RowNumber"     => $num,
                    "PassportType"      => null,
                    "PassportCode"      => null,
                    "Passport"          => $passport,
                    "PassportIssuer"    => null,
                    "PassportExpired"   => null,
                    "Nationality"       => null,
                    "ParentGuestId" => null,
                    "ParentGuestIdConfirmed" => null,
                    "SortOrder" => $num,
                    "ParentGuestCode" => null,
                    "LoyaltyNumber" => null
                ];

                $num++;
            }
            
            $requestBody = [
                "UserId" => null,
                "UserCode" => null,
                "UserFullName" => null,
                "TransactionId" => null,
                "IsIssueTicket" => false, // Issue immediately
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
            $responseArr['status'] = (int)!$responseArr['error']; // Convert key error to status
            $responseData = $responseArr['data'] ?? [];
            // Preparing request body for next step
            foreach($requestBody['Flights'] as $i => $f) {
                foreach($responseData as $res) { // check here baby
                    if($f['SystemCode'] == $res['SystemCode']) {
                        $requestBody['Flights'][$i]['VerifySession'] = $res['SessionVerify'] ?? '';
                    }
                }
            }
            $responseArr['requestBody'] = $requestBody;
            unset($responseArr['error']);
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
            $mappingSystemCodeName = ['VJ' => 'Vietjet Air', 'VN' => 'Vietnam Airlines', 'QH' => 'Bamboo Airways', 'VU' => 'Vietravel Airlines']; 
            if($responseArr['error'] == 0) {
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
                        $fullname = trim($current_user->last_name.' '.$current_user->first_name);
                        $linkBooking = $sugar_config['host_name']."/index.php?module=EC_Flight_Bookings&action=DetailView&record=$bookingId";
                        $m = "Giữ chỗ: **$pnr** ($systemName) bởi **$fullname**";
                        $m .= "\n- Transaction ID: " . ($f["TransactionId"] ?? '');
                        $m .= "\n" . Mattermost::markdownLink($linkBooking, "Mở booking");
                        Mattermost::sendMessage($sugar_config['mattermost']['channel_id_api_phuong_nam'] ?? '', $m);

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
            
            $responseArr['status'] = (int)!$responseArr['error']; // Convert key error to status
            unset($responseArr['error']);
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
            $responseArr['status'] = (int)!$responseArr['error']; // Convert key error to status
            unset($responseArr['error']);
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
            $responseArr = json_decode($response, true);
            $responseArr['status'] = (int)!$responseArr['error']; // Convert key error to status
            unset($responseArr['error']);
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