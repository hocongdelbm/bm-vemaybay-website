<?php
class Bookings_Helper
{
    /**
     * Get online payment link
     *
     * @param string|null $booking_id
     * @param string|null $created_by_id User id create booking
     * @return string URL
     */
    public static function get_online_payment_link(?string $booking_id, ?string $created_by_id) {
        $booking_id = (string) $booking_id;
        $created_by_id = (string) $created_by_id;

        $domain_name = get_server_name($created_by_id);
        if (in_array($domain_name, ['vietjet.net', 'timchuyenbay.com', 'timchuyenbay.vn', 'vemaybay5s.com'])) {
            $payment_link = "https://$domain_name/thanh-toan-online?bkid=$booking_id";
        } else {
            $payment_link = "https://timchuyenbay.vn/thanh-toan-online?bkid=$booking_id";
        }

        return $payment_link;
    }
}
