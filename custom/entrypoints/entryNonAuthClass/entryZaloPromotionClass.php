<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryNonAuthClass/entryClass.php';
require_once 'custom/include/helpers/api/APIZaloOA.php';

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
            $oa_id = $sugar_config['zalo_config']['oa_id'] ?? '';

            $countSuccess = 0;
            $results = [];

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


            /******  Stage 1: Get list user who will not send  ******/ 
            $start_datetime = date('Y-m-01 06:00:00', strtotime('-7 hours'));
            $end_datetime   = date('Y-m-d 21:59:59', strtotime('last day of this month -7 hours'));
            $sqlCheck = "SELECT DISTINCT(to_id) AS zalo_id
                FROM ec_zalo_messages
                WHERE src = 0
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
            // $listNotSend = "'" . implode("','", $listNotSend ) . "'";


            /******  Stage 2: Get list follower by API and send message  ******/ 
            $apiZaloOA   = new APIZaloOA();
            $zaloMessage = new EC_Zalo_Messages();
            $start_date = '2024_01_01';
            $end_date   = '2025_11_30';
            $is_follower = true;

            // Get data from cache
            $cacheHelper = new CacheHelper('file');
            $cache_key = "{$sub_type}-zalo-promotional-messages";
            $cacheData = $cacheHelper->get($cache_key);
            $offset = (int)($cacheData['offset'] ?? 0);

            $i = 0;
            while($countSuccess < $number && $i < 25) {
                $i++;
                $res = json_decode($apiZaloOA->get_list_user($offset, 50, "{$start_date}:{$end_date}", $is_follower), true);

                if(isset($res['error']) && $res['error'] == 0) {
                    $listUsers = $res['data']['users'] ?? [];
                    
                    foreach($listUsers as $u) {
                        if($countSuccess >= $number) break;

                        if(array_search($u['user_id'], $listNotSend) === false) {
                            $status = $zaloMessage->send_promotion_message(
                                $u['user_id'],
                                $oa_id,
                                $sub_type,
                                $banner_link,
                                $header,
                                $text,
                                $table,
                                $text2,
                                $buttons
                            );

                            if($status) $countSuccess++;
                            $results[$u['user_id']] = $status;
                        }

                        $offset++;
                    }
                }
                else {
                    break;
                }
            }

            $cacheData['offset'] = $offset;
            $cacheHelper->set($cache_key, $cacheData);

            return json_encode([
                "status" => 1,
                "message" => "Sent successfully to {$countSuccess} users",
                "data" => $results
            ]);
        }
        catch(Throwable $th) {
            $GLOBALS['log']->fatal("Error when running sendTicketPricesLunarNewYear2026(): {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return json_encode([
                "status" => 0,
                "message" => "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}",
                "data" => null
            ]);
        }
    }
}