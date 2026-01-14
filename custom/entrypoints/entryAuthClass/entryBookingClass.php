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

        try {
            $bookingBean = new EC_Flight_Bookings();
            $bookingBean->retrieve($bookingId);
            foreach($fields as $name => $value) {
                if(in_array($name, $list_allowed_fields)) {
                    $bookingBean->$name = $value;
                }
            }
            if($bookingBean->save2()) return ['status' => 1, 'message' => 'Thao tác thành công'];
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        }
        catch(Throwable $th) {
            $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Có lỗi xảy ra trong quá trình thao tác"];
        }
    }
}