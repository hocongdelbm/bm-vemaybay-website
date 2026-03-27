<?php
require_once "custom/entrypoints/entryClass.php";
require_once "custom/include/helpers/api/APIDatacom.php";

use custom\services\Notification\NotificationService;

/**
 * Class entryAutoBookDatacomClass
 * 
 * Using for booking by Datacom API
 */
class entryAutoBookDatacomClass extends entryClass {
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
            '1A' => 'Amadeus',
            '1G' => 'Galileo',
            'TR' => 'Scoot',
            'AK' => 'AirAsia',
            'FO' => 'Flyone',
        ];
        $this->mappingSystemCode = [
            'VJA' => 'VJ',
            'VNA' => 'VN',
            'VNP' => 'VN',
            'BBA' => 'QH',
            'VTA' => 'VU',
            '9G' => '9G'
        ];
        // BM database
        $this->supplierId = "ebdf163a-7b85-30bf-62be-5a4af5a1166c";
        $this->supplierCode = "VNAHNH";
        $this->supplierName = "Hồng Ngọc Hà 218";
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

            if(isset($dataItineraries['dep']) && isset($dataItineraries['ret'])
                && $dataItineraries['dep']['airlineCode'] != $dataItineraries['ret']['airlineCode']
                && ($dataItineraries['dep']['airlineCode'] == '9G' || $dataItineraries['ret']['airlineCode'] == '9G')
            ) {
                return ["status" => 0, "message" => "Hãng Sun PhuQuoc phải autobook riêng"];
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
            $dataContact['email'] = !empty($booking->email_reservation) ? $booking->email_reservation : 'info@timchuyenbay.com';
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

        $agency = new APIDatacom();

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
                                "price"     => $newPrice,
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
                }
            }
            else { // International
                foreach($arr['data'] as $f) {
                    if(!isset($f["sessionId"])) continue;

                    // Check flight
                    $isThatFlight = false;
                    if(!empty($retDate) && isset($f['ret']) && !empty($f['ret'])) { // Roundtrip
                        if($f['dep']['flightNo'] == ($flightNo[0] ?? '') && $f['ret']['flightNo'] == ($flightNo[1] ?? '')) {
                            $isThatFlight = true;

                            if(isset($f["airlineId"])) { // Combine
                                $standardData = [[
                                    "Session"           => $f["sessionId"],
                                    "SessionType"       => "search", // Hard code
                                    "AirlineOptionId"   => $f["airlineId"] ?? null,
                                    "FareOptionId"      => $f["fareId"] ?? null,
                                    "FlightOptionId"    => $f["flightId"] ?? null,
                                    "Tourcode"          => "",
                                    "CAcode"            => "",
                                    "VIPText"           => "",
                                    "Remark"            => "",
                                    "AccountCode"       => ""
                                ]];
                            }
                            else { // Domestic airline like VJ, VN,...
                                $standardData = [
                                    [
                                        "Session"           => $f["sessionId"],
                                        "SessionType"       => "search", // Hard code
                                        "AirlineOptionId"   => $f["dep"]["airlineId"] ?? null,
                                        "FareOptionId"      => $f["dep"]["fareId"] ?? null,
                                        "FlightOptionId"    => $f["dep"]["flightId"] ?? null,
                                        "Tourcode"          => "",
                                        "CAcode"            => "",
                                        "VIPText"           => "",
                                        "Remark"            => "",
                                        "AccountCode"       => ""
                                    ],
                                    [
                                        "Session"           => $f["sessionId"],
                                        "SessionType"       => "search", // Hard code
                                        "AirlineOptionId"   => $f["ret"]["airlineId"] ?? null,
                                        "FareOptionId"      => $f["ret"]["fareId"] ?? null,
                                        "FlightOptionId"    => $f["ret"]["flightId"] ?? null,
                                        "Tourcode"          => "",
                                        "CAcode"            => "",
                                        "VIPText"           => "",
                                        "Remark"            => "",
                                        "AccountCode"       => ""
                                    ],
                                ];
                            }
                        }
                    }
                    else if(isset($f['dep']) && !empty($f['dep'])) { // Oneway
                        if($f['dep']['flightNo'] == ($flightNo[0] ?? '')) {
                            $isThatFlight = true;

                            if(isset($f["airlineId"])) { // Combine
                                $standardData = [[
                                    "Session"           => $f["sessionId"],
                                    "SessionType"       => "search", // Hard code
                                    "AirlineOptionId"   => $f["airlineId"] ?? null,
                                    "FareOptionId"      => $f["fareId"] ?? null,
                                    "FlightOptionId"    => $f["flightId"] ?? null,
                                    "Tourcode"          => "",
                                    "CAcode"            => "",
                                    "VIPText"           => "",
                                    "Remark"            => "",
                                    "AccountCode"       => ""
                                ]];
                            }
                            else { // Domestic airline like VJ, VN,...
                                $standardData = [[
                                    "Session"           => $f["sessionId"],
                                    "SessionType"       => "search", // Hard code
                                    "AirlineOptionId"   => $f["dep"]["airlineId"] ?? null,
                                    "FareOptionId"      => $f["dep"]["fareId"] ?? null,
                                    "FlightOptionId"    => $f["dep"]["flightId"] ?? null,
                                    "Tourcode"          => "",
                                    "CAcode"            => "",
                                    "VIPText"           => "",
                                    "Remark"            => "",
                                    "AccountCode"       => ""
                                ]];
                            }
                        }
                    }
                    if(!$isThatFlight) continue;

                    $flightData = $f;

                    // Check departure schedule
                    if(date('Y-m-d H:i', strtotime($depDate)) != $f['dep']['depDate'] . ' ' . $f['dep']['depTime']) {
                        // Using for displaying UI
                        $updateData[0]['departureDate'] = date('d-m-Y H:i', strtotime($f['dep']['depDate'] . ' ' . $f['dep']['depTime']));
                        // Using for updating in DB
                        $updateData[0]['itinerary']['dep']['segments'] = $this->getUpdatedFieldInSegments($f['dep']['details']);
                        $updateData[0]['itinerary']['dep']['transits'] = $f['dep']['transits'];
                    }
                    // Check return schedule
                    if(!empty($retDate) && date('Y-m-d H:i', strtotime($retDate)) != $f['ret']['depDate'] . ' ' . $f['ret']['depTime']) {
                        // Using for displaying UI
                        $updateData[1]['departureDate'] = date('d-m-Y H:i', strtotime($f['ret']['depDate'] . ' ' . $f['ret']['depTime']));
                        // Using for updating in DB
                        $updateData[0]['itineraryId']['ret'] = $listItineraryId[1] ?? ''; // Itinerary Id
                        $updateData[0]['itinerary']['ret']['segments'] = $this->getUpdatedFieldInSegments($f['ret']['details']);
                        $updateData[0]['itinerary']['ret']['transits'] = $f['ret']['transits'];
                    }
                    
                    // Check prices
                    $totalAmout = 0;
                    if(isset($f["adtPrice"]) && isset($adtPrice[0]) && $f["adtPrice"] != $adtPrice[0]) {
                        $newFare    = $f["adtFare"] ?? null;
                        $newTaxFee  = $f["adtTaxFee"] ?? null;
                        $newPrice   = $f["adtPrice"] ?? null;
    
                        $updateData[0]['adtFare'] = [
                            "detailId"  => $adtDetailId[0] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $adt;
                    }
                    else $totalAmout += ($adtPrice[0] ?? 0) * $adt;
                    if($chd > 0 && isset($f["chdPrice"]) && isset($chdPrice[0]) && $f["chdPrice"] != $chdPrice[0]) {
                        $newFare    = $f["chdFare"] ?? null;
                        $newTaxFee  = $f["chdTaxFee"] ?? null;
                        $newPrice   = $f["chdPrice"] ?? null;
    
                        $updateData[0]['chdFare'] = [
                            "detailId"  => $chdDetailId[0] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $chd;
                    }
                    else $totalAmout += ($chdPrice[0] ?? 0) * $chd;
                    if($inf > 0 && isset($f["infPrice"]) && isset($infPrice[0]) && $f["infPrice"] != $infPrice[0]) {
                        $newFare    = $f["infFare"] ?? null;
                        $newTaxFee  = $f["infTaxFee"] ?? null;
                        $newPrice   = $f["infPrice"] ?? null;
    
                        $updateData[0]['infFare'] = [
                            "detailId"  => $infDetailId[0] ?? '',
                            "fare"      => $newFare,
                            "fee"       => $newTaxFee,
                            "price"     => $newPrice
                        ];

                        $totalAmout += $newPrice * $inf;
                    }
                    else $totalAmout += ($infPrice[0] ?? 0) * $inf;

                    // Add more info to update data itinerary id
                    if(isset($updateData[0]) && !empty($updateData[0])) {
                        $updateData[0]['itineraryId']['dep'] = $listItineraryId[0] ?? ''; // Itinerary Id
                        $updateData[0]['totalAmount'] = $totalAmout; // Total amount
                    }
                    break;
                }
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
                    //         ,date_modified = NOW()
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
                            ,date_modified = NOW()
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
                        ,date_modified = NOW()
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
                            ,date_modified = NOW()
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
                            ,date_modified = NOW()
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
            /**
             * Procedures & Rules
             * - The price of international flights are combined, thus only update price to outbound in database (With roundtrip).
             * - The itinerary of international flights are handled normally such as domestic flights.
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
             *      "itineraryId": {
             *          "dep": "string",
             *          "ret": "string"
             *      },
             *      "itinerary": {
             *          "dep": {
             *              "segments": [...],
             *              "transits": [...]
             *          },
             *          "ret": ...
             *      }
             *  }
             */

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
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = NOW()
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
                        ,modified_user_id = '{$this->currentUser->id}'
                        ,date_modified = NOW()
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

            // Update departure and return schedule
            foreach(['dep', 'ret'] as $roundName) {
                if(isset($params['itinerary']) && isset($params['itinerary'][$roundName]) && !empty($params['itinerary'][$roundName])) {
                    $segments = $params['itinerary'][$roundName]['segments'] ?? [];
                    $transits = $params['itinerary'][$roundName]['transits'] ?? [];
                    $directionVal = $roundName == 'ret' ? '1' : '0';
    
                    $sql = "SELECT id
                        FROM ec_booking_itineraries
                        WHERE booking_id = '$bookingId'
                            AND direction = '$directionVal'
                            AND deleted = 0
                            AND add_type = 0
                        ORDER BY transit_order, departure_date";
    
                    $i = 0;
                    $res = $db->query($sql);
                    while ($row = $db->fetchByAssoc($res)) {
                        $rowId = $row['id'] ?? '';

                        $segDep         = $segments[$i]['dep'] ?? '';
                        $segDes         = $segments[$i]['des'] ?? '';
                        $segCarrierCode = $segments[$i]['carrierCode'] ?? '';
                        $segDepDateTime = ($segments[$i]['depDate'] ?? '') . ' ' . ($segments[$i]['depTime'] ?? '') . ':00'; // Y-m-d H:i:s
                        $segArvDateTime = ($segments[$i]['arvDate'] ?? '') . ' ' . ($segments[$i]['arvTime'] ?? '') . ':00'; // Y-m-d H:i:s
                        $segFlightNo    = trim($segments[$i]['flightNo'] ?? '');
                        
                        // Get transit info
                        $transit = '';
                        if(isset($transits[$i]) && !empty($transits[$i])) {
                            $transit = 'Trung chuyển tại: ' . $transits[$i]['station'] ?? '';
                            $transit .= ' - Thời gian dừng: ' . $transits[$i]['nDuration'] ?? '';
                        }
                        $transit = trim($transit);
                    
                        $sqlUpdate = "UPDATE ec_booking_itineraries
                            SET departure = '$segDep'
                                ,arrival = '$segDes '
                                ,departure_date = '$segDepDateTime'
                                ,arrival_date = '$segArvDateTime'
                                ,airline_code = '$segCarrierCode'
                                ,description = IF(LENGTH('$transit') > 0, '$transit', description)
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id = '$rowId' AND TRIM(flight_number) = '$segFlightNo'";
                        $db->query($sqlUpdate);
    
                        $i++;
                    }
                }
            }

            // Update base price
            if(!is_null($basePrice)) {
                $itiDepId = $params['itineraryId']['dep'] ?? '';
                $itiRetId = $params['itineraryId']['ret'] ?? '';

                $sqlUpdate = "UPDATE ec_booking_itineraries
                        SET base_price = IF($basePrice <> base_price && base_price > 0, $basePrice, base_price)
                            ,modified_user_id = '{$this->currentUser->id}'
                            ,date_modified = NOW()
                        WHERE (id = '$itiDepId' OR id = '$itiRetId')
                            AND booking_id = '$bookingId'
                            AND deleted = 0
                            AND add_type = 0";
                $db->query($sqlUpdate);
            }

            return ["status" => 1, "message" => "Update success"];
        }

        return ["status" => 0, "message" => "Nothing to update", "params" => $params];
    }

    /**
     * Book a flight
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

        $agency = new APIDatacom();
        $response = $agency->book($requestBody);
        $responseArr = json_decode($response, true);

        // Save to BM
        if($responseArr['status'] == 1) {
            global $db;

            // Booking type: oneway (Một chiều), roundtrip (Khứ hồi cùng hãng) , twoway (Khứ hồi 2 hãng khác nhau)
            $bookingType = null;
            $countItinerary = count($requestBody['ListAirOption'] ?? []);
            if($countItinerary == 1) $bookingType = 'oneway';
            elseif($countItinerary == 2) {
                if($requestBody['ListAirOption'][0]['Session'] == $requestBody['ListAirOption'][1]['Session']) $bookingType = 'roundtrip';
                else $bookingType = 'twoway';
            }

            $inListPassengerId  = "'".implode("','", $listPassengerId)."'";
            $inListItineraryId  = "'".implode("','", $listItineraryId)."'";
            $inListDetailId     = "'".implode("','", $listDetailId)."'";

            $booking = new EC_Flight_Bookings();
            $booking->retrieve($bookingId);

            $listBooking = $responseArr['data']['ListBooking'] ?? [];
            foreach($listBooking as $key => $bk) {
                // Get PNR
                $pnr = $bk['GdsCode'] ?? '';
                if(empty($pnr)) $pnr = $bk['BookingCode'] ?? '';

                $systemCode = $bk['System'] ?? ''; // System code
                $airlineCode = $bk['Airline'] ?? ''; // Airline code
                $expirationTime = $agency->convertDatetime($bk['ExpirationTime'] ?? ''); // 19092025 1737

                // Send notification
                try {
                    $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;
                    $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                    $airlineCodeHTML = $systemCode != $airlineCode ? "($airlineCode)" : "";
                    $linkBooking = "https://{$this->domain}/index.php?module=EC_Flight_Bookings&action=DetailView&record=$bookingId";

                    $link = "<a href=\"".$linkBooking."\">$pnr</a>";

                    $m = "Giữ chỗ $systemName $airlineCodeHTML: $link bởi <b>$fullname</b>";
                    if(isset($bk['AutoIssue']) && $bk['AutoIssue'] === true) {
                        $m = "<b>💰 Xuất vé cận $systemName $airlineCodeHTML: $link bởi $fullname</b>";
                    } 
                    if(isset($responseArr['data']['OrderId']) && !empty($responseArr['data']['OrderId'])) {
                        $m .= "\n<i>Order ID: ". ($responseArr['data']['OrderId']) ."</i>";
                    }
                    $m .= "\nNCC: <b>{$this->supplierName}</b>";

                    NotificationService::sendMessage($m, 'autobook');
                }
                catch(Throwable $th) {
                    $GLOBALS['log']->warning("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
                }

                if($bookingType == 'roundtrip') {
                    // Update PNR
                    $sqlUpdate = "UPDATE ec_booking_passengers
                            SET pnr_outbound = '$pnr'
                                ,pnr_inbound = '$pnr'
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListPassengerId) 
                                AND booking_id = '$bookingId'
                                AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    // Update supplier
                    $ticketing_fee = 0;
                    $sqlUpdate = "UPDATE ec_booking_details
                            SET supplier_id = '{$this->supplierId}'
                                -- ,fee_bought = IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                -- ,total_bought_price = total_bought_price + IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListDetailId) 
                                AND booking_id = '$bookingId'
                                AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    // Update expiration time
                    $expirationTimestamp = strtotime($expirationTime);
                    if($expirationTimestamp && $expirationTimestamp > time()) {
                        $expirationTime = date("Y-m-d H:i:00", $expirationTimestamp);
                        $sqlUpdate = "UPDATE ec_booking_itineraries
                            SET time_limit = '{$expirationTime}'
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListItineraryId) 
                                AND booking_id = '$bookingId'
                                AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    }

                    // // Update available checked baggage info (Use for website have new baggage)
                    // if($booking->id && !empty($booking->id) && in_array($booking->created_by, $booking->list_website_new_baggage)) {
                    //     foreach($bk["ListFlightFare"] as $ff) {
                    //         $roundText = $ff["Leg"] == 1 ? 'inbound' : 'outbound';

                    //         foreach($ff["FareInfo"]["ListFarePax"] as $farePax) {
                    //             $paxType = strtolower($farePax["PaxType"] ?? '');   
                    //             $paxTypeValue = $paxType == 'adt' ? '0' : ($paxType == 'chd' ? '1' : '2');

                    //             // $handBaggage = $this->extractBaggageValue($farePax["ListFareInfo"][0]["HandBaggage"] ?? '');
                    //             $freeBaggage = $this->extractBaggageValue($farePax["ListFareInfo"][0]["FreeBaggage"] ?? '');
                    //             $freeBaggageValue = $freeBaggage["value"] ?? '';

                    //             $sqlUpdate = "UPDATE ec_booking_passengers p
                    //                 SET p.luggage_index_{$roundText} = '{$freeBaggageValue}'
                    //                 WHERE p.booking_id = '{$bookingId}'
                    //                     AND p.id IN ({$inListPassengerId})
                    //                     AND p.type = '{$paxTypeValue}'
                    //                     AND p.deleted = 0";
                    //             if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    //         }
                    //     }
                    // }
                }
                else {
                    $direction = '0';
                    $roundText = 'outbound';
                    if($bookingType == 'twoway') {
                        if($key == 1) {
                            $direction = '1';
                            $roundText = 'inbound';
                        }
                    }
                    else {
                        $sql = "SELECT direction
                            FROM ec_booking_itineraries
                            WHERE id = '{$listItineraryId[0]}' AND booking_id = '$bookingId' AND deleted = 0";
                        $direction = $db->getOne($sql);
                        $roundText = $direction == '1' ? 'inbound' : 'outbound'; 
                    }

                    // Update PNR
                    $sqlUpdate = "UPDATE ec_booking_passengers
                            SET pnr_{$roundText} = '$pnr'
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListPassengerId) 
                                AND booking_id = '$bookingId'
                                AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    // Update supplier
                    $ticketing_fee = ($systemCode == 'VJ' || $airlineCode == 'VJ') ? 5000 : 0;
                    $sqlUpdate = "UPDATE ec_booking_details
                            SET supplier_id = '{$this->supplierId}'
                                ,fee_bought = IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                ,total_bought_price = total_bought_price + IF(passenger_type <> '2', $ticketing_fee * quantity, 0)
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListDetailId)
                                AND booking_id = '$bookingId'
                                AND direction = '$direction'
                                AND deleted = 0";
                    if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);

                    // Update expiration time
                    $expirationTimestamp = strtotime($expirationTime);
                    if($expirationTimestamp && $expirationTimestamp > time()) {
                        $expirationTime = date("Y-m-d H:i:00", $expirationTimestamp);
                        $sqlUpdate = "UPDATE ec_booking_itineraries
                            SET time_limit = '{$expirationTime}'
                                ,modified_user_id = '{$this->currentUser->id}'
                                ,date_modified = NOW()
                            WHERE id IN ($inListItineraryId) 
                                AND booking_id = '$bookingId'
                                AND deleted = 0";
                        if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    }

                    // // Update available checked baggage info (Use for website have new baggage)
                    // if($booking->id && !empty($booking->id) && in_array($booking->created_by, $booking->list_website_new_baggage)) {
                    //     foreach($bk["ListFlightFare"] as $ff) {
                    //         foreach($ff["FareInfo"]["ListFarePax"] as $farePax) {
                    //             $paxType = strtolower($farePax["PaxType"] ?? '');   
                    //             $paxTypeValue = $paxType == 'adt' ? '0' : ($paxType == 'chd' ? '1' : '2');

                    //             // $handBaggage = $this->extractBaggageValue($farePax["ListFareInfo"][0]["HandBaggage"] ?? '');
                    //             $freeBaggage = $this->extractBaggageValue($farePax["ListFareInfo"][0]["FreeBaggage"] ?? '');
                    //             $freeBaggageValue = $freeBaggage["value"] ?? '';

                    //             $sqlUpdate = "UPDATE ec_booking_passengers
                    //                 SET luggage_index_{$roundText} = '{$freeBaggageValue}'
                    //                     ,modified_user_id = '{$this->currentUser->id}'
                    //                     ,date_modified = NOW()
                    //                 WHERE booking_id = '{$bookingId}'
                    //                     AND id IN ({$inListPassengerId})
                    //                     AND type = '{$paxTypeValue}'
                    //                     AND deleted = 0";
                    //             if(!$db->query($sqlUpdate)) $this->sendSQLErrorNotification($sqlUpdate);
                    //         }
                    //     }
                    // }
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

        $agency = new APIDatacom();
        $jsonBooking = $agency->getBooking($pnr, $systemCode, $airlineCode);
        $arrBooking = json_decode($jsonBooking, true);
        if(isset($arrBooking["status"]) && $arrBooking["status"] == 1) {
            $supplier = strtolower($arrBooking['supplier'] ?? '');
            if($supplier == 'phuongnam') {
                require_once "custom/include/helpers/api/APIPhuongNam.php";
                $phuongNamAgency = new APIPhuongNam();
                $arrBooking['data'] = $phuongNamAgency->standardizeBookingData($arrBooking['data']);
                $arrBooking['data']['EntryClass'] = "entryAutoBookPhuongNamClass";
                $arrBooking['data']['Supplier'] = "NCC Phương Nam";
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
     * Get booking data
     * 
     * @param array $params
     * @return array [status, message, data]
     */
    public function getBaggageInfo($params = []) {
        $bookingCode    = $params['bookingCode'] ?? ''; // PNR
        $bookingId      = $params['bookingId'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';
        $direction      = (int)($params['direction'] ?? 0);
        $origin         = $params['origin'] ?? "";
        $destination    = $params['destination'] ?? "";
        $flightNumber   = $params['flightNumber'] ?? "";
        $passengerInfo  = $params['passengerInfo'] ?? []; // Info who purchase baggage
        
        $agency = new APIDatacom();
        $json = $agency->getBaggageInfo($bookingCode, $systemCode, $bookingId);
        $arr = json_decode($json, true);

        if(isset($arr["status"]) && $arr["status"] == 1) {
            $data = [];
            $data["ListBaggage"] = $agency->standardizeListBaggageData($arr["data"], $origin, $destination, $flightNumber);
            $data["Origin"]      = $data["ListBaggage"][0]["Origin"];
            $data["Destination"] = $data["ListBaggage"][0]["Destination"];
            return [
                "status" => 1,
                "message" => $arr["message"] ?? "Success",
                "data" => $data
            ];
        };

        return $arr;
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

        $agency = new APIDatacom();
        $response = $agency->payBooking($bookingCode, $systemCode);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;
                $paidAmount = $responseArr["data"]["PaidAmount"] ?? 0;

                $m = "<b>💰 Xuất vé $systemName: $bookingCode bởi $fullname</b>";
                if($paidAmount > 0) $m .= "\nTổng thanh toán: <b>".format_number($paidAmount)." VND</b>";
                $m .= "\nNCC: <b>{$this->supplierName}</b>";
                NotificationService::sendMessage($m, 'autobook');
            }
        }
        catch(Throwable $th) {
            $GLOBALS['log']->warning("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }

        return $responseArr;
    }

    /**
     * Add baggage to passenger
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

        $agency = new APIDatacom();
        $response = $agency->addBaggage($bookingCode, $systemCode, $airlineCode, $baggageData['Value'] ?? [], $passengerData['Value'] ?? []);
        $responseArr = json_decode($response, true);

        if(isset($responseArr['status']) && $responseArr['status'] == 1) {
            try {
                global $db;

                $bagDescription = $baggageData["Description"] ?? "";
                if(!empty($bagDescription)) $bagDescription = $baggageData["Name"] ?? "";
                $bagAmount      = $baggageData["Amount"] ?? "";
                $bagVat         = $baggageData["VAT"] ?? "";
                $bagTotalAmount = $baggageData["TotalAmount"] ?? 0;
                $passengerName  = trim($passengerData['LastName'] . ' ' . $passengerData['FirstName']);
                $suffix         = $direction === 1 ? "_inbound" : "";
                $pnrField       = $direction === 1 ? "pnr_inbound" : "pnr_outbound";

                $sqlUpdate = "UPDATE ec_booking_passengers
                    SET luggage_purchase_text{$suffix}      = '$bagDescription'
                        ,luggage_purchase{$suffix}          = $bagTotalAmount
                        ,vat_luggage_purchase{$suffix}      = $bagVat
                        ,luggage_purchase{$suffix}_no_vat   = $bagAmount
                        ,supplier{$suffix}_id               = '{$this->supplierId}'
                        ,modified_user_id                   = '{$this->currentUser->id}'
                        ,date_modified                      = NOW()
                    WHERE $pnrField = '$bookingCode'
                        AND name = '$passengerName'
                        AND (luggage_purchase$suffix IS NULL OR luggage_purchase$suffix = 0)
                        AND date_entered >= NOW() - INTERVAL 120 DAY
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
        $listSegmentId  = $params['listSegmentId'] ?? [];

        $agency = new APIDatacom();
        $response = $agency->cancelBooking($bookingCode, $systemCode, $listSegmentId);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;

                $m = "<b>Hủy giữ chỗ $bookingCode ($systemName) bởi $fullname</b>";
                $m .= "\nNCC: <b>{$this->supplierName}</b>";
                NotificationService::sendMessage($m, 'autobook');
            }
        }
        catch(Throwable $th) {
            $GLOBALS['log']->warning("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }

        return $responseArr;
    }

    /**
     * Void ticket
     * 
     * @param array $params
     * @return array
     */
    public function voidTicket($params = []) {
        $bookingCode    = $params['bookingCode'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';
        $listTicket     = $params['listTicket'] ?? [];

        $agency = new APIDatacom();
        $response = $agency->voidTicket($bookingCode, $systemCode, $listTicket);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;
                $qty = count($listTicket);

                $m = "<b>Hủy $qty vé $bookingCode ($systemName) bởi $fullname</b>";
                $m .= "\nNCC: <b>{$this->supplierName}</b>";
                NotificationService::sendMessage($m, 'autobook');
            }
        }
        catch(Throwable $th) {
            $GLOBALS['log']->warning("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }

        return $responseArr;
    }

    /**
     * Refund ticket
     * 
     * @param array $params
     * @return array
     */
    public function refundTicket($params = []) {
        $bookingCode    = $params['bookingCode'] ?? '';
        $systemCode     = $params['systemCode'] ?? '';
        $listTicket     = $params['listTicket'] ?? [];

        $agency = new APIDatacom();
        $response = $agency->refundTicket($bookingCode, $systemCode, $listTicket);
        $responseArr = json_decode($response, true);

        // Send notification
        try {
            if(isset($responseArr['status']) && $responseArr['status'] == 1) {
                $fullname = trim($this->currentUser->last_name.' '.$this->currentUser->first_name);
                $systemName = $this->mappingSystemCodeName[$systemCode] ?? $systemCode;
                $qty = count($listTicket);

                $m = "<b>Hoàn $qty vé $bookingCode ($systemName) bởi $fullname</b>";
                $m .= "\nNCC: <b>{$this->supplierName}</b>";
                NotificationService::sendMessage($m, 'autobook');
            }
        }
        catch(Throwable $th) {
            $GLOBALS['log']->warning("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
        }

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

    /**
     * Extract baggage from response
     * 
     * @param string $str
     * @return array [value, description]
     */
    private function extractBaggageValue($str) {
        /**
         * $str includes values as:
         * null
         * 1 piece x 10kg
         * 1 piece
         * 20kg
         * 20 KG
         */
        if(is_null($str) || empty($str)) return ["value" => "", "description" => ""];

        $str = strtolower(trim($str));

        preg_match('/(\d+)\s*piece/i', $str, $pieceMatches);
        $piece = (int)($pieceMatches[1] ?? 0);

        preg_match('/(\d+)\s*kg/i', $str, $weightMatches);
        $weight = (int)($weightMatches[1] ?? 0);

        if($piece > 0 && $weight > 0) {
            return ["value" => "{$piece}x{$weight}", "description" => "$piece kiện x {$weight}kg"];
        }
        elseif($piece > 0) {
            return ["value" => $piece, "description" => "$piece kiện"];
        }
        elseif($weight > 0) {
            return ["value" => $weight, "description" => "{$weight}kg"];
        }
        return ["value" => "", "description" => ""];
    }
}