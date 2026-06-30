<?php
require_once 'custom/entrypoints/entryClass.php';

use custom\services\Notification\NotificationService;

/**
 * Class entryBookingClass
 * 
 * Xử lý ajax cho booking
 */
class entryBookingClass extends entryClass {
    /**
     * Update fields
     *
     * @param array $params
     * @return array
     */
    public function updateFields($params = [])
    {
        $bookingId = $params['bookingId'] ?? '';
        $fields = $params['fields'] ?? [];

        if (empty($bookingId))
            return ['status' => 0, 'message' => 'Không tìm thấy booking'];
        if (empty($fields))
            return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = ['customer_source', 'zalo_id'];

        try {
            $bookingBean = new EC_Flight_Bookings();
            $bookingBean->retrieve($bookingId);
            foreach ($fields as $name => $value) {
                if (in_array($name, $list_allowed_fields)) {
                    // Người dùng có thể dán nguyên link chat Zalo (vd: https://oa.zalo.me/chat?uid=123&oaid=456)
                    // -> chỉ lưu uid làm zalo_id.
                    if ($name === 'zalo_id' && !empty($value) && preg_match('/\buid=(\d+)/i', (string) $value, $matches)) {
                        $value = $matches[1];
                    }
                    $bookingBean->$name = $value;
                }
            }
            if ($bookingBean->save2())
                return ['status' => 1, 'message' => 'Thao tác thành công'];
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        } catch (Throwable $th) {
            $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Có lỗi xảy ra trong quá trình thao tác"];
        }
    }

    /**
     * Update fields
     *
     * @param array $params
     * @return array
     */
    public function updatePassengerFields($params = [])
    {
        $passengerId = $params['passengerId'] ?? '';
        $fields = $params['fields'] ?? [];

        if (empty($passengerId))
            return ['status' => 0, 'message' => 'Không tìm thấy hành khách'];
        if (empty($fields))
            return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = ['type'];

        try {
            $passengerBean = new EC_Booking_Passengers();
            $passengerBean->retrieve($passengerId);
            foreach ($fields as $name => $value) {
                if (in_array($name, $list_allowed_fields)) {
                    $passengerBean->$name = $value;
                }
            }
            if ($passengerBean->save()) {
                return ['status' => 1, 'message' => 'Thao tác thành công'];
            }
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        } catch (Throwable $th) {
            $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Có lỗi xảy ra trong quá trình thao tác"];
        }
    }

