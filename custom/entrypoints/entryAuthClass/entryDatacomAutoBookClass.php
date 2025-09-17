<?php
require_once "custom/entrypoints/entryAuthClass/entryClass.php";
require_once "modules/EC_Flight_Bookings/APIDatacom.php";

/**
 * Class entryDatacomAutoBookClass
 * 
 * Using for booking by Datacom API
 */
class entryDatacomAutoBookClass extends entryClass {
    public $mappingSystemCodeName;
    public $mappingSystemCode;
    public $interSystemCode;
    public $vatPercentage;
    public $supplierId;
    public $supplierCode;
    public $supplierName;

    public function __construct() {
        parent::__construct();
        global $sugar_config;
        $this->vatPercentage = $sugar_config['flight_config']['vat_percentage'] ?? 0.08;
        $this->interSystemCode = $sugar_config['api_autobook']['InterSystemCode'] ?? '1A';
        $this->mappingSystemCodeName = [
            'VJ' => 'Vietjet Air',
            'VN' => 'Vietnam Airlines',
            'QH' => 'Bamboo Airways',
            'VU' => 'Vietravel Airlines'
        ];
        $this->mappingSystemCode = [
            'VJA' => 'VJ',
            'VNA' => 'VN',
            'VNP' => 'VN',
            'BBA' => 'QH',
            'VTA' => 'VU'
        ];
        // BM database
        $this->supplierId = "ebdf163a-7b85-30bf-62be-5a4af5a1166c";
        $this->supplierCode = "VNAHNH";
        $this->supplierName = "Hồng Ngọc Hà 218";
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
                        $departureDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                        $dataItineraries['dep'] = [
                            'id'            => $row['id'],
                            'depCode' 	    => $row['departure'],
                            'desCode'	    => $row['arrival'],
                            'departureDate' => $departureDate,
                            'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                            'flightNo'	    => $row['flight_number'],
                            'ticketClass'   => $row['ticket_class'],
                            'within24h'     => $within24h
                        ];
                    }
                    elseif(isset($dataItineraries['dep'])) {
                        $dataItineraries['dep']['desCode'] = $row['arrival'];
                        $dataItineraries['dep']['flightNo'] .= ' - ' . $row['flight_number'];
                    }
                    $stt_dep++;
                }

                // Lượt về
                if ($row['direction'] == '1') {
                    if ($stt_ret == 0 && in_array($row['id'], $listItineraryId)) {
                        $within24h = 0;
                        if(strtotime($row['departure_date']) - time() < 86400) $within24h = 1;
                        $departureDate = date('d-m-Y H:i', strtotime($row['departure_date']));
                        $dataItineraries['ret'] = [
                            'id'            => $row['id'],
                            'depCode' 	    => $row['departure'],
                            'desCode'	    => $row['arrival'],
                            'departureDate' => $departureDate,
                            'airlineCode'   => $arrMapAirlineCode[$row['airline_code']] ?? $row['airline_code'],
                            'flightNo'	    => $row['flight_number'],
                            'ticketClass'	=> $row['ticket_class'],
                            'within24h'     => $within24h
                        ];
                    }
                    elseif(isset($dataItineraries['ret'])) {
                        $dataItineraries['ret']['desCode'] = $row['arrival'];
                        $dataItineraries['ret']['flightNo'] .= ' - ' . $row['flight_number'];
                    }
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
                    'fareDetails' => $dataFareDetails,
                    'entryClass' => __CLASS__,
                    'supplierName' => $this->supplierName,
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
                "listDetailId" => $listDetailId
            ]
        ];
    }

    public function getBooking() {

    }

    public function getBaggageInfo() {

    }

    public function research($params = []) {
        $airlineCode    = $params['airlineCode'] ?? '';
        $depCode        = $params['depCode'] ?? '';
        $desCode        = $params['desCode'] ?? '';
        $depDate        = $params['depDate'] ?? '';
        $retDate        = $params['retDate'] ?? '';
        $adt            = (int)($params['adt'] ?? 0);
        $chd            = (int)($params['chd'] ?? 0);
        $inf            = (int)($params['inf'] ?? 0);
        $isInter        = (int)($params['isInter'] ?? 0);
        $listItineraryId = $params['listItineraryId'] ?? [];
        $flightNo       = $params['flightNo'] ?? [];
        $adtDetailId    = $params['adtDetailId'] ?? [];
        $chdDetailId    = $params['chdDetailId'] ?? [];
        $infDetailId    = $params['infDetailId'] ?? [];
        $adtFare        = $params['adtFare'] ?? [];
        $chdFare        = $params['chdFare'] ?? [];
        $infFare        = $params['infFare'] ?? [];
        $adtTax         = $params['adtTax'] ?? [];
        $chdTax         = $params['chdTax'] ?? [];
        $infTax         = $params['infTax'] ?? [];
        $adtPrice       = $params['adtPrice'] ?? [];
        $chdPrice       = $params['chdPrice'] ?? [];
        $infPrice       = $params['infPrice'] ?? [];

        $agency = new APIDatacom();

        // Format search params
        $airlineCodeSearch = $isInter ? $this->interSystemCode : $this->mappingSystemCode[$params['airlineCode'] ?? ''] ?? '';
        $depDateSearch = $retDateSearch = '';
        if(!empty($depDate)) $depDateSearch = date('Y-m-d', strtotime($depDate));
        if(!empty($retDate)) $retDateSearch = date('Y-m-d', strtotime($retDate));
        $options = [
            'isInter' => $isInter
        ];

        // Search
        $json = $agency->searchFlights($airlineCodeSearch, $depCode, $desCode, $depDateSearch, $retDateSearch, $adt, $chd, $inf, $options);
        $arr = json_decode($json, true);

        // Recheck data
        if(isset($arr['status']) && $arr['status'] == 1) {
            $standardData = [];
            $updateData = [];
            $flightData = [];

            if(!$isInter) { // Domestic
                $i = 0;
                foreach($arr['data'] as $roundName => $round) {
                    $totalAmout = 0;
                    foreach($round as $f) {
                        if(!isset($f["sessionId"]) || $f["flightNo"] != ($flightNo[$i] ?? '')) continue;

                        // Standard ListAirOption data for automatic booking in the next step
                        $standardData[$i] = [
                            "Session"           => $f["sessionId"] ?? null,
                            "SessionType"       => "search", // Hard code
                            "AirlineOptionId"   => $f["airlineId"] ?? null,
                            "FareOptionId"      => $f["fareId"] ?? null,
                            "FlightOptionId"    => $f["flightId"] ?? null,
                            "Tourcode"          => "",
                            "CAcode"            => "",
                            "VIPText"           => "",
                            "Remark"            => "",
                            "AccountCode"       => ""
                        ];

                        // Check datetime
                        $flightDate = $i == 1 ? $retDate : $depDate;
                        if(date('Y-m-d H:i', strtotime($flightDate)) != ($f['depDate'] . ' ' .$f['depTime'])) {
                            $updateData[$i]['departureDate']    = date('d-m-Y H:i', strtotime($f['depDate'] . ' ' . $f['depTime']));
                            $updateData[$i]['arrivalDate']      = date('d-m-Y H:i', strtotime($f['arvDate'] . ' ' . $f['arvTime']));
                        }

                        // Check prices
                        if(isset($f["adtFare"]) && isset($adtFare[$i]) && $f["adtFare"] != $adtFare[$i]) {
                            $newFare    = $f["adtFare"] ?? 0;
                            $newTax     = isset($f["adtTax"]) && $f["adtTax"] > 0 ? $f["adtTax"] : $newFare * $this->vatPercentage;
                            $newPrice   = $adtPrice[$i] - $adtFare[$i] - $adtTax[$i] + $newFare + $newTax; // Current fee + new fare + new tax

                            $updateData[$i]['adtFare'] = [
                                "detailId"  => $adtDetailId[$i] ?? '',
                                "fare"      => $newFare,
                                "tax"       => $newTax,
                                // "fee"    => $f["adtFee"],
                                "price"     => $newPrice
                            ];
                            $totalAmout += $newPrice * $adt;
                        }
                        else $totalAmout += ($adtPrice[$i] ?? 0) * $adt;
                        if($chd > 0 && isset($f["chdFare"]) && isset($chdFare[$i]) && $f["chdFare"] != $chdFare[$i]) {
                            $newFare    = $f["chdFare"] ?? 0;
                            $newTax     = isset($f["chdTax"]) && $f["chdTax"] > 0 ? $f["chdTax"] : $newFare * $this->vatPercentage;
                            $newPrice   = $chdPrice[$i] - $chdFare[$i] - $chdTax[$i] + $newFare + $newTax;

                            $updateData[$i]['chdFare'] = [
                                "detailId"  => $chdDetailId[$i] ?? '',
                                "fare"      => $newFare,
                                "tax"       => $newTax,
                                // "fee"    => $f["chdFee"],
                                "price"     => $newPrice
                            ];
                            $totalAmout += $newPrice * $chd;
                        }
                        else $totalAmout += ($chdPrice[$i] ?? 0) * $chd;
                        if($inf > 0 && isset($f["infFare"]) && isset($infFare[$i]) && $f["infFare"] != $infFare[$i]) {
                            $newFare    = $f["infFare"] ?? 0;
                            $newTax     = isset($f["infTax"]) && $f["infTax"] > 0 ? $f["infTax"] : $newFare * $this->vatPercentage;
                            $newPrice   = $infPrice[$i] - $infFare[$i] - $infTax[$i] + $newFare + $newTax;

                            $updateData[$i]['infFare'] = [
                                "detailId"  => $infDetailId[$i] ?? '',
                                "fare"      => $newFare,
                                "tax"       => $newTax,
                                // "fee"    => $f["infFee"],
                                "price"     => $newPrice
                            ];
                            $totalAmout += $newPrice * $inf;
                        }
                        else $totalAmout += ($infPrice[$i] ?? 0) * $inf;

                        // Add itinerary id
                        if(isset($updateData[$i]) && !empty($updateData[$i])) {
                            $updateData[$i]['itineraryId'] = $listItineraryId[$i] ?? ''; // Itinerary Id
                            $updateData[$i]['totalAmount'] = $totalAmout; // Total amount
                        }
                        // Add this flight info
                        $flightData[$i] = $f;
                        break;
                    }
                    $i++;
                }
            }
            else { // International
                foreach($arr['data'] as $f) {
                    $isThatFlight = true;
                    $roundList = !empty($retDate) ? ['dep', 'ret'] : ['dep'];

                    foreach($roundList as $i => $roundName) {
                        if(!isset($f["sessionId"]) || $f[$roundName]["flightNo"] != ($flightNo[$i] ?? '')) {
                            $isThatFlight = false;
                            continue;
                        };

                        // Standard ListAirOption data for automatic booking in the next step
                        $standardData[$i] = [
                            "Session"           => $f["sessionId"] ?? null,
                            "SessionType"       => "search", // Hard code
                            "AirlineOptionId"   => $f["airlineId"] ?? null,
                            "FareOptionId"      => $f["fareId"] ?? null,
                            "FlightOptionId"    => $f["flightId"] ?? null,
                            "Tourcode"          => "",
                            "CAcode"            => "",
                            "VIPText"           => "",
                            "Remark"            => "",
                            "AccountCode"       => ""
                        ];
                    }

                    if(!$isThatFlight) continue;
                    $flightData = $f;
                    
                    // Check prices
                    $totalAmout = 0;
                    if(isset($f["adtPrice"]) && isset($adtPrice[$i]) && $f["adtPrice"] != $adtPrice[$i]) {
                        $newFare    = $f["adtFare"] ?? null;
                        $newTaxFee  = $f["adtTaxFee"] ?? null;
                        $newPrice   = $f["adtPrice"] ?? null;
    
                        $updateData[$i]['adtFare'] = [
                            "detailId"  => $adtDetailId[$i] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $adt;
                    }
                    else $totalAmout += ($adtPrice[$i] ?? 0) * $adt;
                    if($chd > 0 && isset($f["chdPrice"]) && isset($chdPrice[$i]) && $f["chdPrice"] != $chdPrice[$i]) {
                        $newFare    = $f["chdFare"] ?? null;
                        $newTaxFee  = $f["chdTaxFee"] ?? null;
                        $newPrice   = $f["chdPrice"] ?? null;
    
                        $updateData[$i]['chdFare'] = [
                            "detailId"  => $chdDetailId[$i] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $chd;
                    }
                    else $totalAmout += ($chdPrice[$i] ?? 0) * $chd;
                    if($inf > 0 && isset($f["infPrice"]) && isset($infPrice[$i]) && $f["infPrice"] != $infPrice[$i]) {
                        $newFare    = $f["infFare"] ?? null;
                        $newTaxFee  = $f["infTaxFee"] ?? null;
                        $newPrice   = $f["infPrice"] ?? null;
    
                        $updateData[$i]['infFare'] = [
                            "detailId"  => $infDetailId[$i] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $inf;
                    }
                    else $totalAmout += ($infPrice[$i] ?? 0) * $inf;

                    // Add more info to update data itinerary id
                    if(isset($updateData[$i]) && !empty($updateData[$i])) {
                        $updateData[$i]['itineraryId'] = $listItineraryId[$i] ?? ''; // Itinerary Id
                        $updateData[$i]['totalAmount'] = $totalAmout; // Total amount
                    }
                    break;
                }
            }

            if(!empty($retDate)) { // Roundtrip
                if(count($standardData) > 1) {
                    if(empty($updateData)) {
                        return [
                            "status" => 1,
                            "message" => "Matched information",
                            "data" => [
                                "standardData" => $standardData,
                                "updateData" => $updateData
                            ],
                            "flights"=> $flightData ?? []
                        ];
                    }
                    else {
                        return [
                            "status" => 0,
                            "errorCode" => "UNMATCHED_INFO",
                            "message" => "Unmatched information",
                            "data" => [
                                "standardData" => $standardData,
                                "updateData" => $updateData
                            ],
                            "flights"=> $flightData ?? []
                        ];
                    }
                }
                elseif(count($standardData) == 1) {
                    $i = isset($standardData[1]) && !empty($standardData[1]) ? 0 : 1; 
                    return [
                        "status"    => 0,
                        "errorCode" => "NOT_FOUND_FLIGHT",
                        "message"   => trim("Không tìm thấy chuyến bay " . ($flightNo[$i] ?? '')),
                        "data" => [
                            "standardData" => $standardData,
                            "updateData" => $updateData
                        ],
                        'flights' => $arr['data'] ?? []
                    ];
                }
            }
            else if(!empty($standardData)) {
                if(empty($updateData)) {
                    return [
                        "status"    => 1,
                        "message"   => "Matched information",
                        "data" => [
                            "standardData" => $standardData,
                            "updateData" => $updateData
                        ],
                        'flights'=> $flightData ?? []
                    ];
                }
                else {
                    return [
                        "status"    => 0,
                        "errorCode" => "UNMATCHED_INFO",
                        "message"   => "Unmatched information",
                        "data" => [
                            "standardData" => $standardData,
                            "updateData" => $updateData
                        ],
                        'flights'=> $flightData ?? []
                    ];
                }
            }

            return [
                "status"    => 0,
                "errorCode" => "NOT_FOUND_FLIGHT",
                "message"   => trim("Không tìm thấy chuyến bay " . implode(", ", $flightNo)),
                "data"      => null,
                "flights"   => $arr['data'] ?? []
            ];
        }
        else {
            return [
                "status"    => 0,
                "errorCode" => "SEARCH_FAILED",
                "message"   => $arr['message'] ?? "Fail",
                "data"      => null,
                "response"  => $arr
            ];
        }
    }

    public function updateDataBooking($params = []) {
        $bookingId  = $params['bookingId'] ?? '';
        $direction  = $params['direction'] ?? null;
        $isInter    = (int)($params['isInter'] ?? 0);

        if(is_null($direction) || empty($bookingId)) {
            return [
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "params" => [
                    "bookingId" => $bookingId,
                    "direction" => $direction
                ]
            ];
        }

        global $db, $current_user;
        $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
        $sqlUpdate = '';

        if(!$isInter) { // Domestic
            // Update detail prices
            $basePrice = null;
            $passengerTypes = ['adt', 'chd', 'inf'];
            foreach($passengerTypes as $i => $type) {
                $k = $type . "Fare";
                if(isset($params[$k]) && !empty($params[$k])) {
                    $detailId = $params[$k]["detailId"];
                    $fare = $params[$k]["fare"];
                    $tax = $params[$k]["tax"];
                    // $fee    = $params[$k]["fee"];
                    // $vatFee = $fee * $this->vatPercentage;
                    // $price  = $params[$k]["price"];

                    // $sqlUpdate = "UPDATE ec_booking_details
                    //     SET unit_price = $fare
                    //         ,tax_and_fee = $tax
                    //         -- ,airport_fee        = IF($fee > admin_fee, $fee - admin_fee, 0)
                    //         -- ,admin_fee          = IF($fee > admin_fee, admin_fee, $fee)
                    //         -- ,vat_admin          = IF($fee > admin_fee, vat_admin, $vatFee)
                    //         -- ,admin_fee_no_vat   = IF($fee > admin_fee, admin_fee_no_vat, $fee - $vatFee)
                    //         -- ,total_bought_price = $price * quantity
                    //         -- ,total_price = ($price + service_fee) * quantity
                    //         ,total_bought_price = ($fare + $tax + airport_fee + admin_fee) * quantity
                    //         ,total_price = ($fare + $tax + airport_fee + admin_fee + service_fee) * quantity
                    //         ,modified_user_id = '$current_user->id'
                    //         ,date_modified = '$dateModified'
                    //     WHERE id = '$detailId'
                    //         AND booking_id = '$bookingId'
                    //         AND direction = '$direction'
                    //         AND passenger_type = '$i'
                    //         AND deleted = 0";

                    $sqlUpdate = "UPDATE ec_booking_details
                        SET unit_price = $fare
                            ,tax_and_fee = $tax
                            ,total_bought_price = ($fare + $tax + airport_fee + admin_fee) * quantity
                            ,total_price = ($fare + $tax + airport_fee + admin_fee + service_fee) * quantity
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE id = '$detailId'
                            AND booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND passenger_type = '$i'
                            AND deleted = 0";

                    if($type == 'adt') $basePrice = $fare;
                    if(!$db->query($sqlUpdate)) {
                        if($this->isDebug()) return [
                            "status" => 0,
                            "message" => "Cập nhật chi tiết vé không thành công, vui lòng thử lại",
                            "query" => trim($sqlUpdate)
                        ];
                        return [
                            "status" => 0,
                            "message" => "Cập nhật chi tiết vé không thành công, vui lòng thử lại",
                        ];
                    }
                }
            }

            // Update total number in booking (Don't update total amount)
            $total_bought_amount = $db->getOne("SELECT SUM(total_bought_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $subtotal_amount = $db->getOne("SELECT SUM(total_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $sqlUpdate = "UPDATE ec_flight_bookings
                    SET total_bought_amount = IF($total_bought_amount > 0, $total_bought_amount, total_bought_amount)
                        ,subtotal_amount = IF($subtotal_amount > 0, $subtotal_amount, subtotal_amount)
                    WHERE id = '$bookingId' AND deleted = 0";
            if(!$db->query($sqlUpdate)) {
                if($this->isDebug()) return [
                    "status" => 0,
                    "message" => "Cập nhật giá tổng không thành công, vui lòng thử lại",
                    "query" => trim($sqlUpdate)
                ];
                return [
                    "status" => 0,
                    "message" => "Cập nhật giá tổng không thành công, vui lòng thử lại",
                ];
            }

            $itiId = $params['itineraryId'] ?? '';

            // Update departure date (Should update into schedule flight time in itineraries table by details flight)
            if(isset($params['departureDate']) && !empty($params['departureDate'])) {
                $ddate = date('Y-m-d H:i:00', strtotime($params['departureDate']));
                $adate = date('Y-m-d H:i:00', strtotime($params['arrivalDate']));

                if(strtotime($ddate) > time() && strtotime($adate) > strtotime($ddate)) { // Check datetime is invalid
                    $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET departure_date = '$ddate'
                            ,arrival_date = '$adate'
                            ". (!is_null($basePrice) ? " ,base_price = $basePrice " : '') ."
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE id = '$itiId'
                            AND booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";

                    if(!$db->query($sqlUpdate)) {
                        if($this->isDebug()) return [
                            "status" => 0,
                            "message" => "Cập nhật ngày giờ bay không thành công, vui lòng thử lại",
                            "query" => trim($sqlUpdate)
                        ];
                        return [
                            "status" => 0,
                            "message" => "Cập nhật ngày giờ bay không thành công, vui lòng thử lại",
                        ];
                    }
                }
            }
            
            // Update base price
            if(!is_null($basePrice)) {
                $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET base_price = IF($basePrice <> base_price, $basePrice, base_price)
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE id = '$itiId'
                            AND booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";
                $db->query($sqlUpdate);
            }

            return ["status" => 1, "message" => "Update success"];
        }
        else { // International
            // Update detail prices
            $basePrice = null;
            $passengerTypes = ['adt', 'chd', 'inf'];
            foreach($passengerTypes as $i => $type) {
                $k = $type . "Fare";
                if(isset($params[$k]) && !empty($params[$k])) {
                    $detailId = $params[$k]["detailId"];
                    $fare   = $params[$k]["fare"];
                    $fee    = $params[$k]["fee"];
                    $price  = $params[$k]["price"];

                    $sqlUpdate = "UPDATE ec_booking_details
                        SET unit_price = $fare
                            ,admin_fee = $fee
                            ,total_bought_price = $price * quantity
                            ,total_price = ($price + service_fee) * quantity
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE id = '$detailId'
                            AND booking_id = '$bookingId'
                            AND passenger_type = '$i'
                            AND deleted = 0";

                    if($type == 'adt') $basePrice = $fare;
                    if(!$db->query($sqlUpdate)) {
                        if($this->isDebug()) return [
                            "status" => 0,
                            "message" => "Cập nhật chi tiết vé không thành công, vui lòng thử lại",
                            "query" => trim($sqlUpdate)
                        ];
                        return [
                            "status" => 0,
                            "message" => "Cập nhật chi tiết vé không thành công, vui lòng thử lại",
                        ];
                    }
                }
            }

            // Update total number in booking (Don't update total amount)
            $total_bought_amount = $db->getOne("SELECT SUM(total_bought_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $subtotal_amount = $db->getOne("SELECT SUM(total_price) FROM ec_booking_details WHERE booking_id = '$bookingId' AND deleted = 0") ?? 0;
            $sqlUpdate = "UPDATE ec_flight_bookings
                    SET total_bought_amount = IF($total_bought_amount > 0, $total_bought_amount, total_bought_amount)
                        ,subtotal_amount = IF($subtotal_amount > 0, $subtotal_amount, subtotal_amount)
                    WHERE id = '$bookingId' AND deleted = 0";
            if(!$db->query($sqlUpdate)) {
                if($this->isDebug()) return [
                    "status" => 0,
                    "message" => "Cập nhật giá tổng không thành công, vui lòng thử lại",
                    "query" => trim($sqlUpdate)
                ];
                return [
                    "status" => 0,
                    "message" => "Cập nhật giá tổng không thành công, vui lòng thử lại",
                ];
            }

            $itiId = $params['itineraryId'] ?? '';

            // // Update departure date (Should update into schedule flight time in itineraries table by details flight)
            // if(isset($params['departureDate']) && !empty($params['departureDate'])) {
            //     $ddate = date('Y-m-d H:i:00', strtotime($params['departureDate']));

            //     if(strtotime($ddate) > time()) {
            //         $sqlUpdate = "UPDATE ec_booking_itineraries
            //             SET departure_date = '$ddate'
            //                 ". (!is_null($basePrice) ? " ,base_price = $basePrice " : '') ."
            //                 ,modified_user_id = '$current_user->id'
            //                 ,date_modified = '$dateModified'
            //             WHERE id = '$itiId'
            //                 AND booking_id = '$bookingId'
            //                 AND direction = '$direction'
            //                 AND deleted = 0
            //                 AND add_type = 0";

            //         if(!$db->query($sqlUpdate)) {
            //             if($this->isDebug()) return [
            //                 "status" => 0,
            //                 "message" => "Cập nhật ngày giờ bay không thành công, vui lòng thử lại",
            //                 "query" => trim($sqlUpdate)
            //             ];
            //             return [
            //                 "status" => 0,
            //                 "message" => "Cập nhật ngày giờ bay không thành công, vui lòng thử lại",
            //             ];
            //         }
            //     }
            // }

            // Update base price
            if(!is_null($basePrice)) {
                $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET base_price = IF($basePrice <> base_price, $basePrice, base_price)
                            ,modified_user_id = '$current_user->id'
                            ,date_modified = '$dateModified'
                        WHERE id = '$itiId'
                            AND booking_id = '$bookingId'
                            AND direction = '$direction'
                            AND deleted = 0
                            AND add_type = 0";
                $db->query($sqlUpdate);
            }

            return ["status" => 1, "message" => "Update success"];
        }

        return ["status" => 0, "message" => "Nothing to update", "params" => $params];
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