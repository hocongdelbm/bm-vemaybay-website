<?php
try {
    require_once("modules/EC_Flight_Bookings/PhuongNamAPI.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $db, $current_user; 
        $action = isset($_POST['action']) ? $_POST['action'] : "";

        if($action == 'get_info_to_auto_book') {
            $booking_id = $_POST['booking_id'] ?? '';
            $list_journey_id = $_POST['list_journey_id'] ?? [];
            $list_passenger_id = $_POST['list_passenger_id'] ?? [];
            $arrMapAirlineCode = ['VJA' => 'VJ', 'VNA' => 'VN', 'VNP' => 'VN', 'BBA' => 'QH', 'VTA' => 'VU'];

            if(!empty($booking_id) && !empty($list_journey_id) && !empty($list_passenger_id)) {
                // Thông tin hành trình
                $dataJourneys = [];
                $stt_dep = $stt_ret = 0;
                $sql_1 = "SELECT iti.id
                        ,iti.departure
                        ,iti.arrival
                        ,iti.departure_date AS departure_date
                        ,iti.arrival_date AS arrival_date
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
                        'birthday' => date('d-m-Y', strtotime($row['birthday'])),
                        'cic' => $row['cic'],
                        'passportNumber' => $row['passport_number']
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
                        'fareFormat'  => format_number($row['fare']) . " VND",
                        'taxFormat'   => format_number($row['tax']) . " VND",
                        'feeFormat'   => format_number($row['fee']) . " VND",
                        'priceFormat' => format_number($row['fare'] + $row['tax'] + $row['fee']) . " VND",
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
            $requestData    = json_decode(file_get_contents('php://input'), true) ?? [];
            $airlineCode    =  $requestData['airlineCode'] ?? '';
            $depCode        =  $requestData['depCode'] ?? '';
            $desCode        =  $requestData['desCode'] ?? '';
            $flightDate     =  $requestData['flightDate'] ?? '';
            $ticketClass    =  $requestData['ticketClass'] ?? '';
            $adt            =  $requestData['adt'] ?? 1;
            $chd            =  $requestData['chd'] ?? 0;
            $inf            =  $requestData['inf'] ?? 0;
            $cabin          = detectCabin($airlineCode, $ticketClass); // Detect cabin from ticket class
            
            $phuongnamapi = new PhuongNamAPI();
            $json = $phuongnamapi->searchFlights($airlineCode, $depCode, $desCode, $flightDate, '', $adt, $chd, $inf, $cabin);
            $arr = json_decode($json, true);

            // Recheck data
            if(isset($arr['error']) && $arr['error'] == 0) {
                $fareSumAmount = 0;

                $flights = $arr['data']['dep'] ?? [];
                foreach($flights as $f) {
                    $f['flightNo'];
                    $f['flightNo'];
                    $f['flightNo'];
                    $f['flightNo'];

                    $fareSumAmount += $f["adtTotalAmount"];
                    if($chd > 0) $fareSumAmount += $f["chdTotalAmount"];
                    if($inf > 0) $fareSumAmount += $f["infTotalAmount"];

                    // $f["adtFare"];
                    // $f["adtTax"];
                    // $f["adtFee"];
                    // $f["adtPrice"];
                    // $f["adtTotalAmount"];
                    // $f["chdFare"];
                    // $f["chdFee"];
                    // $f["chdTax"];
                    // $f["chdPrice"];
                    // $f["chdTotalAmount"];
                    // $f["infFare"];
                    // $f["infFee"];
                    // $f["infTax"];
                    // $f["infPrice"];
                    // $f["infTotalAmount"];
                }
            }
            else {
                echo json_encode(["status" => 0, "message" => "Đối sánh thông tin chuyến bay thất bại", "description" => $arr['message'] ?? '']);
                exit();
            }
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

function detectCabin($airlineCode, $ticketClass) {
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