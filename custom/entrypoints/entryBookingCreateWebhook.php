<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

header('Content-Type: application/json; charset=utf-8');

$bookingWebhookRespond = static function (
    int $httpCode,
    bool $success,
    ?string $bookingDetail = null,
    ?string $bookingName = null
): void {
    http_response_code($httpCode);
    $body = json_encode([
        'success' => $success,
        'booking_detail' => $bookingDetail,
        'booking_name' => $bookingName,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    echo $body === false
        ? '{"success":false,"booking_detail":null,"booking_name":null}'
        : $body;
    exit;
};

$bookingWebhookHeaders = static function (): array {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $normalized = [];
    foreach (is_array($headers) ? $headers : [] as $name => $value) {
        $normalized[strtolower($name)] = $value;
    }

    return $normalized;
};

$bookingWebhookLog = static function (string $message): void {
    if (isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
        $GLOBALS['log']->fatal($message);
        return;
    }

    error_log($message);
};

final class BookingCreateWebhookHandler
{
    public static $isSavingChatBooking = false;

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

            self::$isSavingChatBooking = true;
            try {
                $bookingId = $booking->save();
            } finally {
                self::$isSavingChatBooking = false;
            }
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
}

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        $bookingWebhookRespond(405, false);
    }

    $headers = $bookingWebhookHeaders();
    $contentType = $headers['content-type'] ?? ($_SERVER['CONTENT_TYPE'] ?? '');
    if (!is_string($contentType) || stripos($contentType, 'application/json') === false) {
        $bookingWebhookRespond(415, false);
    }

    global $sugar_config;
    $config = $sugar_config['webhook']['booking'] ?? [];
    $authKey = is_string($config['auth_key'] ?? null) ? trim($config['auth_key']) : '';
    $signatureKey = is_string($config['signature_key'] ?? null) ? trim($config['signature_key']) : '';

    if (!preg_match('/^[a-f0-9]{64}$/i', $authKey)
        || !preg_match('/^[a-f0-9]{64}$/i', $signatureKey)
    ) {
        $bookingWebhookLog('Booking webhook configuration is incomplete or invalid');
        $bookingWebhookRespond(500, false);
    }

    $requestAuthKey = $headers['x-auth-key'] ?? ($_SERVER['HTTP_X_AUTH_KEY'] ?? '');
    if (!is_string($requestAuthKey) || !hash_equals($authKey, $requestAuthKey)) {
        $bookingWebhookRespond(401, false);
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || trim($rawBody) === '') {
        $bookingWebhookRespond(400, false);
    }

    $requestSignature = $headers['x-signature'] ?? ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    $requestSignature = is_string($requestSignature) ? strtolower(trim($requestSignature)) : '';
    $expectedSignature = hash_hmac('sha256', $rawBody, $signatureKey);
    if (!preg_match('/^[a-f0-9]{64}$/', $requestSignature)
        || !hash_equals($expectedSignature, $requestSignature)
    ) {
        $bookingWebhookRespond(401, false);
    }

    $payload = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        $bookingWebhookRespond(400, false);
    }

    $bookingHandler = new BookingCreateWebhookHandler();
    $validation = $bookingHandler->validate($payload);
    if (!$validation['valid']) {
        $bookingWebhookLog(
            'Booking webhook validation failed for fields: ' . implode(', ', $validation['errors'])
        );
        $bookingWebhookRespond(400, false);
    }

    $sessionUserId = $_SESSION['authenticated_user_id'] ?? '';
    $sessionUniqueKey = $_SESSION['unique_key'] ?? '';
    $serverUniqueKey = $sugar_config['unique_key'] ?? '';
    $hasValidSession = is_string($sessionUserId)
        && $sessionUserId !== ''
        && is_string($sessionUniqueKey)
        && is_string($serverUniqueKey)
        && $serverUniqueKey !== ''
        && hash_equals($serverUniqueKey, $sessionUniqueKey);

    $bookingUser = null;
    if ($validation['payload']['userId'] !== '') {
        $payloadUser = BeanFactory::getBean('Users', $validation['payload']['userId']);
        if (is_object($payloadUser)
            && !empty($payloadUser->id)
            && empty($payloadUser->deleted)
            && (!isset($payloadUser->status) || $payloadUser->status === 'Active')
        ) {
            $bookingUser = $payloadUser;
        }
    }

    if ($bookingUser === null && $hasValidSession) {
        $sessionUser = BeanFactory::getBean('Users', $sessionUserId);
        if (is_object($sessionUser)
            && !empty($sessionUser->id)
            && empty($sessionUser->deleted)
            && (!isset($sessionUser->status) || $sessionUser->status === 'Active')
        ) {
            $bookingUser = $sessionUser;
        }
    }

    if ($bookingUser === null) {
        $bookingWebhookLog(
            'Booking webhook requires an active payload userId or an active session user'
        );
        $bookingWebhookRespond(401, false);
    }

    global $current_user;
    $current_user = $bookingUser;

    $result = $bookingHandler->createBookingFromWebhook($validation['payload']);

    if (empty($result['success'])) {
        $bookingWebhookRespond((int) ($result['http_code'] ?? 500), false);
    }

    $requestHost = $_SERVER['HTTP_HOST'] ?? '';
    if (!is_string($requestHost)
        || !preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/i', $requestHost)
    ) {
        $bookingWebhookLog('Booking webhook request host is invalid');
        $bookingWebhookRespond(500, false);
    }

    $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    if (!$isHttps && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $forwardedProto = strtolower(trim(
            explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]
        ));
        $isHttps = $forwardedProto === 'https';
    }

    $siteUrl = ($isHttps ? 'https' : 'http') . '://' . $requestHost;
    $detailUrl = $siteUrl
        . '/index.php?module=EC_Flight_Bookings&action=DetailView&record='
        . rawurlencode($result['booking_id']);
    $bookingWebhookRespond(200, true, $detailUrl, $result['booking_name']);
} catch (Throwable $throwable) {
    $bookingWebhookLog(sprintf(
        'Booking webhook failed: %s on line %d in %s',
        $throwable->getMessage(),
        $throwable->getLine(),
        $throwable->getFile()
    ));
    $bookingWebhookRespond(500, false);
}
