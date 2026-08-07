<?php
require_once 'custom/entrypoints/entryClass.php';

use custom\services\Notification\NotificationService;

/**
 * Class entryBookingClass
 * 
 * Xử lý ajax cho booking
 */
class entryBookingClass extends entryClass {
    private const BOOKING_WEBHOOK_NOTE_MAX_LENGTH = 2000;

    /**
     * Validate and normalize a Booking Webhook payload.
     *
     * @return array{valid: bool, payload: array, errors: array}
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $depCode = $this->normalizeWebhookCode($payload['depCode'] ?? null);
        $desCode = $this->normalizeWebhookCode($payload['desCode'] ?? null);
        if (!preg_match('/^[A-Z]{3}$/', $depCode)) {
            $errors[] = 'depCode';
        }
        if (!preg_match('/^[A-Z]{3}$/', $desCode) || $desCode === $depCode) {
            $errors[] = 'desCode';
        }

        $depDate = $this->parseWebhookDate($payload['depDate'] ?? null);
        if ($depDate === null) {
            $errors[] = 'depDate';
        }

        $retDateValue = array_key_exists('retDate', $payload) ? $payload['retDate'] : null;
        $retDate = $retDateValue === null ? null : $this->parseWebhookDate($retDateValue);
        if ($retDateValue !== null && $retDate === null) {
            $errors[] = 'retDate';
        } elseif ($depDate !== null && $retDate !== null && $retDate < $depDate) {
            $errors[] = 'retDate';
        }

        $airlineCodeDep = $this->normalizeWebhookAirlineCode($payload['airlineCodeDep'] ?? null);
        if (!preg_match('/^[A-Z0-9]{2,20}$/', $airlineCodeDep)) {
            $errors[] = 'airlineCodeDep';
        }

        $airlineCodeRetValue = $payload['airlineCodeRet'] ?? null;
        $airlineCodeRet = $airlineCodeRetValue === null
            ? ''
            : $this->normalizeWebhookAirlineCode($airlineCodeRetValue);
        if (($airlineCodeRetValue !== null && !is_string($airlineCodeRetValue))
            || ($airlineCodeRet !== '' && !preg_match('/^[A-Z0-9]{2,20}$/', $airlineCodeRet))
            || ($retDate !== null && $airlineCodeRet === '')
        ) {
            $errors[] = 'airlineCodeRet';
        }

        $contactName = $this->normalizeWebhookString($payload['contactName'] ?? null);
        if ($contactName === '' || $this->webhookStringLength($contactName) > 128) {
            $errors[] = 'contactName';
        }

        $contactPhone = $this->normalizeWebhookString($payload['contactPhone'] ?? null);
        if ($contactPhone === '' || $this->webhookStringLength($contactPhone) > 30) {
            $errors[] = 'contactPhone';
        }

        $contactEmailValue = $payload['contactEmail'] ?? null;
        $contactEmail = $contactEmailValue === null
            ? ''
            : $this->normalizeWebhookString($contactEmailValue);
        if (($contactEmailValue !== null && !is_string($contactEmailValue))
            || $this->webhookStringLength($contactEmail) > 50
            || ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false)
        ) {
            $errors[] = 'contactEmail';
        }

        $identityNumberValue = $payload['identityNumber'] ?? null;
        $identityNumber = $identityNumberValue === null
            ? ''
            : $this->normalizeWebhookString($identityNumberValue);
        if (($identityNumberValue !== null && !is_string($identityNumberValue))
            || $this->webhookStringLength($identityNumber) > 16
        ) {
            $errors[] = 'identityNumber';
        }

        $ticketType = $payload['ticketType'] ?? null;
        if (!is_int($ticketType) || !in_array($ticketType, [1, 2], true)) {
            $errors[] = 'ticketType';
        }

        $bookingNoteValue = $payload['bookingNote'] ?? null;
        $bookingNote = $bookingNoteValue === null
            ? ''
            : $this->normalizeWebhookString($bookingNoteValue);
        if (($bookingNoteValue !== null && !is_string($bookingNoteValue))
            || $this->webhookStringLength($bookingNote) > self::BOOKING_WEBHOOK_NOTE_MAX_LENGTH
        ) {
            $errors[] = 'bookingNote';
        }

        return [
            'valid' => $errors === [],
            'payload' => [
                'depCode' => $depCode,
                'desCode' => $desCode,
                'depDate' => $depDate ? $depDate->format('Y-m-d 00:00:00') : null,
                'retDate' => $retDate ? $retDate->format('Y-m-d 00:00:00') : null,
                'airlineCodeDep' => $airlineCodeDep,
                'airlineCodeRet' => $airlineCodeRet,
                'contactName' => $contactName,
                'contactPhone' => $contactPhone,
                'contactEmail' => $contactEmail,
                'identityNumber' => $identityNumber,
                'ticketType' => $ticketType,
                'bookingNote' => $bookingNote,
            ],
            'errors' => array_values(array_unique($errors)),
        ];
    }

    private function parseWebhookDate($value): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) !== $value || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!d/m/Y', $value);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if ($date === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format('d/m/Y') !== $value
        ) {
            return null;
        }

        return $date;
    }

    private function normalizeWebhookCode($value): string
    {
        return is_string($value) ? strtoupper(trim($value)) : '';
    }

    private function normalizeWebhookAirlineCode($value): string
    {
        $value = is_string($value) ? strtoupper(trim($value)) : '';
        return $this->webhookStringLength($value) <= 20 ? $value : '';
    }

    private function normalizeWebhookString($value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function webhookStringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    /**
     * Create a draft booking received from the Chat webhook.
     *
     * X-Request-Id is optional until Chat supports it. When present, it is used
     * together with the raw payload hash to make retries idempotent.
     *
     * @return array{success: bool, http_code: int, booking_id?: string, booking_name?: string}
     */
    public function createBookingFromWebhook(
        array $payload,
        ?string $requestId,
        string $payloadHash
    ): array {
        global $current_user, $db;

        $existingBooking = $requestId
            ? $this->findBookingByExternalRequestId($requestId)
            : null;
        if ($existingBooking !== null) {
            return hash_equals((string) $existingBooking['external_payload_hash'], $payloadHash)
                ? [
                    'success' => true,
                    'http_code' => 200,
                    'booking_id' => $existingBooking['id'],
                    'booking_name' => $existingBooking['name'],
                ]
                : ['success' => false, 'http_code' => 409];
        }

        if (empty($current_user->id)
            || !empty($current_user->deleted)
            || (isset($current_user->status) && $current_user->status !== 'Active')
        ) {
            $GLOBALS['log']->fatal('Booking webhook current user is missing or inactive');
            return ['success' => false, 'http_code' => 401];
        }
        $currentUserId = $current_user->id;

        require_once 'modules/EC_Flight_Bookings/EC_Flight_Bookings.php';
        require_once 'modules/EC_Booking_Itineraries/EC_Booking_Itineraries.php';
        require_once 'modules/EC_Booking_Passengers/EC_Booking_Passengers.php';

        $transactionStarted = false;

        try {
            if ($db->query('START TRANSACTION') === false) {
                throw new RuntimeException('Unable to start database transaction');
            }
            $transactionStarted = true;

            $booking = new EC_Flight_Bookings();
            $booking->contact_name = $payload['contactName'];
            $booking->phone = $payload['contactPhone'];
            $booking->email = $payload['contactEmail'];
            $booking->ticket_type = (string) $payload['ticketType'];
            $booking->description = $payload['bookingNote'];
            $booking->airline = $payload['airlineCodeDep'];
            $booking->airline_inbound = $payload['airlineCodeRet'];
            $booking->journey = $payload['depCode'] . '-' . $payload['desCode'];
            $booking->flight_type = $payload['retDate'] === null ? '1' : '0';
            $booking->booking_status = '1';
            $booking->customer_source = 'chat';
            $booking->created_by = $currentUserId;
            $booking->modified_user_id = $currentUserId;
            $booking->external_payload_hash = $payloadHash;
            if ($requestId !== null) {
                $booking->external_request_id = $requestId;
            }

            $bookingId = $booking->save();
            if (!is_string($bookingId) || $bookingId === '') {
                throw new RuntimeException('Unable to save booking');
            }

            $this->saveWebhookItinerary(
                $bookingId,
                '0',
                $payload['depCode'],
                $payload['desCode'],
                $payload['airlineCodeDep'],
                $payload['depDate'],
                $currentUserId
            );

            if ($payload['retDate'] !== null) {
                $this->saveWebhookItinerary(
                    $bookingId,
                    '1',
                    $payload['desCode'],
                    $payload['depCode'],
                    $payload['airlineCodeRet'],
                    $payload['retDate'],
                    $currentUserId
                );
            }

            $passenger = new EC_Booking_Passengers();
            $passenger->name = $payload['contactName'];
            $passenger->type = '0';
            $passenger->booking_id = $bookingId;
            $passenger->assigned_user_id = $currentUserId;
            $passenger->created_by = $currentUserId;
            $passenger->modified_user_id = $currentUserId;
            $passengerId = $passenger->save();
            if (!is_string($passengerId) || $passengerId === '') {
                throw new RuntimeException('Unable to save booking passenger');
            }

            if ($db->query('COMMIT') === false) {
                throw new RuntimeException('Unable to commit database transaction');
            }
            $transactionStarted = false;

            return [
                'success' => true,
                'http_code' => 200,
                'booking_id' => $bookingId,
                'booking_name' => $booking->name,
            ];
        } catch (Throwable $throwable) {
            if ($transactionStarted) {
                $db->query('ROLLBACK');
            }

            $GLOBALS['log']->fatal(sprintf(
                'Booking webhook create failed: %s on line %d in %s',
                $throwable->getMessage(),
                $throwable->getLine(),
                $throwable->getFile()
            ));

            // Resolve the race where another request committed the same unique
            // request ID after the initial lookup.
            $existingBooking = $requestId
                ? $this->findBookingByExternalRequestId($requestId)
                : null;
            if ($existingBooking !== null) {
                return hash_equals((string) $existingBooking['external_payload_hash'], $payloadHash)
                    ? [
                        'success' => true,
                        'http_code' => 200,
                        'booking_id' => $existingBooking['id'],
                        'booking_name' => $existingBooking['name'],
                    ]
                    : ['success' => false, 'http_code' => 409];
            }

            return ['success' => false, 'http_code' => 500];
        }
    }

