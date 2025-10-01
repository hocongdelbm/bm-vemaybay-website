<?php
require_once "custom/entrypoints/entryAuthClass/entryClass.php";
require_once "custom/include/helpers/api/APIPhuongNam.php";

/**
 * Class entryAutoBookPhuongNamClass
 * 
 * Using for booking by Phuong Nam API
 */
class entryAutoBookPhuongNamClass extends entryClass {
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
            'VU' => 'Vietravel Airlines',
            '9G' => 'Sun PhuQuoc Airways',
        ];
        $this->mappingSystemCode = [
            'VJA' => 'VJ',
            'VNA' => 'VN',
            'VNP' => 'VN',
            'BBA' => 'QH',
            'VTA' => 'VU'
        ];
        // BM database
        $this->supplierId = "7eafb1bc-6ac2-3816-3ea9-6455f638436e";
        $this->supplierCode = "PHUONGNAM";
        $this->supplierName = "NCC Phương Nam";
    }

    /**
     * Get data from database to auto book
     * 
     * @param array $params
     * @return array
     */
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
                    ,iti.departure_date
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
                            'airlineCode'   => $this->mappingSystemCode[$row['airline_code']] ?? $row['airline_code'],
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
                            'airlineCode'   => $this->mappingSystemCode[$row['airline_code']] ?? $row['airline_code'],
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
                        'dateOfBirth' => !is_null($row['birthday']) && !empty($row['birthday']) ? date('d-m-Y', strtotime($row['birthday'])) : '',
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

                    $qty = 0;
                    switch ($row['passenger_type']) {
                        case '2':
                            $qty = $infCount;
                            break;
                        case '1':
                            $qty = $chdCount;
                            break;
                        case '0':
                        default:
                            $qty = $adtCount;
                            break;
                    }

                    $direction_name = $row['direction'] == '1' ? 'ret' : 'dep';
                    $dataFareDetails[$direction_name][$row['passenger_type']] = [
                        'id' => $row['id'],
                        'fare'  => (int)$row['fare'],
                        'tax'   => (int)$row['tax'],
                        'fee'   => (int)$row['fee'],
                        'price' => $row['fare'] + $row['tax'] + $row['fee'],
                        'qty'   => $qty,
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

    /**
     * Research data before booking
     * 
     * @param array $params
     * @return array
     */
    public function research($params = []) {
        $airlineCode    = $params['airlineCode'] ?? '';
        $depCode        = $params['depCode'] ?? '';
        $desCode        = $params['desCode'] ?? '';
        $depDate        = $params['depDate'] ?? ''; // d-m-Y H:i
        $retDate        = $params['retDate'] ?? ''; // d-m-Y H:i
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

        $agency = new APIPhuongNam();

        // Format search params
        $airlineCodeSearch = $airlineCode;
        if($isInter) {
            if(isset($this->mappingSystemCodeName[$airlineCode])) $airlineCodeSearch = $airlineCode;
            else $airlineCodeSearch = $this->interSystemCode;
        }

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
                foreach($arr['data'] as $roundName => $round) {
                    $i = $roundName == 'ret' ? 1 : 0;
                    $totalAmout = 0;
                    foreach($round as $f) {
                        if(!isset($f["transactionID"]) || $f["flightNo"] != ($flightNo[$i] ?? '')) continue;

                        // Standard data for automatic booking in the next step
                        $standardData[$i] = [
                            "SystemCode" => $f['airlineCode'],
                            "TransactionId" => $f['transactionID'] ?? null,
                            "FlightNumber" => preg_replace('/\D/', '', $f['flightNo']),
                            "FarePricings" => [
                                [
                                    "FareBasis" => $f['fareBasis'],
                                    "PassengerTypeId" => 1,
                                    "FareSumAmount" => $f["adtPrice"] * $adt
                                ]
                            ],
                        ];
                        if($chd > 0) {
                            $standardData[$i]["FarePricings"][] = [
                                "FareBasis" => $f['fareBasis'],
                                "PassengerTypeId" => 6,
                                "FareSumAmount" => $f["chdPrice"] * $chd
                            ];
                        }
                        if($inf > 0) {
                            $standardData[$i]["FarePricings"][] = [
                                "FareBasis" => $f['fareBasis'],
                                "PassengerTypeId" => 5,
                                "FareSumAmount" => $f["infPrice"] * $inf
                            ];
                        }

                        // Check schedule
                        $flightDate = $i == 1 ? $retDate : $depDate;
                        if(date('Y-m-d H:i', strtotime($flightDate)) != ($f['depDate'] . ' ' .$f['depTime'])) {
                            // Using for displaying UI
                            $updateData[$i]['departureDate'] = date('d-m-Y H:i', strtotime($f['depDate'] . ' ' . $f['depTime']));
                            // Using for updating in DB
                            $updateData[$i]['segments'] = $this->getUpdatedFieldInSegments($f['details']);
                            $updateData[$i]['transits'] = $f['transits'];
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
                                "price"     => $f["chdPrice"]
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
            else {  // International
                return [
                    "status"    => 0,
                    "errorCode" => "SEARCH_FAILED",
                    "message"   => "Vé quốc tế chưa sẵn sàng cho NCC Phương Nam",
                    "data"      => null,
                ];
            }

            if(!empty($retDate)) { // Roundtrip
                if(count($standardData) > 1 || ($isInter && count($standardData) == 1)) {
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
                "message"   => $isInter && !empty($retDate)
                    ? trim("Không tìm thấy cặp chuyến " . implode(", ", $flightNo))
                    : trim("Không tìm thấy chuyến bay " . implode(", ", $flightNo)),
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

    /**
     * Update new data to booking in one round
     * 
     * @param array $params [bookingId, direction, isInter,...]
     * @return array
     */
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

        global $db;
        $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);
        $sqlUpdate = '';

        if(!$isInter) { // Domestic
            /**
             * Procedures & Rules
             * - Parameters note ($params structure):
             *  {
             *      "bookingId": "string",
             *      "direction": number,
             *      "isInter": number,
             *      "adtFare": {
             *          "detailId": "string",
             *          "fare": number,
             *          "fee": number,
             *          "price": number
             *      },
             *      "chdFare": ...,
             *      "infFare": ...,
             *      "itineraryId": "string",
             *      "segments": [...],
             *      "transits": [...]
             *  }
             */

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
                    //         ,modified_user_id = '{$this->currentUser->id}'
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
                            ,modified_user_id = '{$this->currentUser->id}'
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
                        ,modified_user_id = '{$this->currentUser->id}'
                        ,date_modified = '$dateModified'
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

            // Update flight schedule
            if(isset($params['segments']) && !empty($params['segments'])) {
                $sql = "SELECT id
                    FROM ec_booking_itineraries
                    WHERE booking_id = '$bookingId'
                        AND direction = '$direction'
                        AND deleted = 0
                        AND add_type = 0
                    ORDER BY transit_order, departure_date";

                $i = 0;
                $res = $db->query($sql);
                while ($row = $db->fetchByAssoc($res)) {
                    $rowId = $row['id'] ?? '';

                    $segDep         = $params['segments'][$i]['dep'] ?? '';
                    $segDes         = $params['segments'][$i]['des'] ?? '';
                    $segDepDateTime = ($params['segments'][$i]['depDate'] ?? '') . ' ' . ($params['segments'][$i]['depTime'] ?? '') . ':00'; // Y-m-d H:i:s
                    $segArvDateTime = ($params['segments'][$i]['arvDate'] ?? '') . ' ' . ($params['segments'][$i]['arvTime'] ?? '') . ':00'; // Y-m-d H:i:s
                    $segFlightNo    = trim($params['segments'][$i]['flightNo'] ?? '');
                    
                    // Get transit info
                    $transit = '';
                    if(isset($params['transits'][$i]) && !empty($params['transits'][$i])) {
                        $transit = 'Trung chuyển tại: ' . $params['transits'][$i]['station'] ?? '';
                        $transit .= ' - Thời gian dừng: ' . $params['transits'][$i]['nDuration'] ?? '';
                    }
                    $transit = trim($transit);
                
                    $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET departure = '$segDep'
                            ,arrival = '$segDes'
                            ,departure_date = '$segDepDateTime'
                            ,arrival_date = '$segArvDateTime'
                            ,description = IF(LENGTH('$transit') > 0, '$transit', description)
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = '$dateModified'
                        WHERE id = '$rowId' AND TRIM(flight_number) = '$segFlightNo'";
                    $db->query($sqlUpdate);

                    $i++;
                }
            }
            
            // Update base price
            if(!is_null($basePrice)) {
                $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET base_price = IF($basePrice <> base_price, $basePrice, base_price)
                            ,modified_user_id = '{$this->currentUser->id}'
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
            return [
                "status"    => 0,
                "message"   => "Vé quốc tế chưa sẵn sàng cho NCC Phương Nam",
            ];
        }

        return ["status" => 0, "message" => "Nothing to update", "params" => $params];
    }

    /**
     * Verify flight info
     * 
     * @param array $params
     * @return array
     */
    public function verify($params = []) {
        $bookingId = $params['bookingId'] ?? '';
        $flights = $params['flights'] ?? [];
        $contact = $params['contact'] ?? [];
        $listPassenger = $params['listPassenger'] ?? [];
        $isWithin24h = isset($params['isWithin24h']) ? (int)$params['isWithin24h'] : 0;

        if(!$flights || !is_array($flights) || empty($flights) 
            || empty($bookingId) 
            || !is_array($listPassenger) || empty($listPassenger)
            || !is_array($contact) || empty($contact)
        ) {
            return [
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "params" => [
                    "bookingId" => $bookingId,
                    "flights"   => $flights,
                    "contact"   => $contact,
                    "listPassenger" => $listPassenger,
                ]
            ];
        }
        
        $phuongnamapi = new APIPhuongNam();

        // Get airline codes
        $airlineCodes = [];
        foreach($flights as $f) $airlineCodes[] = $f['SystemCode'];

        if($isWithin24h === 1 && count(array_unique($airlineCodes)) === 2) {
            return [
                "status" => 0,
                "message" => "Vé cận phải giữ chung 1 hãng",
            ];
            exit();
        }
        // The other domestic airlines allow close-in ticket holds
        if($isWithin24h === 1 && $airlineCodes[0] != 'VJ') $isWithin24h === 0;

        // Contact info
        $contactRequiredFields = [
            "Title" => "Danh xưng liên hệ (Mr/Ms)",
            "Name" => "Họ tên người liên hệ",
            "Phone" => "Số điện thoại liên hệ",
            "Email" => "Email liên hệ (Email đặt chỗ)",
            "Address" => "Địa chỉ liên hệ",
        ];
        foreach($contactRequiredFields as $key => $name) {
            if(!isset($contact[$key]) || empty($contact[$key])) {
                return ["status" => 0, "message" => "$name là bắt buộc"];
            }
            elseif($key == "Address" && in_array('VJ', $airlineCodes) && strlen($contact['Address']) > 50) {
                return ["status" => 0, "message" => "Vietjet địa chỉ liên hệ tối đa 50 ký tự"];
            }
        }

        // Passengers info
        $customerInfos = [];
        foreach($listPassenger as $num => $pass) {
            $birthday = isset($pass['BirthDay']) && !empty($pass['BirthDay']) && strtotime($pass['BirthDay']) ? $pass['BirthDay'] : null;
            if($birthday) $birthday = date('Y-m-d', strtotime(str_replace("/", "-", $birthday)));

            if(in_array('QH', $airlineCodes) && $pass['PassengerTypeId'] === 5) {
                $pass['FirstName'] = $phuongnamapi->getOnlyFirstName($pass['FirstName'] ?? '');
            }

            $customerInfos[$num] = $pass;
            $customerInfos[$num]["BirthDay"] = $birthday;
            $customerInfos[$num]["Age"] = $phuongnamapi->getAge($birthday);
            $customerInfos[$num]["PersonOrgIdConfirmed"] = null;
            $customerInfos[$num]["PersonOrgCode"] = null;
            $customerInfos[$num]["CustomerKey"] = null;
            $customerInfos[$num]["AddressFull"] = null;
            $customerInfos[$num]["IsContract"] = true;
            $customerInfos[$num]["PassportType"] = null;
            $customerInfos[$num]["PassportCode"] = null;
            $customerInfos[$num]["PassportIssuer"] = null;
            $customerInfos[$num]["PassportExpired"] = null;
            $customerInfos[$num]["Nationality"] = null;
            $customerInfos[$num]["ParentGuestIdConfirmed"] = null; // QH uses
            $customerInfos[$num]["ParentGuestCode"] = null; // VJ uses
            $customerInfos[$num]["LoyaltyNumber"] = null;
        }
        
        $requestBody = [
            "UserId" => null,
            "UserCode" => null,
            "UserFullName" => null,
            "TransactionId" => null,
            "IsIssueTicket" => (bool)$isWithin24h, // Issue immediately
            "Itinerary" => count($flights), // 1:Một chiều 2:Khứ hồi, 3:Đa chặng
            "ContactTitle" => $contact['Title'],
            "ContactName" => $contact['Name'],
            "ContactPhone" => $contact['Phone'],
            "ContactEmail" => $contact['Email'],
            "ContactAddress" => $contact['Address'],
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
            if(count(array_unique($airlineCodes)) === 1 && $airlineCodes[0] === 'VN' && count($flights) == 2) {
                $sessionVerify = $responseData[0]['SessionVerify'] ?? '';
                $verifyData = $responseData[0]['VerifyData'] ?? [];

                if(!empty($sessionVerify) && is_array($verifyData) && !empty($verifyData)) {
                    foreach($requestBody['Flights'] as $i => $f) {
                        $requestBody['Flights'][$i]['VerifySession'] = $sessionVerify;
                    }
                }
                else {
                    $isVerifyFailed = false;
                    $verifyFailedMessage = $responseData[0]['Message'] ?? '';
                }
            }
            else {
                foreach($requestBody['Flights'] as $i => $f) {
                    foreach(($responseData ?? []) as $res) {
                        if(!isset($res['SessionVerify']) || !$res['SessionVerify'] || empty($res['SessionVerify'])) {
                            $isVerifyFailed = false;
                            $verifyFailedMessage = $res['Message'] ?? '';
                            break;
                        }

                        if($f['SystemCode'] == $res['SystemCode']) {
                            $requestBody['Flights'][$i]['VerifySession'] = $res['SessionVerify'] ?? '';
                        }

                        // Bamboo has to update request body into verify data
                        if(in_array('QH', $airlineCodes) && isset($res['VerifyData']) && !empty($res['VerifyData'])) {
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

    /**
     * Create booking
     * 
     * @param array $params
     * @return array
     */
    public function booking($params = []) {
        $bookingId          = $params['bookingId'] ?? '';
        $listPassengerId    = $params['listPassengerId'] ?? [];
        $listItineraryId    = $params['listItineraryId'] ?? [];
        $listDetailId       = $params['listDetailId'] ?? [];
        $requestBody        = $params['requestBody'] ?? [];
        
        if(!$requestBody || !is_array($requestBody) || empty($requestBody) || empty($bookingId)
            || !is_array($listPassengerId) || empty($listPassengerId)
            || !is_array($listItineraryId) || empty($listItineraryId)
            || !is_array($listDetailId) || empty($listDetailId)
        ) {
            return [
                "status" => 0,
                "message" => "Dữ liệu không hợp lệ",
                "params" => [
                    "requestBody" => $requestBody,
                    "listPassengerId" => $listPassengerId,
                    "listItineraryId" => $listItineraryId,
                    "listDetailId" => $listDetailId,
                    "bookingId" => $bookingId,
                ]
            ];
        }

        // Booking type: oneway (Một chiều), roundtrip (Khứ hồi cùng hãng) , twoway (Khứ hồi 2 hãng khác nhau)
        $bookingType = null;
        $countItinerary = count($requestBody['Flights']);
        if($countItinerary == 1) $bookingType = 'oneway';
        elseif($countItinerary == 2) {
            if($requestBody['Flights'][0]['SystemCode'] != $requestBody['Flights'][1]['SystemCode']) $bookingType = 'twoway';
            else $bookingType = 'roundtrip';
        }

        $agency  = new APIPhuongNam();
        $response = $agency->book($requestBody);
        $responseArr = json_decode($response, true);

        // Save to BM
        if(isset($responseArr['status']) && $responseArr['status'] == 1) {
            global $db;
            $booking = new EC_Flight_Bookings();
            $booking->retrieve($bookingId);

            $inListPassengerId  = "'".implode("','", $listPassengerId)."'";
            $inListItineraryId  = "'".implode("','", $listItineraryId)."'";
            $inListDetailId     = "'".implode("','", $listDetailId)."'";

            foreach(($responseArr["data"] ?? []) as $i => $f) {
                if(isset($f["ID"]) && $f["ID"] == 1) {
                    $bookingCode = explode(":", $f["BookingCode"]); // "VJ: XUBK2G"
                    $systemCode = trim($bookingCode[0] ?? ''); // System code
                    $systemName = $this->mappingSystemCodeName[$systemCode] ?? "Quốc tế $systemCode";

                    $pnr = trim($bookingCode[1] ?? '');
                    $dateModified = date('Y-m-d H:i:s', time() - 7*60*60);

                    // Send notification
                    try {
                        $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                        $linkBooking = "https://{$this->domain}/index.php?module=EC_Flight_Bookings&action=DetailView&record=$bookingId";

                        if($this->notificationChannel == 'Mattermost') {
                            $link = Mattermost::markdownLink($linkBooking, $pnr);
                            
                            $m = "Giữ chỗ $systemName: $link bởi **$fullname**";
                            if(isset($requestBody['IsIssueTicket']) && $requestBody['IsIssueTicket'] == true) $m = "**Xuất vé cận $systemName: $pnr bởi $fullname**";
                            if(isset($f["TransactionId"]) && !empty($f["TransactionId"])) $m .= "\n- Transaction ID: " . $f["TransactionId"];
                            $m .= "\nNCC: **{$this->supplierName}**";

                            Mattermost::sendMessage($this->mattermostConfig['channel_id_api_phuong_nam'] ?? '', $m);
                        }
                        else {
                            $link = "<a href=\"".$linkBooking."\">$pnr</a>";

                            $m = "Giữ chỗ $systemName: $link bởi <b>$fullname</b>";
                            if(isset($requestBody['IsIssueTicket']) && $requestBody['IsIssueTicket'] === true) $m = "<b>💰 Xuất vé cận $systemName: $link bởi $fullname</b>";
                            if(isset($f["TransactionId"]) && !empty($f["TransactionId"])) $m .= "\n<i>Transaction ID: ". ($f["TransactionId"]) ."</i>";
                            $m .= "\nNCC: <b>{$this->supplierName}</b>";

                            $botToken   = $this->telegramConfig['autobook']['bot_token'] ?? '';
                            $chatId     = $this->telegramConfig['autobook']['chat_id'] ?? '';
                            Telegram::sendMessage($m, $botToken, $chatId);
                        }
                    }
                    catch(Throwable $th) {}

                    if($bookingType == 'roundtrip') {
                        // Update PNR
                        $sqlUpdate = "UPDATE ec_booking_passengers
                                SET pnr_outbound = '$pnr'
                                    ,pnr_inbound = '$pnr'
                                    ,modified_user_id = '{$this->currentUser->id}'
                                    ,date_modified = '$dateModified'
                                WHERE id IN ($inListPassengerId) 
                                    AND booking_id = '$bookingId'
                                    AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                        // Update supplier
                        $ticketing_fee = $systemCode == 'VJ' ? 5000 : 0;
                        $sqlUpdate = "UPDATE ec_booking_details
                                SET supplier_id = '{$this->supplierId}'
                                    ,fee_bought = IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                    ,total_bought_price = total_bought_price + IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                    ,modified_user_id = '{$this->currentUser->id}'
                                    ,date_modified = '$dateModified'
                                WHERE id IN ($inListDetailId) 
                                    AND booking_id = '$bookingId'
                                    AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    }
                    else {
                        $airlineCodeOutbound = $db->getOne("SELECT airline FROM ec_flight_bookings WHERE id = '$bookingId' AND deleted = 0") ?? '';
                        if($systemCode == ($this->mappingSystemCode[$airlineCodeOutbound] ?? '')) {
                            $colNamePNR = 'pnr_outbound';
                            // $colNameLugIndex = 'luggage_index_outbound';
                            $direction = '0';
                        }
                        else {
                            $colNamePNR = 'pnr_inbound';
                            // $colNameLugIndex = 'luggage_index_inbound';
                            $direction = '1';
                        }

                        // $fareBasic = $requestBody['Flights'][$i]['FarePricings'][0]['FareBasis'] ?? '';
                        // $fareClass = FareClass::getFareClass($systemCode, $fareBasic);
                        // $bagIndex  = Baggage::getAvailableCheckedBaggageInfo($systemCode, $fareClass, 'ADT');

                        // Update PNR
                        $sqlUpdate = "UPDATE ec_booking_passengers
                                SET $colNamePNR = '$pnr'
                                    ,modified_user_id = '{$this->currentUser->id}'
                                    ,date_modified = '$dateModified'
                                WHERE booking_id = '$bookingId'
                                    AND id IN ($inListPassengerId)
                                    AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                        // Update supplier
                        $ticketing_fee = $systemCode == 'VJ' ? 5000 : 0;
                        $sqlUpdate = "UPDATE ec_booking_details
                                SET supplier_id = '{$this->supplierId}'
                                    ,fee_bought = IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                    ,total_bought_price = total_bought_price + IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                    ,modified_user_id = '{$this->currentUser->id}'
                                    ,date_modified = '$dateModified'
                                WHERE id IN ($inListDetailId) 
                                    AND booking_id = '$bookingId'
                                    AND direction = '$direction'
                                    AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    }
                }
            }
        }
        else {
            $botToken   = $this->telegramConfig['bot_token'] ?? '';
            $chatId     = $this->telegramConfig['chat_id'] ?? '';
            $threadId   = $this->telegramConfig['thread_id_logs'] ?? '';
            Telegram::sendMessageData($response, $botToken, $chatId, $threadId);
        }
        
        return $responseArr;
    }

    /**
     * Get booking data
     * 
     * @param array $params
     * @return array
     */
    public function getBooking($params = []) {
        $pnr = trim($params['pnr'] ?? '');
        $systemCode = $params['systemCode'] ?? '';
        $airlineCode = $params['airlineCode'] ?? '';

        $agency = new APIPhuongNam();
        $jsonBooking = $agency->getBooking($pnr, $systemCode, $airlineCode);
        $arrBooking = json_decode($jsonBooking, true);
        if(isset($arrBooking["status"]) && $arrBooking["status"] == 1) {
            $supplier = strtolower($arrBooking['supplier'] ?? '');
            if($supplier == 'datacom') {
                require_once "custom/include/helpers/api/APIDatacom.php";
                $datacomAgency = new APIDatacom();
                $arrBooking['data'] = $datacomAgency->standardizeBookingData($arrBooking['data']);
                $arrBooking['data']['EntryClass'] = "entryAutoBookDatacomClass";
                $arrBooking['data']['Supplier'] = "Hồng Ngọc Hà 218";
            }
            else {
                $arrBooking['data'] = $agency->standardizeBookingData($arrBooking['data']);
                $arrBooking['data']['EntryClass'] = __CLASS__;
                $arrBooking['data']['Supplier'] = $this->supplierName;
            }
        }
        return $arrBooking;
    }

    /**
     * Get baggage info in booking
     * 
     * @param array $params
     * @return array
     */
    public function getBaggageInfo($params = []) {
        $bookingCode    = $params['bookingCode'] ?? ''; // PNR
        $bookingId      = $params['bookingId'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';
        $direction      = (int)($params['direction'] ?? 0);
        $passengerInfo  = $params['passengerInfo'] ?? []; // Info who purchase baggage

        if(!empty($passengerInfo)) {
            $agency = new APIPhuongNam();
            $json = $agency->getBaggageInfo($bookingCode, $systemCode);
            $arr = json_decode($json, true);

            if(isset($arr["status"]) && $arr["status"] == 1) {
                $data = [];
                $data["ListBaggage"] = $agency->standardizeListBaggageData($arr["data"], $passengerInfo, $direction);
                $data["Origin"]      = $arr["data"][$direction]["Origin"];
                $data["Destination"] = $arr["data"][$direction]["Destination"];
                return [
                    "status" => 1,
                    "message" => $arr["message"] ?? "",
                    "data" => $data
                ];
            }

            return $arr;
        }

        return [
            "status" => 0,
            "message" => "Thiếu thông tin của hành khách mua hành lý",
            "data" => null
        ];
    }

    /**
     * Pay for booking
     * 
     * @param array $params
     * @return array
     */
    public function payBooking($params = []) {
        $bookingCode = $params['bookingCode'] ?? '';
        $systemCode  = $params['systemCode'] ?? '';

        $agency = new APIPhuongNam();
        $response = $agency->payBooking($bookingCode, $systemCode);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;

                if($this->notificationChannel == 'Mattermost') {
                    $m = "**Xuất vé $systemName: $bookingCode bởi $fullname**";
                    $m .= "\n- NCC: **{$this->supplierName}**";
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_api_phuong_nam'] ?? '', $m);
                }
                else {
                    $m = "<b>💰 Xuất vé $systemName: $bookingCode bởi $fullname</b>";
                    $m .= "\nNCC: <b>{$this->supplierName}</b>";
                    $botToken   = $this->telegramConfig['autobook']['bot_token'] ?? '';
                    $chatId     = $this->telegramConfig['autobook']['chat_id'] ?? '';
                    Telegram::sendMessage($m, $botToken, $chatId);
                } 
            }
        }
        catch(Throwable $th) {}

        return $responseArr;
    }

    /**
     * Add baggage
     * 
     * @param array $params
     * @return array
     */
    public function addBaggage($params = []) {
        $bookingCode    = $params['bookingCode'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';
        $airlineCode    = $params['airlineCode'] ?? '';
        $baggageData    = $params['baggageData'] ?? [];
        $passengerData  = $params['passengerData'] ?? [];
        $direction      = (int)($params['direction'] ?? 0);

        $agency  = new APIPhuongNam();
        $response = $agency->addBaggage($bookingCode, $systemCode, $baggageData['Value'] ?? []);
        $responseArr = json_decode($response, true);

        if(isset($responseArr['status']) && $responseArr['status'] == 1) {
            try {
                global $db;
                $bagDescription = $baggageData["Description"] ?? "";
                if(!empty($bagDescription)) $bagDescription = $baggageData["Name"] ?? "";

                $bagAmount      = $baggageData["Amount"] ?? "";
                $bagVat         = $baggageData["VAT"] ?? "";
                $bagTotalAmount = $baggageData["TotalAmount"] ?? 0;
                $bagDescription = $baggageData["Description"] ?? "";
                $passengerName  = trim($passengerData['LastName'] . ' ' . $passengerData['FirstName']);
                $dateModified   = date('Y-m-d H:i:s', time() - 7*60*60);
                $suffix         = $direction === 1 ? "_inbound" : "";
                $pnrField       = $direction === 1 ? "pnr_inbound" : "pnr_outbound";

                $sqlUpdate = "UPDATE ec_booking_passengers
                    SET luggage_purchase_text{$suffix}      = '$bagDescription'
                        ,luggage_purchase{$suffix}          = $bagTotalAmount
                        ,vat_luggage_purchase{$suffix}      = $bagVat
                        ,luggage_purchase{$suffix}_no_vat   = $bagAmount
                        ,supplier{$suffix}_id               = '{$this->supplierId}'
                        ,modified_user_id                   = '{$this->currentUser->id}'
                        ,date_modified                      = '$dateModified'
                    WHERE $pnrField = '$bookingCode'
                        AND name = '$passengerName'
                        AND (luggage_purchase$suffix IS NULL OR luggage_purchase$suffix = 0)
                        AND date_entered >= NOW() - INTERVAL 120 DAY;
                        AND deleted = 0";
                if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
            }
            catch(Throwable $th) {}
        }

        return $responseArr;
    }

    /**
     * Cancel booking
     * 
     * @param array $params
     * @return array
     */
    public function cancelBooking($params = []) {
        $bookingCode    = $params['bookingCode'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';

        $agency = new APIPhuongNam();
        $response = $agency->cancelBooking($bookingCode, $systemCode);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;

                if($this->notificationChannel == 'Mattermost') {
                    $m = "**Hủy giữ chỗ $bookingCode ($systemName) bởi $fullname**";
                    $m .= "\n- NCC: **{$this->supplierName}**";
                    Mattermost::sendMessage($sugar_config['mattermost']['channel_id_api_phuong_nam'] ?? '', $m);
                }
                else {
                    $m = "<b>Hủy giữ chỗ $bookingCode ($systemName) bởi $fullname</b>";
                    $m .= "\nNCC: <b>{$this->supplierName}</b>";
                    $botToken   = $this->telegramConfig['autobook']['bot_token'] ?? '';
                    $chatId     = $this->telegramConfig['autobook']['chat_id'] ?? '';
                    Telegram::sendMessage($m, $botToken, $chatId);
                }
            }
        }
        catch(Throwable $th) {}

        return $responseArr;
    }

    /**
     * Get updated field in segments
     * 
     * @param array $segments Details in flight
     * @return array
     */
    protected function getUpdatedFieldInSegments($segments) {
        if(is_array($segments) && !empty($segments)) {
            $fields = [
                'dep', 'depDate', 'depTime',
                'des', 'arvDate', 'arvTime',
                'carrierCode',
                'flightNo',
                'ticketClass',
            ];

            $result = [];
            foreach($segments as $i => $seg) {
                foreach($fields as $field) {
                    $result[$i][$field] = $seg[$field] ?? '';
                }
            }
            return $result;
        }
        return [];
    }
}