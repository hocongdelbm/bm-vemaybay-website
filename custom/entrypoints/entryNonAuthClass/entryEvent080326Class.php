<?php
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryEvent080326Class
 * Sự kiện 08/03/2026
 */
class entryEvent080326Class extends entryClass
{
    /**
     * Get list bk completed for special prize
     * ngày 01/03/2026 - 08/03/2026
     * @return array
     */
    public function getAllBookingCompleted()
    {
        global $db;

        $start_date = '2026-03-01';
        $end_date = '2026-03-08';

        $sql = "
                SELECT id, name, phone, contact_name, date_entered
                FROM ec_flight_bookings 
                WHERE booking_status = 8 
                AND deleted = 0
                AND date_entered BETWEEN '" . $start_date . "' AND '" . $end_date . " 17:59:59'
            ";
        $result = $db->query($sql);
        $data = [];
        while ($row = $db->fetchByAssoc($result)) {
            $data[$row['name']] = $row;
        }

        return $data;
    }
}