    private function saveWebhookItinerary(
        string $bookingId,
        string $direction,
        string $departure,
        string $arrival,
        string $airlineCode,
        string $departureDate,
        string $currentUserId
    ): void {
        $itinerary = new EC_Booking_Itineraries();
        $itinerary->name = $departure . '-' . $arrival;
        $itinerary->direction = $direction;
        $itinerary->departure = $departure;
        $itinerary->arrival = $arrival;
        $itinerary->airline_code = $airlineCode;
        $itinerary->departure_date = $departureDate;
        $itinerary->arrival_date = '';
        $itinerary->booking_id = $bookingId;
        $itinerary->assigned_user_id = $currentUserId;
        $itinerary->created_by = $currentUserId;
        $itinerary->modified_user_id = $currentUserId;

        $itinerary->save();
        if (empty($itinerary->id)) {
            throw new RuntimeException('Unable to save booking itinerary');
        }
    }

    private function findBookingByExternalRequestId(string $requestId): ?array
    {
        global $db;

        $sql = "SELECT id, name, external_payload_hash
            FROM ec_flight_bookings
            WHERE external_request_id = '" . $db->quote($requestId) . "'
                AND deleted = 0
            LIMIT 1";
        $result = $db->query($sql);
        if ($result === false) {
            throw new RuntimeException('Unable to check booking request ID');
        }

        $row = $db->fetchByAssoc($result);
        return is_array($row) ? $row : null;
    }

