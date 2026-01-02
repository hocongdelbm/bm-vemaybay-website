<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryClass.php';
require_once 'custom/include/helpers/api/APIZaloOA.php';

/**
 * Class entryBookingClass
 *
 */
class entryBookingClass extends entryClass {
    public function exportBookings($params = []) {
        try {
            if(count($params) > 10) return "Only a maximum of 10 bookings are supported per request";

            global $db;
            $insertQuery = '';
            foreach($params as $booking_name) {
                $booking_name = trim($booking_name);
                if(empty($booking_name)) continue;

                $sql = "SELECT * FROM ec_flight_bookings WHERE name = '{$booking_name}'";
                $res = $db->query($sql);
                $rowBooking = $db->fetchByAssoc($res);
                $booking_id = $rowBooking['id'] ?? '';

                if(empty($booking_id)) continue;

                $columns = implode(',', array_keys($rowBooking));
                $values = implode(',', array_map(fn($v) => "'{$v}'", array_values($rowBooking)));
                $insertQuery .= "INSERT INTO ec_flight_bookings ({$columns}) VALUES ({$values});\n";

                $list_table = [
                    'ec_booking_itineraries',
                    'ec_booking_details',
                    'ec_booking_passengers'
                ];
                foreach($list_table as $table) {
                    $sql = "SELECT * FROM {$table} WHERE booking_id = '{$booking_id}'";
                    $res = $db->query($sql);
                    while($row = $db->fetchByAssoc($res)) {
                        $columns = implode(',', array_keys($row));
                        $values = implode(',', array_map(fn($v) => "'{$v}'", array_values($row)));
                        $insertQuery .= "INSERT INTO {$table} ({$columns}) VALUES ({$values});\n";
                    }
                }

                $insertQuery .= "\n";
            }

            return $insertQuery;
        }
        catch(Throwable $th) {
            return "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
        }
    }
}