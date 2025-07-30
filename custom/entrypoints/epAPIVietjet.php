<?php
require_once("modules/EC_Flight_Bookings/VietjetAPIHelper.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $db, $current_user; 
    $action = isset($_POST['action']) ? strtoupper($_POST['action']) : "";

    if($action == "BOOKING") {
        $body_request   = isset($_POST['body_request']) ? str_replace('&quot;', '"', $_POST['body_request']) : "";
        $direction      = isset($_POST['direction']) ? (int)$_POST['direction'] : null;
        $id_journey_dep = isset($_POST['id_journey_dep']) ? $_POST['id_journey_dep'] : "";
        $id_journey_ret = isset($_POST['id_journey_ret']) ? $_POST['id_journey_ret'] : "";
        $array_id_pass  = isset($_POST['array_id_pass']) ? str_replace('&quot;', '"', $_POST['array_id_pass']) : [];
        $booking_id     = isset($_POST['booking_id']) ? $_POST['booking_id'] : "";
        $supplier_id    = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : "";

        if(!empty($body_request) && !is_null($direction) && !empty($array_id_pass) && !empty($booking_id) && !empty($supplier_id)) {
            $array_id_pass = json_decode($array_id_pass, true);
            $where_in_passenger = "";
            foreach($array_id_pass as $key => $id_pass) {
                if($key == 0) $where_in_passenger .= "(";

                if($key == 0) $where_in_passenger .= "'".$id_pass."'";
                else $where_in_passenger .= ",'".$id_pass."'";

                if($key == count($array_id_pass) - 1) $where_in_passenger .= ")";
            }
            if(!empty($where_in_passenger)) $where_in_passenger = " AND id IN " . $where_in_passenger;

            $VJHelper = new VietjetAPIHelper($supplier_id);
            $json = $VJHelper->booking(json_decode($body_request, true));
            $response = json_decode($json, true);

            // Return
            $result = [];

            if (isset($response['error']) && $response['error'] == 0) {
                $data = $response['data'];
                $pnr  = $data['pnr'];

                // Chuyến đi
                if($direction == 0) {
                    // Update PNR
                    $sql = "UPDATE ec_booking_passengers
                        SET pnr_outbound = '$pnr'
                        WHERE booking_id = '$booking_id' $where_in_passenger";
                    $db->query($sql);

                    // Update supplier
                    $sql = "UPDATE ec_booking_details
                        SET supplier_id = '$supplier_id'
                        WHERE booking_id = '$booking_id' AND direction = '$direction'";
                    $db->query($sql);
                    
                    // Update hold time
                    if($data['status'] == 1) {
                        $sql = "UPDATE ec_booking_itineraries
                                SET time_limit = '".$data['hold_time']."'
                                WHERE id = '$id_journey_dep'";
                        $db->query($sql);
                    }
                }
                // Chuyến về
                else if($direction == 1) {
                    // Update PNR
                    $sql = "UPDATE ec_booking_passengers
                        SET pnr_inbound = '$pnr'
                        WHERE booking_id = '$booking_id' $where_in_passenger";
                    $db->query($sql);

                    // Update supplier
                    $sql = "UPDATE ec_booking_details
                        SET supplier_id = '$supplier_id'
                        WHERE booking_id = '$booking_id' AND direction = '$direction'";
                    $db->query($sql);
                    
                    // Update hold time
                    if($data['status'] == 1) {
                        $sql = "UPDATE ec_booking_itineraries
                                SET time_limit = '".$data['hold_time']."'
                                WHERE id = '$id_journey_ret'";
                        $db->query($sql);
                    }
                }
                // Cả hai chuyến
                else if($direction == 2) {
                    // Update PNR
                    $sql = "UPDATE ec_booking_passengers
                        SET pnr_outbound = '$pnr', pnr_inbound = '$pnr'
                        WHERE booking_id = '$booking_id' $where_in_passenger";
                    $db->query($sql);

                    // Update supplier
                    $sql = "UPDATE ec_booking_details
                        SET supplier_id = '$supplier_id'
                        WHERE booking_id = '$booking_id'";
                    $db->query($sql);
                    
                    // Update hold time
                    if($data['status'] == 1) {
                        $sql = "UPDATE ec_booking_itineraries
                                SET time_limit = '".$data['hold_time']."'
                                WHERE id = '$id_journey_dep' OR id = '$id_journey_ret'";
                        $db->query($sql);
                    }
                }

                if($data['status'] == 1)
                    $result = [
                        "code"          => 1,
                        "message"       => "Giữ chỗ thành công (chưa bao gồm hành lý)<br />PNR: <b>$pnr</b>",
                        "description"   => ""
                    ];
                else if($data['status'] == 2)
                    $result = [
                        "code"          => 1,
                        "message"       => "Xuất vé thành công (chưa bao gồm hành lý)<br />PNR: <b>$pnr</b>",
                        "description"   => ""
                    ];
            }
            else if (isset($response['error']) && $response['error'] != 0 && $response['error_code'] == 1) {
                $result = [
                    "code"          => 0,
                    "message"       => "Lỗi 01: Sai thông tin đặt chỗ",
                    "description"   => $json
                ];
            }
            else if (isset($response['error']) && $response['error'] != 0 && $response['error_code'] == 2) {
                $message = '';
  
                $m = isset($response['message']) ? $response['message'] : '';
                if($m == 'Get flight fail') $message = "Lỗi 02: Lấy dữ liệu chuyến bay bị lỗi, vui lòng thử lại sau";
                elseif($m == 'Not found departure flight') {
                    $message = "Lỗi 02: Thông tin chuyến bay lượt đi không chính xác, vui lòng kiểm tra lại giá, mã chuyến và thời gian bay";
                }
                elseif($m == 'Not found return flight') {
                    $message = "Lỗi 02: Thông tin chuyến bay lượt về không chính xác, vui lòng kiểm tra lại giá, mã chuyến và thời gian bay";
                }
                else {
                    $message = "Lỗi 02: Lỗi tìm chuyến bay";
                    $description = isset($response['check_params']) ? json_encode($response['check_params']) : '';;
                }

                $result = [
                    "code"          => 0,
                    "message"       => $message,
                    "description"   => $description,
                    "list_flight"   => $response['list_flight'] ?? ''
                ];
            }
            else if (isset($response['error']) && $response['error'] != 0 && $response['error_code'] == 3) {
                $response_2  = isset($response['response']) ? $response['response'] : [];
                $description = isset($response_2['message']) ? $response_2['message'] : $response_2;

                $result = [
                    "code"          => 0,
                    "message"       => "Lỗi 03: Báo giá đặt chỗ thất bại",
                    "description"   => $description
                ];
            }
            else if (isset($response['error']) && $response['error'] != 0 && $response['error_code'] == 4) {
                $result = [
                    "code"          => 0,
                    "message"       => "Lỗi 04: Đặt chỗ thất bại",
                    "description"   => $json
                ];
            }
            else {
                $result = [
                    "code"          => 0,
                    "message"       => "ERROR",
                    "description"   => $json
                ];
            }

            echo json_encode($result);
            exit();
        }

        echo json_encode([
            "code"          => -1,
            "message"       => "ERROR: Dữ liệu cung cấp không hợp lệ",
            "description"   => "Check body_request"
        ]);
        exit();
    } elseif($action == "PAY") {
        $supplier_id                = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : "";
        $reservation_key            = isset($_POST['reservation_key']) ? $_POST['reservation_key'] : '';
        $total_amount               = isset($_POST['total_amount']) ? $_POST['total_amount'] : 0;
        $total_amount_processing    = isset($_POST['total_amount_processing']) ? $_POST['total_amount_processing'] : 0;
        $pnr                        = isset($_POST['pnr']) ? $_POST['pnr'] : '';

        if(empty($reservation_key)) {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'Dữ liệu cung cấp không hợp lệ', 'description' => 'Thiếu khóa đặt chỗ']);
            exit();
        }
        elseif(empty($supplier_id)) {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'Dữ liệu cung cấp không hợp lệ', 'description' => 'Thiếu mã NCC']);
            exit();
        }
        elseif($total_amount <= $total_amount_processing) {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'PNR không thể xuất vé', 'description' => 'Đã thanh toán đầy đủ']);
            exit();
        }
        elseif(empty($pnr)) {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'Dữ liệu cung cấp không hợp lệ', 'description' => 'Thiếu PNR']);
            exit();
        }
    
        $VJHelper = new VietjetAPIHelper($supplier_id);
        $info_payment = json_decode($VJHelper->payBooking($reservation_key, $total_amount, $total_amount_processing, $pnr), true);
        
        if (isset($info_payment['error']) && $info_payment['error'] == 0 && !is_null($info_payment['data']) && !empty($info_payment['data'])) {
            echo json_encode([
                'error'     => false,
                'message'   => 'Xuất vé thành công',
                'data'      => $info_payment['data']
            ]);
            exit();
        }
        else {
            echo json_encode(['error' => true, 'code' => 'Lỗi 02', 'message' => 'Xuất vé thất bại', 'response' => $info_payment]);
            exit();
        }
    } else if($action == "SEARCH") {
        $pnr = isset($_POST['pnr']) ? trim($_POST['pnr']) : '';
        if(empty($pnr)) {
            echo json_encode([
                "error" => true,
                "message" => "Mã PNR không hợp lệ"
            ]);
            exit();
        }

        $supplier_id = get_supplier_id_by_pnr($pnr);
        if(!$supplier_id || empty($supplier_id)) {
            echo json_encode(['error' => true, 'message' => 'Không tìm thấy nhà cung cấp', 'data' => ['supplier_id' => $supplier_id]]);
            exit();
        }

        // Init
        $VJHelper = new VietjetAPIHelper($supplier_id);

        // Get booking information
        $info_pnr = json_decode($VJHelper->getBookingByPNR($pnr), true);
        if($info_pnr['error'] == 0) {
            $reservation_key = $info_pnr['data']['key'];
            $info_key = json_decode($VJHelper->getBookingByKey($reservation_key, 1), true);
    
            if($info_key['error'] == 0) {
                $data_pnr = $info_pnr['data'];
                $data_key = $info_key['data'];
                $DEP_CODE = $ARV_CODE = "";
                $result = [];

                ##### BOOKING INFO #####
                $result['booking_info'] = [
                    'pnr'               => $data_pnr['locator'],
                    'reservation_key'   => $reservation_key,
                    'number'            => $data_pnr['number'],
                    'status_code'       => $data_pnr['status']['type'],
                    'status'            => $data_pnr['status']['name'],
                    'charges'           => $data_pnr['charges'],
                    'payments'          => $data_pnr['payments'],
                    'refunds'           => $data_pnr['refunds'],
                    'charges_format'    => format_price($data_pnr['charges']),
                    'payments_format'   => format_price($data_pnr['payments']),
                    'refunds_format'    => format_price($data_pnr['refunds']),
                    'email'             => $data_pnr['contact']['email']
                ];
                if($data_pnr['status']['type'] == 1) $result['booking_info']['hold_time'] = date("H:i d/m/Y", strtotime($data_pnr['status']['hold_time']));
    
                ##### JOURNEYS #####
                $round_trip = false;
                foreach($data_key['journeys'] as $index => $j) {
                    $result['journeys'][$index] = [
                        'key' => $j['key']
                    ];
    
                    foreach($j['segments'] as $seg) {
                        $result['journeys'][$index]['segments'][] = [
                            'key'           => $seg['key'],
                            'airline_code'  => $seg['flight']['airlineCode']['code'],
                            'flight_no'     => $seg['flight']['airlineCode']['code'].$seg['flight']['flightNumber'],
                            'aircraft'      => $seg['flight']['aircraftModel']['name'],
    
                            'dep_code'      => $seg['departure']['airport']['code'],
                            'dep_name'      => $seg['departure']['airport']['name'],
                            'dep_time'      => date("H:i d/m/Y", strtotime($seg['departure']['localScheduledTime'])),
    
                            'arv_code'      => $seg['arrival']['airport']['code'],
                            'arv_name'      => $seg['arrival']['airport']['name'],
                            'arv_time'      => date("H:i d/m/Y", strtotime($seg['arrival']['localScheduledTime'])),
                        ];
    
                        if($index == 0 && empty($depcode)) $DEP_CODE = $seg['departure']['airport']['code'];
                        if($index == 0) $ARV_CODE = $seg['arrival']['airport']['code'];
                    }
    
                    if($index == 0) $result['journeys'][$index]['itinerary'] = $DEP_CODE . " - " . $ARV_CODE;
                    if($index == 1) {
                        $result['journeys'][$index]['itinerary'] = $ARV_CODE . " - " . $DEP_CODE;
                        $round_trip = true;
                    }

                    // Add booking key
                    $result['journeys'][$index]['booking_keys'] = [];
                    foreach($j['passengerJourneyDetails'] as $pdetail) {
                        $passkey = $pdetail['passenger']['key'] ?? '';
                        $result['journeys'][$index]['booking_keys'][$passkey] = $pdetail['bookingKey']['key'] ?? '';
                    }
                }
    
                ##### OPTION #####
                $list_pass_key = ['dep' => [], 'ret' => []];
                $baggage = json_decode($VJHelper->getCharges($reservation_key), true);
                if($baggage['error'] == 0 && !is_null($baggage['data']) && !empty($baggage['data'])) {
                    foreach($baggage['data'] as $option) {
                        if($option['code'] != 'SR') continue;

                        // Get journey info
                        $key_journey = $option['key_journey'];
                        $journey = json_decode($VJHelper->getJourney($reservation_key, $key_journey), true)['data'];

                        // Get passenger info
                        $direction = '';
                        if($journey['departure_code'] == $DEP_CODE) {
                            if($option['totalAmount'] > 0)
                                $list_pass_key['dep'][] = $option['key_passenger'];
                            $direction = 'Lượt đi';
                        }
                        elseif($journey['departure_code'] == $ARV_CODE) {
                            if($option['totalAmount'] > 0)
                                $list_pass_key['ret'][] = $option['key_passenger'];
                            $direction = 'Lượt về';
                        }
                        $pass = json_decode($VJHelper->getPassengerByKey($reservation_key, $option['key_passenger']), true)['data'];
    
                        $gender = $pass['gender'] == 'Male' ? 'Nam' : 'Nữ';
                        $row = [
                            'direction'         => $direction,
                            'passenger_type'    => $pass['type'],
                            'passenger_gender'  => $gender,
                            'passenger_name'    => $pass['name'],
                            'detail'            => $option['name'],
                            'total'             => $option['totalAmount']
                        ];
                        $result['options'][] = $row;
                    }
                }
    
                ##### PASSENGERS #####
                $icon_add_luggage = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square-fill me-2" viewBox="0 0 16 16">
                                        <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm6.5 4.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3a.5.5 0 0 1 1 0"/>
                                    </svg>';
    
                $passengers = json_decode($VJHelper->getPassengers($reservation_key), true);
                if ($passengers['error'] == 0 && !is_null($passengers['data']) && !empty($passengers['data'])) {
                    foreach($passengers['data'] as $p) {
                        // Button
                        $button_add_luggage_dep = $button_add_luggage_ret = $button_update_passenger = "";
                        if(!in_array($p['key'], $list_pass_key['dep'])) {
                            $button_add_luggage_dep = '<button type="button" class="btn btn-primary btn-add-luggage btn-add-luggage-dep me-2"
                                re_key="'.$reservation_key.'"
                                pass_key="'.$p['key'].'"
                            >
                                '.$icon_add_luggage.'Hành lý đi
                            </button>';
                        }
                        if($round_trip && !in_array($p['key'], $list_pass_key['ret'])) {
                            $button_add_luggage_ret = '<button type="button" class="btn btn-primary btn-add-luggage btn-add-luggage-ret"
                                re_key="'.$reservation_key.'"
                                pass_key="'.$p['key'].'"
                            >
                                '.$icon_add_luggage.'Hành lý về
                            </button>';
                        }
                        
                        if($p['type'] == 'Em bé') {
                            $button_add_luggage_dep = $button_add_luggage_ret = "";
                            $key_pass_edit = $p['key_adult'];
                        } 
                        else $key_pass_edit = $p['key'];

                        $button_update_passenger = '<button type="button" class="btn btn-primary btn_edit_passenger" re_key="'.$reservation_key.'" pass_key="'.$key_pass_edit.'" pass_type="'.$p['type'].'">Thông tin</button>';
                        
                        $gender = $p['gender'] == 'Male' ? 'Nam' : 'Nữ';
                        $birthdate = is_null($p['birthdate']) || empty($p['birthdate']) ? '' : date("d-m-Y", strtotime($p['birthdate']));
                        $result['passengers'][$p['key']] = [
                            'type'      => $p['type'],
                            'gender'    => $gender,
                            'name'      => $p['name'],
                            'birthdate' => $birthdate,
                            'email'     => isset($p['email']) ? $p['email'] : '',
                            'mobile'    => isset($p['mobile']) ? format_mobile_phone($p['mobile']) : '',
                            'inumber'   => isset($p['inumber']) ? $p['inumber'] : '',
                            'button_add_luggage'        => $button_add_luggage_dep . $button_add_luggage_ret,
                            'button_update_passenger'   => $button_update_passenger
                        ];
    
                    }
                }
    
                ##### PRICES #####
                $base_price = [];
                foreach($data_key['charges'] as $key => $charges) {
                    if($charges['status']['active'] === true) {
                        $code           = $charges['chargeType']['code'];
                        $key_passenger  = $charges['passenger']['key'];
                        $key_journey    = $charges['journey']['key'];
                        $base_amount    = $charges['currencyAmounts'][0]['baseAmount'];
                        $total_amount    = $charges['currencyAmounts'][0]['totalAmount'];
    
                        $type = "none";
                        if($key_journey == $result['journeys'][0]['key']) $type = 'dep';
                        elseif($key_journey == $result['journeys'][1]['key']) $type = 'ret';
    
                        $result['charges'][$type][$key_passenger][$key] = [
                            'code'                  => $code,
                            'code_description'      => $charges['chargeType']['description'],
                            'description'           => $charges['description'],
                            'base_amount'           => $base_amount,
                            'total_amount'          => $total_amount
                        ];
    
                        if($charges['chargeType']['code'] == "FA" && (empty($base_price) || !isset($base_price[$type]))) $base_price[$type] = format_price($base_amount);
                    }
                }
                $result['base_price'] = $base_price;
                
                $result['error'] = false;
                $result['supplier'] = [
                    'id' => $supplier_id,
                    'name' => $data_pnr['company']['name']
                ];

                ##### AGENCY #####
                $agency = json_decode($VJHelper->getAgency(), true);
                if(isset($agency['error']) && $agency['error'] == 0) {
                    $result['supplier']['iataNumber'] = $agency['data'][0]['iataNumber'];
                    $result['supplier']['accountNumber'] = $agency['data'][0]['accountNumber'];
                    $result['supplier']['creditAvailable'] = number_format($agency['data'][0]['creditAvailable'], 0, ',', '.') . ' VND';
                    $result['supplier']['iataNumber'] = $agency['data'][0]['iataNumber'];
                    $result['supplier']['name'] = $agency['data'][0]['name'];
                }

                echo json_encode($result);
                exit();
            }
    
            echo json_encode(['error' => true, 'message' => 'Lấy dữ liệu thất bại']);
            exit();
        }
        echo json_encode(['error' => true, 'message' => 'Mã PNR không hợp lệ', 'supplier_id' => $supplier_id, 'data' => $info_pnr]);
        exit();
    } else if($action == "ADD_ANCILLARY") {
        $pnr = isset($_POST['pnr']) ? trim($_POST['pnr']) : '';
        $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';

        // Lấy danh sách hành lý của Vietjet theo booking key
        if($_POST['type'] == 'get') {
            $reservation_key   = isset($_POST['re_key']) ? $_POST['re_key'] : '';
            $passenger_key     = isset($_POST['pass_key']) ? $_POST['pass_key'] : '';
            $direction         = isset($_POST['direction']) ? (int)$_POST['direction'] : null;
    
            if(empty($reservation_key) || empty($passenger_key) || empty($supplier_id)|| is_null($direction)){
                echo json_encode([
                    'error' => true,
                    'code' => 'Lỗi 02',
                    'message' => 'Dữ liệu cung cấp không hợp lệ', 
                    'data' => [
                        'pnr'               => $pnr,
                        'supplier_id'       => $supplier_id,
                        'reservation_key'   => $reservation_key,
                        'passenger_key'     => $passenger_key,
                        'direction'         => $direction
                    ]
                ]);
                exit();
            }
    
            $VJHelper = new VietjetAPIHelper($supplier_id);
            $res = json_decode($VJHelper->getBookingByKey($reservation_key, 1), true);
            if(isset($res['error']) && $res['error'] == 0 && !is_null($res['data'])) {
                $journey = $res['data']['journeys'][$direction];
                $journey_key    = $journey['key'];
     
                foreach($journey['passengerJourneyDetails'] as $p) {
                    if($passenger_key == $p['passenger']['key']) {
                        $booking_key = $p['bookingKey'];

                        $options = json_decode($VJHelper->getBaggage($booking_key), true);
                        if (isset($options['error']) && $options['error'] == 0 && !is_null($options['data'])) {
                            $result = ['error'=>null, 'data' => []];
                            
                            foreach($options['data'] as $opt) {
                                if(strpos($opt['description'], "hành lý") === false) continue;
                                $result['data'][] = [
                                    'journey_key'   => $journey['key'],
                                    'passenger_key' => $p['passenger']['key'],
                                    'purchase_key'  => $opt['purchaseKey'],
                                    'booking_key'   => $booking_key,
                                    'description'   => $opt['description'],
                                    'total'         => $opt['totalAmount']
                                ];
                            }
    
                            echo json_encode($result);
                            exit();
                        }
                        else {
                            echo json_encode(['error' => true, 'code' => 'Lỗi 04', 'message' => 'Booking này không thể thêm hành lý', 'response' => $booking_key]);
                            exit();
                        }
                    }
                }
            }
            else {
                echo json_encode(['error' => true, 'code' => 'Lỗi 03', 'message' => 'Booking này không thể thêm hành lý', 'response' => $res]);
                exit();
            }
        }
        // Thêm hành lý vào đặt chỗ
        elseif($_POST['type'] == 'add') {
            $reservation_key   = isset($_POST['re_key']) ? $_POST['re_key'] : '';
            $purchase_key      = isset($_POST['purchase_key']) ? $_POST['purchase_key'] : '';
            $journey_key       = isset($_POST['journey_key']) ? $_POST['journey_key'] : '';
            $passenger_key     = isset($_POST['passenger_key']) ? $_POST['passenger_key'] : '';
            $booking_key       = isset($_POST['booking_key']) ? $_POST['booking_key'] : '';
            $value             = isset($_POST['value']) ? $_POST['value'] : '';
            $username          = $current_user->user_name;
    
            if(empty($reservation_key) || empty($purchase_key) || empty($journey_key) || empty($passenger_key) || empty($booking_key) ||
                empty($pnr) || empty($supplier_id) || empty($value) || empty($username)
            ) {
                echo json_encode([
                    'error' => true,
                    'code' => 'Lỗi 02',
                    'message' => 'Dữ liệu cung cấp không hợp lệ', 
                    'data' => [
                        'pnr'               => $pnr,
                        'supplier_id'       => $supplier_id,
                        'reservation_key'   => $reservation_key,
                        'purchase_key'      => $purchase_key,
                        'journey_key'       => $journey_key,
                        'passenger_key'     => $passenger_key,
                        'booking_key'       => $booking_key,
                        'value'             => $value,
                        'username'          => $username
                    ]
                ]);
                exit();
            }

            $total_old = $total_new = 0;
            $VJHelper = new VietjetAPIHelper($supplier_id);
            $info = json_decode($VJHelper->getBookingByPNR($pnr), true);
            if (isset($info['error']) && $info['error'] == 0 && !is_null($info['data'])) {
                switch ($info['data']['status']['type']) {
                    case 0:
                        echo json_encode(['error' => 'warning', 'message' => 'Đặt chỗ đã bị hủy']);
                        exit();
                    case 1:
                        $paid = 0;
                        break;
                    case 2:
                        $paid = 1;
                        break;
                    default:
                        $paid = 0;
                }
    
                $total_old = $info['data']['charges'];
                $total_new = $total_old + $value;
            } 
            else $paid = 0;
    
            $parameters = [
                'reservation_key'   => $reservation_key,
                'purchase_key'      => $purchase_key,
                'passenger_key'     => $passenger_key,
                'journey_key'       => $journey_key,
                'booking_key'       => $booking_key,
                'total'             => $total_new,
                'paid'              => $paid
            ];
    
            $res = json_decode($VJHelper->addBaggage($parameters), true);
            if (isset($res['error']) && $res['error'] == 0 && !is_null($res['data'])) {
                echo json_encode(['error' => false, 'message' => 'Thêm hành lý thành công', 'data' => $res]);
                exit();
            }
    
            echo json_encode(['error' => true, 'code' => 'Lỗi 03', 'message' => 'Thêm hành lý thất bại', 'response' => $res, 'params' => $parameters]);
            exit();
        }
        else {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'Dữ liệu cung cấp không hợp lệ']);
            exit();
        }
    } else if ($action == "SEARCH_CHANGE_JOURNEY"){
        $direction      = isset($_POST['direction']) ? trim($_POST['direction']) : '';
        $re_key         = isset($_POST['re_key']) ? trim($_POST['re_key']) : '';
        $journey_key    = isset($_POST['journey_key']) ? trim($_POST['journey_key']) : '';
        $depart         = isset($_POST['depart']) ? trim($_POST['depart']) : '';
        $arrival        = isset($_POST['arrival']) ? trim($_POST['arrival']) : '';
        $date           = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : '';
        $supplier_id    = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : "";

        // Init
        $VJHelper = new VietjetAPIHelper($supplier_id);

        // Get list journey change
        $list_journey = json_decode($VJHelper->get_flight_update_journey($re_key, $journey_key, $depart, $arrival, $date, $class), true);
        if($list_journey['error'] == 0) {
            $array_journey = $list_journey['data'];
            echo json_encode($array_journey);
            exit();
        }

        echo json_encode(['error' => true, 'message' => 'Hành trình không hợp lệ', 'response' => $supplier_id]);
        exit();
    } else if($action == 'GET_CREDIT_AVAILABLE'){
        $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : "";
        if(empty($supplier_id)) {
            echo json_encode(['error' => true, 'code' => 'Lỗi 01', 'message' => 'Dữ liệu cung cấp không hợp lệ', 'description' => 'Thiếu mã NCC']);
            exit();
        }

        $VJHelper = new VietjetAPIHelper($supplier_id);
        $result = [];
        ##### AGEENCY #####
        $agency = json_decode($VJHelper->getAgency(), true);
        if(isset($agency['error']) && $agency['error'] == 0) {
            $result['iataNumber'] = $agency['data'][0]['iataNumber'];
            $result['accountNumber'] = $agency['data'][0]['accountNumber'];
            $result['creditAvailable'] = number_format($agency['data'][0]['creditAvailable'], 0, ',', '.') . ' VND';
            $result['iataNumber'] = $agency['data'][0]['iataNumber'];
            $result['name'] = $agency['data'][0]['name'];
        }

        echo json_encode($result);
        exit();
    }
    
    echo json_encode([
        "code"          => -1,
        "message"       => "ERROR: Dữ liệu cung cấp không hợp lệ",
        "description"   => "Check type"
    ]);
    exit();
}

