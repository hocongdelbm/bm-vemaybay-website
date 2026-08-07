<?php
require_once 'custom/entrypoints/entryClass.php';

use custom\services\Notification\NotificationService;

/**
 * Class entryBookingClass
 * 
 * Xử lý ajax cho booking
 */
class entryBookingClass extends entryClass {
    private const MAX_ITINERARIES = 20;
    private const MAX_PASSENGERS = 30;
    private const NOTE_MAX_LENGTH = 2000;
    private const PAYMENT_TYPES = [1, 2, 3, 4, 5, 7];

    /**
     * Validate and normalize the payload sent by Chat.
     *
     * @return array{valid: bool, payload: array, errors: array}
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $userIdValue = array_key_exists('userId', $payload)
            ? $payload['userId']
            : ($payload['user_id'] ?? null);
        $userId = is_string($userIdValue) ? trim($userIdValue) : '';
        if ($userIdValue !== null
            && (!is_string($userIdValue) || !$this->isUuid($userId))
        ) {
            $errors[] = 'userId';
        }

        $itineraries = $this->validateItineraries(
            $payload['itineraries'] ?? null,
            $errors
        );
        $outbound = array_values(array_filter(
            $itineraries,
            static function (array $row): bool {
                return $row['direction'] === 0;
            }
        ));
        $inbound = array_values(array_filter(
            $itineraries,
            static function (array $row): bool {
                return $row['direction'] === 1;
            }
        ));

        if ($outbound === []) {
            $errors[] = 'itineraries.direction';
        }

        $firstOutbound = $outbound[0] ?? null;
        $lastOutbound = $outbound === [] ? null : $outbound[count($outbound) - 1];
        $firstInbound = $inbound[0] ?? null;
        $derivedFlightType = $inbound === [] ? 1 : 0;

        $depCode = $firstOutbound['departure'] ?? '';
        $desCode = $lastOutbound['arrival'] ?? '';
        $depDate = isset($firstOutbound['departureAt'])
            ? substr($firstOutbound['departureAt'], 0, 10)
            : '';
        $retDate = isset($firstInbound['departureAt'])
            ? substr($firstInbound['departureAt'], 0, 10)
            : null;
        $airlineCodeDep = $firstOutbound['airlineCode'] ?? '';
        $airlineCodeRet = $firstInbound['airlineCode'] ?? '';

        $this->validateSummaryCode($payload, 'depCode', $depCode, $errors);
        $this->validateSummaryCode($payload, 'desCode', $desCode, $errors);
        $this->validateSummaryDate($payload, 'depDate', $depDate, false, $errors);
        $this->validateSummaryDate($payload, 'retDate', $retDate, true, $errors);
        $this->validateSummaryAirline(
            $payload,
            'airlineCodeDep',
            $airlineCodeDep,
            $errors
        );
        $this->validateSummaryAirline(
            $payload,
            'airlineCodeRet',
            $airlineCodeRet,
            $errors
        );

        $passengers = $this->validatePassengers(
            $payload['passengers'] ?? null,
            $errors
        );
        $firstAdult = null;
        foreach ($passengers as $passenger) {
            if ($passenger['type'] === 0) {
                $firstAdult = $passenger;
                break;
            }
        }
        if ($firstAdult === null) {
            $errors[] = 'passengers.type';
        }

        $contactName = $this->readRequiredString(
            $payload,
            'contactName',
            128,
            $errors,
            true
        );
        $contactPhone = $this->readRequiredString(
            $payload,
            'contactPhone',
            30,
            $errors
        );
        $contactEmail = $this->readOptionalString(
            $payload,
            'contactEmail',
            50,
            $errors
        );
        if ($contactEmail !== ''
            && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false
        ) {
            $errors[] = 'contactEmail';
        }

        $identityNumber = $this->readOptionalString(
            $payload,
            'identityNumber',
            16,
            $errors
        );
        if ($firstAdult !== null
            && $identityNumber !== ''
            && $identityNumber !== $firstAdult['identityNumber']
        ) {
            $errors[] = 'identityNumber';
        }
        $bookingNote = $this->readOptionalString(
            $payload,
            'bookingNote',
            self::NOTE_MAX_LENGTH,
            $errors
        );
        $city = $this->readOptionalString($payload, 'city', 100, $errors);
        $address = $this->readOptionalString($payload, 'address', 255, $errors);

        $defaultContactTitle = $firstAdult['salutation'] ?? 0;
        $contactTitle = array_key_exists('contactTitle', $payload)
            ? $this->readEnum($payload['contactTitle'], [0, 1])
            : $defaultContactTitle;
        if ($contactTitle === null) {
            $errors[] = 'contactTitle';
            $contactTitle = $defaultContactTitle;
        }

        $paymentType = array_key_exists('paymentType', $payload)
            ? $this->readEnum($payload['paymentType'], self::PAYMENT_TYPES)
            : 3;
        if ($paymentType === null) {
            $errors[] = 'paymentType';
            $paymentType = 3;
        }

        $flightType = array_key_exists('flightType', $payload)
            ? $this->readEnum($payload['flightType'], [0, 1])
            : $derivedFlightType;
        if ($flightType === null || $flightType !== $derivedFlightType) {
            $errors[] = 'flightType';
            $flightType = $derivedFlightType;
        }

        if (array_key_exists('customerSource', $payload)
            && $payload['customerSource'] !== 'chat'
        ) {
            $errors[] = 'customerSource';
        }

        if (array_key_exists('bookingStatus', $payload)) {
            $bookingStatus = $this->readEnum($payload['bookingStatus'], [1]);
            if ($bookingStatus !== 1) {
                $errors[] = 'bookingStatus';
            }
        }

        return [
            'valid' => $errors === [],
            'payload' => [
                'userId' => $userId,
                'itineraries' => $itineraries,
                'passengers' => $passengers,
                'depCode' => $depCode,
                'desCode' => $desCode,
                'depDate' => $depDate,
                'retDate' => $retDate,
                'airlineCodeDep' => $airlineCodeDep,
                'airlineCodeRet' => $airlineCodeRet,
                'contactName' => $contactName,
                'contactTitle' => $contactTitle,
                'paymentType' => $paymentType,
                'flightType' => $flightType,
                'city' => $city,
                'contactPhone' => $contactPhone,
                'contactEmail' => $contactEmail,
                'identityNumber' => $identityNumber,
                'bookingNote' => $bookingNote,
                'address' => $address,
                'customerSource' => 'chat',
                'bookingStatus' => 1,
            ],
            'errors' => array_values(array_unique($errors)),
        ];
    }

    /**
     * @return array{success: bool, http_code: int, booking_id?: string, booking_name?: string}
     */
    public function createBookingFromWebhook(array $payload): array
    {
        global $current_user, $db;

        if (!is_object($current_user)
            || empty($current_user->id)
            || !empty($current_user->deleted)
            || (isset($current_user->status) && $current_user->status !== 'Active')
        ) {
            $this->log('Booking webhook user is missing or inactive');
            return ['success' => false, 'http_code' => 401];
        }

        $currentUserId = (string) $current_user->id;

        require_once 'modules/EC_Flight_Bookings/EC_Flight_Bookings.php';
        require_once 'modules/EC_Booking_Itineraries/EC_Booking_Itineraries.php';
        require_once 'modules/EC_Booking_Passengers/EC_Booking_Passengers.php';

        $transactionStarted = false;

        try {
            if ($db->query('START TRANSACTION') === false) {
                throw new RuntimeException('Unable to start database transaction');
            }
            $transactionStarted = true;

            $booking = new \EC_Flight_Bookings();
            $booking->contact_name = $payload['contactName'];
            $booking->contact_title = (string) $payload['contactTitle'];
            $booking->phone = $payload['contactPhone'];
            $booking->email = $payload['contactEmail'];
            $booking->payment_type = (string) $payload['paymentType'];
            $booking->flight_type = (string) $payload['flightType'];
            $booking->ticket_type = $this->inferTicketType($payload['itineraries']);
            $booking->city = $payload['city'];
            $booking->address = $payload['address'];
            $booking->description = $payload['bookingNote'];
            $booking->airline = $payload['airlineCodeDep'];
            $booking->airline_inbound = $payload['airlineCodeRet'];
            $booking->journey = $payload['depCode'] . '-' . $payload['desCode'];
            $booking->booking_status = '1';
            $booking->customer_source = 'chat';
            $this->assignAuditUser($booking, $currentUserId);

            $bookingId = $booking->save();
            if (!is_string($bookingId) || $bookingId === '') {
                throw new RuntimeException('Unable to save booking');
            }

            foreach ($payload['itineraries'] as $itineraryData) {
                $this->saveItinerary($bookingId, $itineraryData, $currentUserId);
            }

            foreach ($payload['passengers'] as $passengerData) {
                $this->savePassenger($bookingId, $passengerData, $currentUserId);
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

            $this->log(sprintf(
                'Booking webhook create failed: %s on line %d in %s',
                $throwable->getMessage(),
                $throwable->getLine(),
                $throwable->getFile()
            ));

            return ['success' => false, 'http_code' => 500];
        }
    }

    private function validateItineraries($value, array &$errors): array
    {
        if (!is_array($value)
            || $value === []
            || count($value) > self::MAX_ITINERARIES
        ) {
            $errors[] = 'itineraries';
            return [];
        }

        $normalized = [];
        foreach (array_values($value) as $index => $row) {
            $prefix = 'itineraries.' . $index . '.';
            if (!is_array($row)) {
                $errors[] = 'itineraries.' . $index;
                continue;
            }

            $direction = $this->readEnum($row['direction'] ?? null, [0, 1]);
            if ($direction === null) {
                $errors[] = $prefix . 'direction';
                $direction = 0;
            }

            $airlineCode = $this->readNullableCode(
                $row,
                'airlineCode',
                '/^[A-Z0-9]{2,20}$/',
                $prefix,
                $errors
            );
            $flightNumber = $this->readOptionalString(
                $row,
                'flightNumber',
                24,
                $errors,
                $prefix
            );
            $ticketClass = $this->readOptionalString(
                $row,
                'ticketClass',
                50,
                $errors,
                $prefix
            );

            $departure = $this->normalizeCode($row['departure'] ?? null);
            $arrival = $this->normalizeCode($row['arrival'] ?? null);
            if (!preg_match('/^[A-Z]{3}$/', $departure)) {
                $errors[] = $prefix . 'departure';
            }
            if (!preg_match('/^[A-Z]{3}$/', $arrival) || $arrival === $departure) {
                $errors[] = $prefix . 'arrival';
            }

            $departureAt = $this->parseDateTime($row['departureAt'] ?? null);
            if ($departureAt === null) {
                $errors[] = $prefix . 'departureAt';
            }

            $arrivalAtValue = $row['arrivalAt'] ?? null;
            $arrivalAt = '';
            if ($arrivalAtValue !== null && $arrivalAtValue !== '') {
                $arrivalAtDate = $this->parseDateTime($arrivalAtValue);
                if ($arrivalAtDate === null) {
                    $errors[] = $prefix . 'arrivalAt';
                } else {
                    $arrivalAt = $arrivalAtDate->format('Y-m-d H:i:s');
                    if ($departureAt !== null && $arrivalAtDate < $departureAt) {
                        $errors[] = $prefix . 'arrivalAt';
                    }
                }
            } elseif ($arrivalAtValue !== null && !is_string($arrivalAtValue)) {
                $errors[] = $prefix . 'arrivalAt';
            }

            $basePrice = $row['basePrice'] ?? 0;
            if ((!is_int($basePrice) && !is_float($basePrice))
                || !is_finite((float) $basePrice)
                || $basePrice < 0
            ) {
                $errors[] = $prefix . 'basePrice';
                $basePrice = 0;
            }

            $normalized[] = [
                'direction' => $direction,
                'airlineCode' => $airlineCode,
                'flightNumber' => strtoupper($flightNumber),
                'ticketClass' => $ticketClass,
                'departure' => $departure,
                'arrival' => $arrival,
                'departureAt' => $departureAt
                    ? $departureAt->format('Y-m-d H:i:s')
                    : '',
                'arrivalAt' => $arrivalAt,
                'basePrice' => $basePrice,
            ];
        }

        return $normalized;
    }

    private function validatePassengers($value, array &$errors): array
    {
        if (!is_array($value)
            || $value === []
            || count($value) > self::MAX_PASSENGERS
        ) {
            $errors[] = 'passengers';
            return [];
        }

        $normalized = [];
        foreach (array_values($value) as $index => $row) {
            $prefix = 'passengers.' . $index . '.';
            if (!is_array($row)) {
                $errors[] = 'passengers.' . $index;
                continue;
            }

            $type = $this->readEnum($row['type'] ?? null, [0, 1, 2]);
            if ($type === null) {
                $errors[] = $prefix . 'type';
                $type = 0;
            }

            $salutation = $this->readEnum($row['salutation'] ?? null, [0, 1]);
            if ($salutation === null) {
                $errors[] = $prefix . 'salutation';
                $salutation = 0;
            }

            $fullName = $this->readRequiredString(
                $row,
                'fullName',
                255,
                $errors,
                true,
                $prefix
            );

            $birthdayValue = $row['birthday'] ?? null;
            $birthday = '';
            if ($birthdayValue !== null && $birthdayValue !== '') {
                $birthdayDate = $this->parseDate($birthdayValue);
                if ($birthdayDate === null) {
                    $errors[] = $prefix . 'birthday';
                } else {
                    $birthday = $birthdayDate->format('Y-m-d');
                }
            } elseif ($birthdayValue !== null && !is_string($birthdayValue)) {
                $errors[] = $prefix . 'birthday';
            }

            $identityNumber = $this->readOptionalString(
                $row,
                'identityNumber',
                16,
                $errors,
                $prefix
            );

            $normalized[] = [
                'type' => $type,
                'salutation' => $salutation,
                'fullName' => $fullName,
                'birthday' => $birthday,
                'identityNumber' => $identityNumber,
            ];
        }

        return $normalized;
    }

    private function validateSummaryCode(
        array $payload,
        string $field,
        string $derivedValue,
        array &$errors
    ): void {
        if (!array_key_exists($field, $payload)) {
            return;
        }

        $value = $this->normalizeCode($payload[$field]);
        if (!preg_match('/^[A-Z]{3}$/', $value) || $value !== $derivedValue) {
            $errors[] = $field;
        }
    }

    private function validateSummaryDate(
        array $payload,
        string $field,
        ?string $derivedValue,
        bool $nullable,
        array &$errors
    ): void {
        if (!array_key_exists($field, $payload)) {
            return;
        }

        $value = $payload[$field];
        if ($nullable && ($value === null || $value === '')) {
            if ($derivedValue !== null) {
                $errors[] = $field;
            }
            return;
        }

        $date = $this->parseFlexibleDate($value);
        if ($date === null || $date->format('Y-m-d') !== $derivedValue) {
            $errors[] = $field;
        }
    }

    private function validateSummaryAirline(
        array $payload,
        string $field,
        string $derivedValue,
        array &$errors
    ): void {
        if (!array_key_exists($field, $payload)
            || $payload[$field] === null
            || $payload[$field] === ''
        ) {
            return;
        }

        $value = $this->normalizeCode($payload[$field]);
        if (!preg_match('/^[A-Z0-9]{2,20}$/', $value) || $value !== $derivedValue) {
            $errors[] = $field;
        }
    }

    private function readRequiredString(
        array $payload,
        string $field,
        int $maxLength,
        array &$errors,
        bool $normalizeWhitespace = false,
        string $prefix = ''
    ): string {
        $value = $payload[$field] ?? null;
        if (!is_string($value)) {
            $errors[] = $prefix . $field;
            return '';
        }

        $value = $normalizeWhitespace
            ? $this->normalizeWhitespace($value)
            : trim($value);
        if ($value === '' || $this->stringLength($value) > $maxLength) {
            $errors[] = $prefix . $field;
        }

        return $value;
    }

    private function readOptionalString(
        array $payload,
        string $field,
        int $maxLength,
        array &$errors,
        string $prefix = ''
    ): string {
        if (!array_key_exists($field, $payload) || $payload[$field] === null) {
            return '';
        }
        if (!is_string($payload[$field])) {
            $errors[] = $prefix . $field;
            return '';
        }

        $value = trim($payload[$field]);
        if ($this->stringLength($value) > $maxLength) {
            $errors[] = $prefix . $field;
        }

        return $value;
    }

    private function readNullableCode(
        array $payload,
        string $field,
        string $pattern,
        string $prefix,
        array &$errors
    ): string {
        if (!array_key_exists($field, $payload) || $payload[$field] === null) {
            return '';
        }
        if (!is_string($payload[$field])) {
            $errors[] = $prefix . $field;
            return '';
        }

        $value = $this->normalizeCode($payload[$field]);
        if ($value !== '' && !preg_match($pattern, $value)) {
            $errors[] = $prefix . $field;
        }

        return $value;
    }

    private function readEnum($value, array $allowed): ?int
    {
        if (is_int($value)) {
            $normalized = $value;
        } elseif (is_string($value) && preg_match('/^[0-9]+$/', $value)) {
            $normalized = (int) $value;
        } else {
            return null;
        }

        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function parseDateTime($value): ?DateTimeImmutable
    {
        return $this->parseStrictDate($value, 'Y-m-d H:i:s');
    }

    private function parseDate($value): ?DateTimeImmutable
    {
        return $this->parseStrictDate($value, 'Y-m-d');
    }

    private function parseFlexibleDate($value): ?DateTimeImmutable
    {
        $date = $this->parseStrictDate($value, 'Y-m-d');
        if ($date !== null) {
            return $date;
        }

        return $this->parseStrictDate($value, 'd/m/Y');
    }

    private function parseStrictDate($value, string $format): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) !== $value || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!' . $format, $value);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if ($date === false
            || ($dateErrors !== false
                && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format($format) !== $value
        ) {
            return null;
        }

        return $date;
    }

