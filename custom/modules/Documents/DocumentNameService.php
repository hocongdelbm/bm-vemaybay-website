<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Service xử lý logic tạo document_name tự động
 */
class DocumentNameService
{
    /**
     * Tạo tên document tự động dựa trên loại tài liệu, SĐT booking và STT
     * @param SugarBean $bean Đối tượng Document đang được lưu
     * @return string|null Tên document mới hoặc null nếu không cần đổi
     */
    public function generateDocumentName($bean)
    {
        global $app_list_strings;

        // 1. Kiểm tra điều kiện bắt buộc
        if (empty($bean->template_type) || empty($bean->booking_id)) {
            return null;
        }

        // Kiểm tra xem người dùng có cố tình đổi tên không
        // Nếu tên rỗng, hoặc giống hệt tên file (có hoặc không có đuôi mở rộng)
        // thì nghĩa là người dùng để mặc định -> hệ thống ghi đè
        $isDefaultName = false;
        if (empty($bean->document_name)) {
            $isDefaultName = true;
        } elseif (!empty($bean->filename)) {
            $fileNameWithoutExt = pathinfo($bean->filename, PATHINFO_FILENAME);
            if ($bean->document_name === $bean->filename || $bean->document_name === $fileNameWithoutExt) {
                $isDefaultName = true;
            }
        }

        if (!$isDefaultName) {
            return null; // Người dùng đã tự đặt tên, không ghi đè
        }

        // 2. Lấy label của loại tài liệu
        $templateTypeDom = $app_list_strings['document_template_type_dom'] ?? [];
        $typeLabel = $templateTypeDom[$bean->template_type] ?? $bean->template_type;
        $typePrefix = $this->normalizeString($typeLabel);

        // 3. Lấy số điện thoại từ Booking
        $booking = BeanFactory::getBean('EC_Flight_Bookings', $bean->booking_id);
        if (empty($booking->id)) {
            return null;
        }
        $phone = preg_replace('/[^0-9]/', '', (string)($booking->phone ?? '')); // Chỉ lấy số

        // 4. Lấy STT (Đếm số lượng tài liệu cùng loại trong Booking này)
        $stt = $this->getNextSequenceNumber($bean->booking_id, $bean->template_type, $bean->id);

        // 5. Ráp chuỗi
        // Ví dụ: hochieu0948380368_1
        $newName = $typePrefix . $phone . '_' . $stt;

        return $newName;
    }

    /**
     * Lấy số thứ tự tiếp theo cho loại tài liệu của booking
     */
    private function getNextSequenceNumber($bookingId, $templateType, $currentDocumentId = '')
    {
        global $db;
        $query = "SELECT COUNT(id) as total FROM documents 
                  WHERE booking_id = " . $db->quoted($bookingId) . " 
                  AND template_type = " . $db->quoted($templateType) . " 
                  AND deleted = 0";
                  
        // Nếu đang update record đã tồn tại, không đếm chính nó
        if (!empty($currentDocumentId)) {
            $query .= " AND id != " . $db->quoted($currentDocumentId);
        }

        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        $count = !empty($row['total']) ? (int)$row['total'] : 0;
        
        return $count + 1;
    }

    /**
     * Chuyển chuỗi tiếng Việt có dấu thành không dấu, viết thường, xóa khoảng trắng
     */
    private function normalizeString($str)
    {
        $str = mb_strtolower($str, 'UTF-8');
        
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        );

        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        
        // Xóa tất cả ký tự không phải là chữ cái hoặc số
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        
        return $str;
    }
}
