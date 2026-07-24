<?php

namespace custom\services\BookingWebhook;

use DateTimeImmutable;

final class BookingWebhookPayloadValidator
{
    private const BOOKING_NOTE_MAX_LENGTH = 2000;

    /**
     * @return array{valid: bool, payload: array, errors: array}
     */
    public function validate(array $payload): array
    {
        $errors = [];

        $depCode = $this->normalizeCode($payload['depCode'] ?? null);
        $desCode = $this->normalizeCode($payload['desCode'] ?? null);
        if (!preg_match('/^[A-Z]{3}$/', $depCode)) {
            $errors[] = 'depCode';
        }
        if (!preg_match('/^[A-Z]{3}$/', $desCode) || $desCode === $depCode) {
            $errors[] = 'desCode';
        }

        $depDate = $this->parseDate($payload['depDate'] ?? null);
        if ($depDate === null) {
            $errors[] = 'depDate';
        }

        $retDateValue = array_key_exists('retDate', $payload) ? $payload['retDate'] : null;
        $retDate = $retDateValue === null ? null : $this->parseDate($retDateValue);
        if ($retDateValue !== null && $retDate === null) {
            $errors[] = 'retDate';
        } elseif ($depDate !== null && $retDate !== null && $retDate < $depDate) {
            $errors[] = 'retDate';
        }

        $airlineCodeDep = $this->normalizeAirlineCode($payload['airlineCodeDep'] ?? null);
        if (!preg_match('/^[A-Z0-9]{2,20}$/', $airlineCodeDep)) {
            $errors[] = 'airlineCodeDep';
        }

        $airlineCodeRetValue = $payload['airlineCodeRet'] ?? null;
        $airlineCodeRet = $this->normalizeNullableAirlineCode($airlineCodeRetValue);
        if (($airlineCodeRetValue !== null && !is_string($airlineCodeRetValue))
            || ($airlineCodeRet !== '' && !preg_match('/^[A-Z0-9]{2,20}$/', $airlineCodeRet))
            || ($retDate !== null && $airlineCodeRet === '')
        ) {
            $errors[] = 'airlineCodeRet';
        }

        $contactName = $this->normalizeRequiredString($payload['contactName'] ?? null);
        if ($contactName === '' || $this->length($contactName) > 128) {
            $errors[] = 'contactName';
        }

        $contactPhone = $this->normalizeRequiredString($payload['contactPhone'] ?? null);
        if ($contactPhone === '' || $this->length($contactPhone) > 30) {
            $errors[] = 'contactPhone';
        }

        $contactEmailValue = $payload['contactEmail'] ?? null;
        $contactEmail = $this->normalizeNullableString($contactEmailValue);
        if (($contactEmailValue !== null && !is_string($contactEmailValue))
            || $this->length($contactEmail) > 50
            || ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false)
        ) {
            $errors[] = 'contactEmail';
        }

        $identityNumberValue = $payload['identityNumber'] ?? null;
        $identityNumber = $this->normalizeNullableString($identityNumberValue);
        if (($identityNumberValue !== null && !is_string($identityNumberValue))
            || $this->length($identityNumber) > 16
        ) {
            $errors[] = 'identityNumber';
        }

        $ticketType = $payload['ticketType'] ?? null;
        if (!is_int($ticketType) || !in_array($ticketType, [1, 2], true)) {
            $errors[] = 'ticketType';
        }

        $bookingNoteValue = $payload['bookingNote'] ?? null;
        $bookingNote = $this->normalizeNullableString($bookingNoteValue);
        if (($bookingNoteValue !== null && !is_string($bookingNoteValue))
            || $this->length($bookingNote) > self::BOOKING_NOTE_MAX_LENGTH
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

    private function parseDate($value): ?DateTimeImmutable
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

    private function normalizeCode($value): string
    {
        return is_string($value) ? strtoupper(trim($value)) : '';
    }

    private function normalizeAirlineCode($value): string
    {
        $value = is_string($value) ? strtoupper(trim($value)) : '';
        return $this->length($value) <= 20 ? $value : '';
    }

    private function normalizeNullableAirlineCode($value): string
    {
        return $value === null ? '' : $this->normalizeAirlineCode($value);
    }

    private function normalizeRequiredString($value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function normalizeNullableString($value): string
    {
        return $value === null ? '' : $this->normalizeRequiredString($value);
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