    private function normalizeCode($value): string
    {
        return is_string($value) ? strtoupper(trim($value)) : '';
    }

    private function normalizeWhitespace(string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));
        return is_string($normalized) ? $normalized : trim($value);
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[a-f0-9]{8}-(?:[a-f0-9]{4}-){3}[a-f0-9]{12}$/i',
            $value
        ) === 1;
    }

    private function inferTicketType(array $itineraries): string
    {
        require_once 'modules/EC_Airports/EC_Airports.php';

        $domesticAirports = \EC_Airports::getAirportList(
            \EC_Airports::AIRPORT_SCOPE_DOMESTIC
        );
        foreach ($itineraries as $itinerary) {
            if (!isset($domesticAirports[$itinerary['departure']])
                || !isset($domesticAirports[$itinerary['arrival']])
            ) {
                return '2';
            }
        }

        return '1';
    }

    private function saveItinerary(
        string $bookingId,
        array $data,
        string $currentUserId
    ): void {
        $itinerary = new \EC_Booking_Itineraries();
        $itinerary->name = $data['departure'] . '-' . $data['arrival'];
        $itinerary->direction = (string) $data['direction'];
        $itinerary->airline_code = $data['airlineCode'];
        $itinerary->flight_number = $data['flightNumber'];
        $itinerary->ticket_class = $data['ticketClass'];
        $itinerary->departure = $data['departure'];
        $itinerary->arrival = $data['arrival'];
        $itinerary->departure_date = $data['departureAt'];
        $itinerary->arrival_date = $data['arrivalAt'];
        $itinerary->base_price = $data['basePrice'];
        $itinerary->booking_id = $bookingId;
        $this->assignAuditUser($itinerary, $currentUserId);

        $itinerary->save();
        if (empty($itinerary->id)) {
            throw new RuntimeException('Unable to save booking itinerary');
        }
    }

    private function savePassenger(
        string $bookingId,
        array $data,
        string $currentUserId
    ): void {
        $passenger = new \EC_Booking_Passengers();
        $passenger->name = $data['fullName'];
        $passenger->type = (string) $data['type'];
        $passenger->salutation = (string) $data['salutation'];
        $passenger->birthday = $data['birthday'];
        $passenger->passport_number = $data['identityNumber'];
        $passenger->passport_type = $data['identityNumber'] === '' ? '' : 'I';
        $passenger->booking_id = $bookingId;
        $this->assignAuditUser($passenger, $currentUserId);

        $passengerId = $passenger->save();
        if (!is_string($passengerId) || $passengerId === '') {
            throw new RuntimeException('Unable to save booking passenger');
        }
    }

    private function assignAuditUser($bean, string $userId): void
    {
        $bean->assigned_user_id = $userId;
        $bean->created_by = $userId;
        $bean->modified_user_id = $userId;
    }

    private function log(string $message): void
    {
        if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
            $GLOBALS['log']->fatal($message);
        }
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
    public function updatePassengerFields($params = [])
    {
        $passengerId = $params['passengerId'] ?? '';
        $fields = $params['fields'] ?? [];

        if (empty($passengerId))
            return ['status' => 0, 'message' => 'Không tìm thấy hành khách'];
        if (empty($fields))
            return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = [
            'type' => 'enum',
            'passport_type' => 'enum',
            'passport_number' => 'varchar',
            'passport_nationality' => 'enum',
            'passport_issue_country' => 'enum',
            'passport_issue_date' => 'date',
            'passport_expired_date' => 'date',
        ];

        if (array_key_exists('passport_type', $fields) || array_key_exists('passport_number', $fields)) {
            if (empty($fields['passport_type']) || empty($fields['passport_number']))
                return ['status' => 0, 'message' => 'Vui lòng nhập loại giấy tờ và số giấy tờ'];
        }

        try {
            $passengerBean = new EC_Booking_Passengers();
            $passengerBean->retrieve($passengerId);
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

                $passengerBean->$name = $value;
            }
            if ($passengerBean->save()) {
                return ['status' => 1, 'message' => 'Thao tác thành công'];
            }
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        }
        catch (Throwable $th) {
            $logId = LoggerHelper::generateLogId();
            $GLOBALS['log']->error("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
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
