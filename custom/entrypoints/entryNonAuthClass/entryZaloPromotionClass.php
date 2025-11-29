<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryNonAuthClass/entryClass.php';

/**
 * Class entryZaloPromotionClass
 * 
 * Gửi tin khuyến mãi hàng loạt trong Zalo 
 */
class entryZaloPromotionClass extends entryClass {
    /**
     * Send info about ticket prices in Lunar New Year 2026
     * 
     * @return string JSON
     * @author DucPham
     */
    public function sendTicketPricesLunarNewYear2026($params = []) {
        global $sugar_config, $db;

        try {
            $number = (int)($params['number'] ?? 100);

            // Content
            $banner_link = "https://gmi.vietjet.net/uploads/2021/07/ve-may-bay-tet-3.webp";
            $header = "Giá vé máy bay Tết 2026 mới cập nhật";
            $text = "Giá vé máy bay Tết 2026 mới cập nhật. Nhanh tay gọi điện đặt vé nào! Bạn đặt càng sớm giá càng tốt so với cận ngày nha, đặc biệt là từ 23 tháng Chạp.<br /><br />Tìm chuyến bay theo cách của bạn, chuyện còn lại để mình lo. Mọi sai lầm trong việc đặt vé máy bay Tết đều trả giá rất đắt : xin lưu ý và thiệt cẩn trọng vào.<br /><br />Hãy cứ lên kế hoạch và gởi dự kiến qua Zalo cty mình nhé. Đội ngũ nhân viên sẽ xử lý nhiệt tình.";
            $text2 = "";
            $table = [
                [
                    "key" => "Tổng đài",
                    "value" => "1900 63 6060"
                ]
            ];
            $buttons = [
                [
                    "title" => "Tham khảo chương trình",
                    "image_icon" => "",
                    "type" => "oa.open.url",
                    "payload" => [
                        "url" => "https://vietjet.net/ve-may-bay-tet/gia-ve-may-bay-tet.html"
                    ]
                ]
            ];

            $sub_type = "lunar-new-year-2026";
            $start_datetime = date('Y-m-01 06:00:00', strtotime('-7 hours'));
            $end_datetime   = date('Y-m-d 21:59:59', strtotime('last day of this month -7 hours'));
            $sqlCheck = "SELECT DISTINCT(to_id) AS zalo_id
                FROM ec_zalo_messages
                WHERE date_entered BETWEEN '{$start_datetime}' AND '{$end_datetime}'
                    AND src = 0
                    AND type = 'promotion'
                    AND sub_type = '{$sub_type}'
                    AND deleted = 0
                UNION
                SELECT to_id AS zalo_id
                FROM ec_zalo_messages
                WHERE date_entered BETWEEN '{$start_datetime}' AND '{$end_datetime}'
                    AND src = 0
                    AND type = 'promotion'
                    AND deleted = 0
                GROUP BY to_id
                HAVING COUNT(*) > 2";
            $resCheck = $db->query($sqlCheck);

            $listNotSend = [];
            while($rowCheck = $db->fetchByAssoc($resCheck)) {
                $listNotSend[] = $rowCheck['zalo_id'];
            }
            $listNotSend = "'" . implode("','", $listNotSend ) . "'";

            $oa_id = $sugar_config['zalo_config']['oa_id'] ?? '';
            $sql = "SELECT zalo_id
                FROM ec_zalo_contacts
                WHERE oa_id = '{$oa_id}'
                    AND is_follower = 1
                    AND status = ''
                    AND zalo_id NOT IN ({$listNotSend})
                ORDER BY date_entered ASC
                LIMIT {$number}";
            $res = $db->query($sql);

            $zaloMessage = new EC_Zalo_Messages();
            $count = 0;
            $results = [];
            while($row = $db->fetchByAssoc($res)) {
                $status = $zaloMessage->send_promotion_message(
                    $row['zalo_id'],
                    $oa_id,
                    $sub_type,
                    $banner_link,
                    $header,
                    $text,
                    $table,
                    $text2,
                    $buttons
                );

                $results[$row['zalo_id']] = $status;
                if($status) $count++;
            }

            return json_encode([
                "status" => 1,
                "message" => "Sent successfully to {$count} users",
                "data" => $results
            ]);
        }
        catch(Throwable $th) {
            return json_encode([
                "status" => 0,
                "message" => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
                "data" => null
            ]);
        }
    }
}