function get_supplier_id_by_pnr($pnr) {
    if(empty($pnr)) return $pnr;

    return '3e414dde-85b6-315b-e0ba-6556c458368f';

    // Get supplier id
    global $db;
    $sql = "SELECT supplier_id
        FROM ec_booking_details d
        WHERE booking_id = (
                SELECT p.booking_id
                FROM ec_booking_passengers p
                    LEFT JOIN ec_booking_itineraries i ON i.booking_id = p.booking_id
                WHERE (p.pnr_outbound = '$pnr' OR p.pnr_inbound = '$pnr')
                    AND i.airline_code IN ('VJA', 'VJ', 'VZ')
                    AND p.deleted = 0 
                ORDER BY p.date_entered DESC
                LIMIT 1
            )
            -- AND supplier_id <> '' 
            AND supplier_id IN('3e414dde-85b6-315b-e0ba-6556c458368f', '7df1cbf9-21b6-4f45-7cc5-62011601a951')
            AND deleted = 0 
        ORDER BY d.date_entered DESC
        LIMIT 1";
    $supplier_id = $db->getOne($sql);

    return $supplier_id;
}

function format_price($price) {
    $number = number_format((float)$price, 0, '.', ',');
    return $number.' VND';
}

function format_mobile_phone($mobile_phone) {
    return str_replace("+84", 0, $mobile_phone);
}

echo json_encode([
    "code"          => -1,
    "message"       => "ERROR: Dữ liệu cung cấp không hợp lệ",
    "description"   => "Check SERVER_REQUEST_METHOD"
]);
exit();
?>