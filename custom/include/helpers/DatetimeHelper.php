<?php
class DatetimeHelper {
    public const DB_FORMAT = 'Y-m-d H:i:s';
    public const DB_DATE_FORMAT = 'Y-m-d';
    public const DB_TIMEZONE= 'UTC';

    /**
     * Convert a datetime string in the user's display format to the DB format
     * (Y-m-d H:i:s in Asia/Ho_Chi_Minh — the storage convention of this project).
     *
     * Accepts a full datetime or a date-only value (time defaults to 00:00:00).
     *
     * @param string $input      Datetime in the given (or user's) date/time format
     * @param string $outputTimezone Timezone the returned datetime is expressed in;
     *                                 defaults to Asia/Ho_Chi_Minh (the DB storage convention)
     * @return string|null DB-formatted datetime, or null when the input cannot be parsed
     */
    public static function user_datetime_to_db(string $input, string $outputTimezone = 'UTC'): ?string {
        global $current_user, $sugar_config;

        $dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $timeFormat = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');
        $timezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        
        try {
            $input_tz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            $input_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        }

        try {
            $output_tz = new DateTimeZone($outputTimezone !== '' ? $outputTimezone : self::DB_TIMEZONE);
        } catch (Exception $e) {
            $output_tz = new DateTimeZone(self::DB_TIMEZONE);
        }

        $input = trim($input);
        
        $dt = DateTime::createFromFormat("$dateFormat $timeFormat|", $input, $input_tz)
            ?: DateTime::createFromFormat($dateFormat . '|', $input, $input_tz);
        if (!$dt) {
            return null;
        }

        return $dt->setTimezone($output_tz)->format(self::DB_FORMAT);
    }

    /**
     * Convert a date string in the user's display format to the DB date format (Y-m-d).
     *
     * Accepts a date-only value or a full datetime (the time part is dropped).
     * Unlike user_datetime_to_db there is no timezone conversion: a pure date has
     * no time component, so shifting it across timezones could move it to the
     * previous/next day.
     *
     * @param string $input Date in the user's date format
     * @return string|null DB-formatted date, or null when the input cannot be parsed
     */
    public static function user_date_to_db(string $input): ?string {
        global $current_user, $sugar_config;

        $dateFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');
        $timeFormat = $current_user->getPreference('timef') ?: ($sugar_config['timef'] ?? 'H:i');

        $input = trim($input);

        $dt = DateTime::createFromFormat($dateFormat . '|', $input)
            ?: DateTime::createFromFormat("$dateFormat $timeFormat|", $input);
        if (!$dt) {
            return null;
        }

        return $dt->format(self::DB_DATE_FORMAT);
    }

    /**
     * Convert a datetime string from one format/timezone to another.
     *
     * Generic converter: both formats are passed in explicitly, unlike the
     * user_* methods which read the current user's preferences.
     *
     * @param string $input        Datetime expressed in $fromFormat
     * @param string $fromFormat   PHP datetime format of the input
     * @param string $toFormat     PHP datetime format of the returned string
     * @param string $fromTimezone Timezone the input is expressed in; defaults to the
     *                             current user's preference (fallback Asia/Ho_Chi_Minh)
     * @param string $toTimezone   Timezone the returned datetime is expressed in;
     *                             defaults to the input timezone (no shift)
     * @return string|null Converted datetime, or null when the input cannot be parsed
     */
    public static function convert_datetime(string $input, string $fromFormat, string $toFormat, string $fromTimezone = '', string $toTimezone = ''): ?string {
        global $current_user, $sugar_config;

        if ($fromTimezone === '') {
            $fromTimezone = $current_user->getPreference('timezone') ?: 'Asia/Ho_Chi_Minh';
        }
        try {
            $from_tz = new DateTimeZone($fromTimezone);
        } catch (Exception $e) {
            $from_tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        }

        try {
            $to_tz = $toTimezone !== '' ? new DateTimeZone($toTimezone) : $from_tz;
        } catch (Exception $e) {
            $to_tz = $from_tz;
        }

        // The trailing '|' zeroes out any fields the format doesn't cover (e.g. seconds)
        $dt = DateTime::createFromFormat($fromFormat . '|', trim($input), $from_tz);
        if (!$dt) {
            return null;
        }

        return $dt->setTimezone($to_tz)->format($toFormat);
    }
}
