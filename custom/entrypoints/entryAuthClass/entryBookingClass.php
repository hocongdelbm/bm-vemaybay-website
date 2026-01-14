<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryBookingClass
 * 
 * Xử lý ajax cho booking
 */
class entryBookingClass extends entryClass {

    /**
     * Update fields
     *
     * @param string $bookingId
     * @param array $fields
     * @return array
     */
    public function updateFields($params = []) {
        $bookingId = $params['bookingId'] ?? '';
        $fields = $params['fields'] ?? [];

        if(empty($bookingId)) return ['status' => 0, 'message' => 'Không tìm thấy booking'];
        if(empty($fields)) return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = ['customer_source'];

        global $db;
        $set = '';
        foreach($fields as $name => $value) {
            if(in_array($name, $list_allowed_fields)) {
                if(!empty($set)) $set .= ", ";
                if(is_string($value)) $set .= "$name = '$value'";
                else $set .= "$name = $value";
            }
        }
        // Update log
        $set .= ", date_modified = '". date('Y-m-d H:i:s', time() - 7*3600) ."'";
        $set .= ", modified_user_id = '{$this->currentUser->id}'";

        try {
            $sqlUpdate = "UPDATE ec_flight_bookings SET $set WHERE id = '$bookingId' AND deleted = 0";
            if($db->query($sqlUpdate)) return ['status' => 1, 'message' => 'Thao tác thành công'];
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        }
        catch(Throwable $th) {
            $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Có lỗi xảy ra trong quá trình thao tác"];
        }
    }
}