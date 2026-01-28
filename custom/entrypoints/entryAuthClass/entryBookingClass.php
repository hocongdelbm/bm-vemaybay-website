<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryBookingClass
 * 
 * Xử lý ajax cho booking
 */
class entryBookingClass extends entryClass
{
    /**
     * Update fields
     *
     * @param string $bookingId
     * @param array $fields
     * @return array
     */
    public function updateFields($params = [])
    {
        $bookingId = $params['bookingId'] ?? '';
        $fields = $params['fields'] ?? [];

        if (empty($bookingId)) return ['status' => 0, 'message' => 'Không tìm thấy booking'];
        if (empty($fields)) return ['status' => 0, 'message' => 'Dữ liệu không hợp lệ'];
        $list_allowed_fields = ['customer_source'];

        try {
            $bookingBean = new EC_Flight_Bookings();
            $bookingBean->retrieve($bookingId);
            foreach ($fields as $name => $value) {
                if (in_array($name, $list_allowed_fields)) {
                    $bookingBean->$name = $value;
                }
            }
            if ($bookingBean->save2()) return ['status' => 1, 'message' => 'Thao tác thành công'];
            return ['status' => 0, 'message' => 'Thao tác không thành công, vui lòng thử lại'];
        } catch (Throwable $th) {
            $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return ["status" => 0, "message" => "Có lỗi xảy ra trong quá trình thao tác"];
        }
    }

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
                u.user_name as created_by
        FROM documents d
        LEFT JOIN users u ON d.created_by = u.id
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
            $documents[] = [
                'id' => $row['id'],
                'document_name' => $row['document_name'],
                'category' => $category,
                'date_entered' => date('d/m/Y H:i', strtotime($row['date_entered'])),
                'created_by_name' => $row['created_by'] ?: 'N/A',
                'preview_image' => "index.php?entryPoint=NextCloudPreview&id=" . $row['id'],
                'revision_id' => $row['revision_id']
            ];
        }
        return json_encode([
            'success' => true,
            'documents' => $documents
        ]);
    }
}
