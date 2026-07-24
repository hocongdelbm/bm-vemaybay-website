<?php

use custom\services\BookingWebhook\BookingWebhookPayloadValidator;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 5)
    . '/custom/services/BookingWebhook/BookingWebhookPayloadValidator.php';

final class BookingWebhookPayloadValidatorTest extends TestCase
{
    private BookingWebhookPayloadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new BookingWebhookPayloadValidator();
    }

    public function testValidOneWayPayloadIsNormalized(): void
    {
        $result = $this->validator->validate($this->validPayload());

        self::assertTrue($result['valid']);
        self::assertSame('HAN', $result['payload']['depCode']);
        self::assertSame('2026-11-15 00:00:00', $result['payload']['depDate']);
        self::assertNull($result['payload']['retDate']);
        self::assertSame('', $result['payload']['airlineCodeRet']);
    }

    public function testValidRoundTripPayload(): void
    {
        $payload = $this->validPayload();
        $payload['retDate'] = '20/11/2026';
        $payload['airlineCodeRet'] = 'VN';

        $result = $this->validator->validate($payload);

        self::assertTrue($result['valid']);
        self::assertSame('2026-11-20 00:00:00', $result['payload']['retDate']);
    }

    public function testReturnDateBeforeDepartureIsRejected(): void
    {
        $payload = $this->validPayload();
        $payload['retDate'] = '14/11/2026';
        $payload['airlineCodeRet'] = 'VN';

        $result = $this->validator->validate($payload);

        self::assertFalse($result['valid']);
        self::assertContains('retDate', $result['errors']);
    }

    public function testRoundTripRequiresReturnAirline(): void
    {
        $payload = $this->validPayload();
        $payload['retDate'] = '20/11/2026';

        $result = $this->validator->validate($payload);

        self::assertFalse($result['valid']);
        self::assertContains('airlineCodeRet', $result['errors']);
    }

    public function testInvalidCalendarDateIsRejected(): void
    {
        $payload = $this->validPayload();
        $payload['depDate'] = '31/02/2026';

        $result = $this->validator->validate($payload);

        self::assertFalse($result['valid']);
        self::assertContains('depDate', $result['errors']);
    }

    public function testInvalidEmailAndTicketTypeAreRejected(): void
    {
        $payload = $this->validPayload();
        $payload['contactEmail'] = 'not-an-email';
        $payload['ticketType'] = 3;

        $result = $this->validator->validate($payload);

        self::assertFalse($result['valid']);
        self::assertContains('contactEmail', $result['errors']);
        self::assertContains('ticketType', $result['errors']);
    }

    public function testNullableFieldsRejectNonStringValues(): void
    {
        $payload = $this->validPayload();
        $payload['contactEmail'] = 123;
        $payload['identityNumber'] = 456;
        $payload['bookingNote'] = ['invalid'];
        $payload['airlineCodeRet'] = false;

        $result = $this->validator->validate($payload);

        self::assertFalse($result['valid']);
        self::assertContains('contactEmail', $result['errors']);
        self::assertContains('identityNumber', $result['errors']);
        self::assertContains('bookingNote', $result['errors']);
        self::assertContains('airlineCodeRet', $result['errors']);
    }

    private function validPayload(): array
    {
        return [
            'depCode' => 'han',
            'desCode' => 'sgn',
            'depDate' => '15/11/2026',
            'retDate' => null,
            'airlineCodeDep' => 'vj',
            'airlineCodeRet' => null,
            'contactName' => 'NGUYEN B',
            'contactPhone' => '0963852741',
            'contactEmail' => 'test@gmail.com',
            'identityNumber' => null,
            'ticketType' => 1,
            'bookingNote' => 'it test',
        ];
    }
}
