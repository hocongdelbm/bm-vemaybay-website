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
            SELECT b.id, b.name, b.phone, b.contact_name, b.date_entered
            FROM ec_flight_bookings b
            WHERE b.booking_status = 8 
            AND b.deleted = 0
            AND b.date_entered BETWEEN '{$start_date}' AND '{$end_date} 17:59:59'
            AND NOT EXISTS (
                SELECT 1 
                FROM ec_hoanve hv
                WHERE hv.booking_id = b.id
                AND hv.deleted = 0
            )
        ";
        
        $result = $db->query($sql);
        $data = [];
        while ($row = $db->fetchByAssoc($result)) {
            $data[$row['name']] = $row;
        }

        return $data;
    }
}