    /**
     * Get uploaded documents
     *
     * @param array $params
     * @return string
     */
    public function getUploadedDocuments($params = [])
    {
        header('Content-Type: application/json');
        $booking_id = $params['booking_id'] ?? '';

        if (empty($booking_id) || $booking_id == '') {
            return json_encode([
                'success' => false,
                'message' => 'Booking ID không hợp lệ'
            ]);
        }

        global $db, $app_list_strings;
        $query =
            "SELECT d.id,
                d.document_name,
                d.date_entered,
                d.document_revision_id as revision_id,
                d.category_id,
                d.doc_url as doc_url,
                dr.doc_url as revision_doc_url,
                u.user_name as created_by
        FROM documents d
        LEFT JOIN users u ON d.created_by = u.id
        LEFT JOIN document_revisions dr ON d.document_revision_id = dr.id
        WHERE d.booking_id = '" . $db->quote($booking_id) . "'
        AND d.deleted = 0
        ORDER BY d.date_entered DESC";

        $result = $db->query($query);
        $documents = [];

        while ($row = $db->fetchByAssoc($result)) {
            $category = 'Khác';
            if (!empty($row['category_id']) && isset($app_list_strings['document_category_dom'][$row['category_id']])) {
                $category = $app_list_strings['document_category_dom'][$row['category_id']];
            }

            // Use direct public share URL from doc_url (already has /download)
            $previewUrl = !empty($row['revision_doc_url']) ? $row['revision_doc_url'] . '/preview' : (!empty($row['doc_url']) ? $row['doc_url'] . '/preview' : "");

            $documents[] = [
                'id' => $row['id'],
                'document_name' => $row['document_name'],
                'category' => $category,
                'date_entered' => (new DateTime($row['date_entered'], new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
                    ->format('d/m/Y H:i'),
                'created_by_name' => $row['created_by'] ?: 'N/A',
                'preview_image' => $previewUrl,
                'doc_url' => $row['doc_url'] ?? '', // Add doc_url for direct download
                'revision_id' => $row['revision_id']
            ];
        }
        return json_encode([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Get lotion (address) by geocode
     * @param array $params
     * @return array
     */
    public function getLocation($params = [])
    {
        $lat = $params['lat'] ?? '';
        $long = $params['long'] ?? '';
        $bookingId = $params['bookingId'] ?? '';
        $city = $this->removeVietnameseTones('Hồ Chí Minh');

        if (empty($lat) || empty($long) || empty($bookingId)) {
            return ["status" => 0, "message" => "Tọa độ không hợp lệ", "data" => null];
        }

        try {
            $locationService = new custom\services\Location\LocationService();
            $res = $locationService->reverseGeocode($lat, $long);

            if (isset($res['status']) && $res['status']) {
                $city = trim($res['data']['city'] ?? '');
                if (empty($city))
                    $city = trim($res['data']['ward'] ?? '');

                if (!empty($city)) {
                    global $db;

                    // Cleaned
                    $city = trim(str_replace("Thành phố", "", $city));
                    $city = trim(str_replace("Thành Phố", "", $city));
                    $city = trim(str_replace("Tỉnh", "", $city));
                    if ($city == "Thủ Đức")
                        $city = "Hồ Chí Minh";
                    $city = $this->removeVietnameseTones($city);
                    $sql = "UPDATE ec_flight_bookings SET city = '$city' WHERE id = '$bookingId' AND deleted = 0";
                    if ($db->query($sql)) {
                        return [
                            "status" => 1,
                            "message" => "Success",
                            "data" => $city,
                        ];
                    } else {
                        return [
                            "status" => 0,
                            "message" => "Dữ liệu chưa được lưu vào BM",
                            "data" => $city,
                        ];
                    }
                }

                return [
                    "status" => 0,
                    "message" => "Không tìm thấy vị trí phù hợp từ tọa độ",
                    "data" => $res['data'],
                    "raw" => $res
                ];
            }

            $m = "Lấy thông tin vị trí không thành công";
            $m = "\n<pre>" . json_encode($res, JSON_UNESCAPED_UNICODE) . "</pre>";
            NotificationService::sendWarningMessage($m, "", ["threadKey" => "logs"]);
            return $res;
        } catch (Throwable $th) {
            return [
                "status" => 0,
                "message" => "{$th->getMessage()} on line {$th->getLine()}",
                "data" => null
            ];
        }
    }

    public function getLocationByIp($params = [])
    {
        $ip = $params['ip'] ?? '';
        $bookingId = $params['bookingId'] ?? '';

        if (empty($ip) || empty($bookingId)) {
            return ["status" => 0, "message" => "IP không hợp lệ", "data" => null];
        }

        try {
            $locationService = new custom\services\Location\LocationService();
            $res = $locationService->getLocationByIp($ip);

            if (isset($res['status']) && $res['status']) {
                $city = trim($res['data']['city'] ?? '');

                if (!empty($city)) {
                    global $db;

                    $city = trim(str_replace("City", "", $city));

                    $sql = "UPDATE ec_flight_bookings SET city = '$city' WHERE id = '$bookingId' AND deleted = 0";
                    if ($db->query($sql)) {
                        return ["status" => 1, "message" => "Success", "data" => $city];
                    } else {
                        return ["status" => 0, "message" => "Dữ liệu chưa được lưu vào BM", "data" => $city];
                    }
                }

                return ["status" => 0, "message" => "Không tìm thấy vị trí từ IP", "data" => null];
            }

            return ["status" => 0, "message" => "Lấy thông tin IP không thành công", "data" => null];
        } catch (Throwable $th) {
            return ["status" => 0, "message" => "{$th->getMessage()} on line {$th->getLine()}", "data" => null];
        }
    }

    public function removeVietnameseTones($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        return $str;
    }
}