    /**
     * Update fields
     *
     * @param array $params
     * @return array
     */
    public function updateFields($params = [])
    {
        $bookingId = $params['bookingId'] ?? '';
        $fields = $params['fields'] ?? [];

        if (empty($bookingId))
            return ['status' => 0, 'message' => 'Không tìm thấy booking'];
        if (empty($fields))
            return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = ['customer_source', 'zalo_id'];

        try {
            $bookingBean = new EC_Flight_Bookings();
            $bookingBean->retrieve($bookingId);
            foreach ($fields as $name => $value) {
                if (in_array($name, $list_allowed_fields)) {
                    $bookingBean->$name = $value;
                }
            }
            if ($bookingBean->save2())
                return ['status' => 1, 'message' => 'Thao tác thành công'];
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        }
        catch (Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Lỗi $logId"];
        }
    }

    /**
     * Update fields
     *
     * @param array $params
     * @return array
     */
    public function updatePassengerFields($params = []) {
        $passengerId = $params["passengerId"] ?? "";
        $fields = $params["fields"] ?? [];

        if (empty($passengerId)) {
            return ["status" => 0, "message" => "Không tìm thấy hành khách"];
        }
        if (empty($fields)) {
            return ["status" => 0, "message" => "Dữ liệu không hợp lệ"];
        }

        $list_allowed_fields = [
            "type" => "enum",
            "passport_type" => "enum",
            "passport_number" => "varchar",
            "passport_nationality" => "enum",
            "passport_issue_country" => "enum",
            "passport_issue_date" => "date",
            "passport_expired_date" => "date",
        ];

        if (array_key_exists("passport_type", $fields) || array_key_exists("passport_number", $fields)) {
            if (empty($fields["passport_type"]) || empty($fields["passport_number"])) {
                return ["status" => 0, "message" => "Vui lòng nhập loại giấy tờ và số giấy tờ"];
            }
        }

        try {
            $passengerBean = new EC_Booking_Passengers();
            $passengerBean->retrieve($passengerId);
            if (empty($passengerBean->id)) {
                return ["status" => 0, "message" => "Không tìm thấy hành khách"];
            }
            $updatedFields = [];
            foreach ($fields as $name => $value) {
                if (!array_key_exists($name, $list_allowed_fields)) {
                    continue;
                }

                if ($list_allowed_fields[$name] === 'date' && !empty($value)) {
                    $value = $GLOBALS['timedate']->to_display(
                        $value,
                        TimeDate::DB_DATE_FORMAT,
                        $GLOBALS['timedate']->get_date_format()
                    );
                }

                // Auto generated expired date of National ID
                if($name == "passport_expired_date"
                    && (is_null($value) || empty($value))
                    && isset($fields['passport_type']) && $fields['passport_type'] == 'I'
                    && !empty($passengerBean->birthday)
                ) {
                    $value = EC_Booking_Passengers_Helper::calculatePassportExpiryDate($passengerBean->birthday);
                }

                $passengerBean->$name = $value;
                $updatedFields[$name] = $value;
            }
            if ($passengerBean->save()) {
                return [
                    "status" => 1,
                    "message" => "Thao tác thành công",
                    "data" => $updatedFields,
                ];
            }
            return ["status" => 0, "message" => "Thao tác không thành công, vui lòng thử lại"];
        }
        catch (Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS["log"]->error("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Lỗi $logId"];
        }
    }

