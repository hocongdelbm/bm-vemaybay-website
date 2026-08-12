<?php
class EC_Booking_Passengers_Helper {
    /**
     * Calculate the expiry date of a Vietnamese Citizen Identification Card (CCCD),
     * used for passengers whose passport/document type is 'I' (Identity Card).
     *
     * Business rules:
     *  - A CCCD expires when the citizen reaches one of the milestone ages 25, 40 or 60.
     *  - The baseline expiry for a milestone is the exact date of birth plus the milestone
     *    age (same month/day), e.g. born 2011-06-10, milestone 25 => 2036-06-10.
     *  - Once the citizen is 60 or older, the card expires 6 months after the date they
     *    turned 60 (DOB + 60 years + 6 months).
     *
     * Milestone selection based on current age:
     *  - age < 25          => milestone 25
     *  - 25 <= age < 40    => milestone 40
     *  - 40 <= age < 60    => milestone 60
     *  - age >= 60         => DOB + 60 years + 6 months
     *
     * Both the input $dob and the returned date use the current user's date
     * format (SugarCRM `datef` preference, e.g. d-m-Y), not the DB Y-m-d format.
     *
     * @param string $dob Date of birth in the current user's date format.
     * @return string Expiry date in the current user's date format, or empty string when $dob is invalid.
     */
    public static function calculatePassportExpiryDate(string $dob): string {
        global $current_user, $sugar_config;

        $dob = trim($dob);
        if ($dob === '') {
            return '';
        }

        // User date format used for both parsing the input and formatting the output.
        $userFormat = $current_user->getPreference('datef') ?: ($sugar_config['datef'] ?? 'd-m-Y');

        // Parse the input strictly with the user format to keep leap years and
        // month arithmetic correct (PHP handles Feb 29 rollovers natively).
        $birthDate = DateTimeImmutable::createFromFormat('!' . $userFormat, $dob);
        if ($birthDate === false) {
            // Input does not match the expected user date format.
            return '';
        }

        // Reference "today" for age calculation, using the business timezone.
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

        // Whole-year age at the reference date.
        $age = (int) $birthDate->diff($now)->y;

        // Citizens 60 or older: expiry = the day they turned 60 plus 6 months.
        if ($age >= 60) {
            $expiry = $birthDate->add(new DateInterval('P60Y'))
                                ->add(new DateInterval('P6M'));
            return $expiry->format($userFormat);
        }

        // Pick the next milestone age the citizen has not yet reached.
        if ($age < 25) {
            $milestone = 25;
        } elseif ($age < 40) {
            $milestone = 40;
        } else { // 40 <= age < 60
            $milestone = 60;
        }

        // Baseline expiry = date of birth + milestone years (same month/day).
        $expiry = $birthDate->add(new DateInterval('P' . $milestone . 'Y'));

        return $expiry->format($userFormat);
    }
}
