<?php
try {
    // require_once("modules/EC_Flight_Bookings/");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        global $db, $current_user; 
        $action = isset($_POST['action']) ? $_POST['action'] : "";

        if($action == 'get_info_to_auto_book') {
            $booking_id = $_POST['booking_id'] ?? '';
            $list_journey_id = $_POST['list_journey_id'] ?? [];
            $list_passenger_id = $_POST['list_passenger_id'] ?? [];

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
                        $flightDate = explode(' ', $row['departure_date']);
                        if ($stt_dep == 0 && in_array($row['id'], $list_journey_id)) {
                            $dataJourneys['dep'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'date'  	    => $flightDate[0],
                                'time'  	    => substr($flightDate[1], 0, -3),
                                'airlineCode'   => $row['airline_code'],
                                'flightNo'	    => $row['flight_number'],
                                'ticketClass'   => $row['ticket_class']
                            ];
                        } elseif(isset($dataJourneys['dep'])) $dataJourneys['dep']['desCode'] = $row['arrival'];
                        $stt_dep++;
                    }

                    // Lượt về
                    if ($row['direction'] == '1') {
                        $flightDate = explode(' ', $row['departure_date']);
                        if ($stt_ret == 0 && in_array($row['id'], $list_journey_id)) {
                            $dataJourneys['ret'] = [
                                'id'            => $row['id'],
                                'depCode' 	    => $row['departure'],
                                'desCode'	    => $row['arrival'],
                                'date'  	    => $flightDate[0],
                                'time'  	    => substr($flightDate[1], 0, -3),
                                'airlineCode'   => $row['airline_code'],
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
                        AND p.deleted = 0";
                $res_2 = $db->query($sql_2);
                while ($row = $db->fetchByAssoc($res_2)) {
                    $dataPassengers[$row['id']] = [
                        'id' => $row['id'],
                        'type' => $row['type'], // 0:Adt ; 1:Chd ; 2:Inf
                        'salutation' => $row['salutation'], // 0:Mr ; 1:Ms
                        'name'=> $row['name'],
                        'birthday' => $row['birthday'],
                        'cic' => $row['cic'],
                        'passport_number' => $row['passport_number']
                    ];
                }
                if(empty($dataPassengers)) $dataPassengers = $sql_2;

                // Thông tin chi tiết vé
                $dateFareDetails = [];
                $sql_3 = "SELECT bkd.id
                        ,bkd.direction
                        ,bkd.passenger_type
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