    /**
     * Get uploaded documents
     *
     * @param array $params
     * @return string
     */
    public function getUploadedDocuments($params = [])
    {
        header('Content-Type: application/json');
        $booking_id = $params['booking_id'] ?? '';

        if (empty($booking_id) || $booking_id == '') {
            return json_encode([
                'success' => false,
                'message' => 'Booking ID không hợp lệ'
            ]);
        }

        global $db, $app_list_strings;
        $query =
            "SELECT d.id,
                d.document_name,
                d.date_entered,
                d.document_revision_id as revision_id,
                d.category_id,
                d.doc_url as doc_url,
                dr.doc_url as revision_doc_url,
                u.user_name as created_by
            FROM documents d
            LEFT JOIN users u ON d.created_by = u.id
            LEFT JOIN document_revisions dr ON d.document_revision_id = dr.id
            WHERE d.booking_id = '" . $db->quote($booking_id) . "'
            AND d.deleted = 0
            ORDER BY d.date_entered DESC";

        $result = $db->query($query);
        $documents = [];

        while ($row = $db->fetchByAssoc($result)) {
            $category = 'Khác';
            if (!empty($row['category_id']) && isset($app_list_strings['document_category_dom'][$row['category_id']])) {
                $category = $app_list_strings['document_category_dom'][$row['category_id']];
            }

            // Use direct public share URL from doc_url (already has /download)
            $previewUrl = !empty($row['revision_doc_url']) ? $row['revision_doc_url'] . '/preview' : (!empty($row['doc_url']) ? $row['doc_url'] . '/preview' : "");

            $documents[] = [
                'id' => $row['id'],
                'document_name' => $row['document_name'],
                'category' => $category,
                'date_entered' => (new DateTime($row['date_entered'], new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
                    ->format('d/m/Y H:i'),
                'created_by_name' => $row['created_by'] ?: 'N/A',
                'preview_image' => $previewUrl,
                'doc_url' => $row['doc_url'] ?? '', // Add doc_url for direct download
                'revision_id' => $row['revision_id']
            ];
        }
        return json_encode([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Get lotion (address) by geocode
     * @param array $params
     * @return array
     */
    public function getLocation($params = [])
    {
        $lat = $params['lat'] ?? '';
        $long = $params['long'] ?? '';
        $bookingId = $params['bookingId'] ?? '';
        $city = $this->removeVietnameseTones('Hồ Chí Minh');

        if (empty($lat) || empty($long) || empty($bookingId)) {
            return ["status" => 0, "message" => "Tọa độ không hợp lệ", "data" => null];
        }

        try {
            $locationService = new custom\services\Location\LocationService();
            $res = $locationService->reverseGeocode($lat, $long);

            if (isset($res['status']) && $res['status']) {
                $city = trim($res['data']['city'] ?? '');
                if (empty($city))
                    $city = trim($res['data']['ward'] ?? '');

                if (!empty($city)) {
                    global $db;

                    // Cleaned
                    $city = trim(str_replace("Thành phố", "", $city));
                    $city = trim(str_replace("Thành Phố", "", $city));
                    $city = trim(str_replace("Tỉnh", "", $city));
                    if ($city == "Thủ Đức") $city = "Ho Chi Minh";
                    $city = $this->removeVietnameseTones($city);
                    $sql = "UPDATE ec_flight_bookings SET city = '$city' WHERE id = '$bookingId' AND deleted = 0";
                    if ($db->query($sql)) {
                        return [
                            "status" => 1,
                            "message" => "Success",
                            "data" => $city,
                        ];
                    } else {
                        return [
                            "status" => 0,
                            "message" => "Dữ liệu chưa được lưu vào BM",
                            "data" => $city,
                        ];
                    }
                }

                return [
                    "status" => 0,
                    "message" => "Không tìm thấy vị trí phù hợp từ tọa độ",
                    "data" => $res['data'],
                    "raw" => $res
                ];
            }

            $m = "Lấy thông tin vị trí không thành công";
            $m = "\n<pre>" . json_encode($res, JSON_UNESCAPED_UNICODE) . "</pre>";
            NotificationService::sendWarningMessage($m, "", ["threadKey" => "logs"]);
            return $res;
        } catch (Throwable $th) {
            return [
                "status" => 0,
                "message" => "{$th->getMessage()} on line {$th->getLine()}",
                "data" => null
            ];
        }
    }

    public function getLocationByIp($params = [])
    {
        $ip = $params['ip'] ?? '';
        $bookingId = $params['bookingId'] ?? '';

        if (empty($ip) || empty($bookingId)) {
            return ["status" => 0, "message" => "IP không hợp lệ", "data" => null];
        }

        try {
            $locationService = new custom\services\Location\LocationService();
            $res = $locationService->getLocationByIp($ip);

            if (isset($res['status']) && $res['status']) {
                $city = trim($res['data']['city'] ?? '');

                if (!empty($city)) {
                    global $db;

                    $city = trim(str_replace("City", "", $city));

                    $sql = "UPDATE ec_flight_bookings SET city = '$city' WHERE id = '$bookingId' AND deleted = 0";
                    if ($db->query($sql)) {
                        return ["status" => 1, "message" => "Success", "data" => $city];
                    } else {
                        return ["status" => 0, "message" => "Dữ liệu chưa được lưu vào BM", "data" => $city];
                    }
                }

                return ["status" => 0, "message" => "Không tìm thấy vị trí từ IP", "data" => null];
            }

            return ["status" => 0, "message" => "Lấy thông tin IP không thành công", "data" => null];
        } catch (Throwable $th) {
            return ["status" => 0, "message" => "{$th->getMessage()} on line {$th->getLine()}", "data" => null];
        }
    }

    public function removeVietnameseTones($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        return $str;
    }
}
