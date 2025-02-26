<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require 'vendor/autoload.php';
use WebSocket\Client;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    global $sugar_config;

    $headers = getallheaders();
    $response = file_get_contents('php://input'); // json
    $data = json_decode($response, true);

    $timestamp  = $data['timestamp'];
    $app_id     = $sugar_config['zalo_config']['app_id'] ? $sugar_config['zalo_config']['app_id'] : '';
    $oa_secret  = $sugar_config['zalo_config']['oa_secret'] ? $sugar_config['zalo_config']['oa_secret'] : '';
    $mac        = "mac=".hash('sha256', $app_id.$response.$timestamp.$oa_secret);
    $h_mac      = isset($headers['X-Zevent-Signature']) ? $headers['X-Zevent-Signature'] : '';
   
    if($mac === $h_mac) {
        global $db;
        $event = isset($data['event_name']) ? $data['event_name'] : '';

        try {
            $live_event_list = [
                'user_send_text', 'user_send_image', 'user_send_gif', 'user_send_link', 'user_send_sticker', 'user_send_location', 'user_send_file', 'user_send_audio', 'user_send_video', 'user_send_business_card',
                'oa_send_text', 'oa_send_image', 'oa_send_gif', 'oa_send_sticker', 'oa_send_file', 'oa_send_list', 'oa_send_template'
            ];

            if(in_array($event, $live_event_list)) {
                // $client = new Client("wss://".$_SERVER['SERVER_NAME']."/chatz/");
                // $client->send($response);
                // $client->close();
                header("HTTP/1.1 200 OK");
                exit();
            }
            else if ($event == 'widget_interaction_accepted') {
                $zalo_user_id = $data['data']['user_id'] ?? ($data['data']['user_external_id'] ?? '');
                $url = $data['data']['url'] ?? '';
                $zalo_last_interaction = date('Y-m-d H:i:s', $timestamp / 1000);
                
                if(!empty($zalo_user_id) && !empty($url)) {
                    $parsed_url = parse_url($url);
                    parse_str($parsed_url['query'], $query_params);

                    $contact_phone = $query_params['contact_phone'] ?? '';
                    if(!empty($contact_phone)) {
                        // Nếu đã tồn tại zalo_user_id id thì (kiểm tra xem từ zalo_user_id có lấy được thông tin hay không)
                            // Nếu có mobile_phone thì thôi
                            // Chưa thì update mobile_phone

                        $sql = "UPDATE contacts
                            SET zalo_id = '$zalo_user_id', zalo_last_interaction = '$zalo_last_interaction'
                            WHERE phone_mobile = '$contact_phone'
                                AND deleted = 0
                                AND (zalo_id IS NULL OR zalo_id = '')
                        ";
                        $db->query($sql);

                        sendTestTelegram("Hệ thống đã map số điện thoại $contact_phone với zalo id $zalo_user_id");
                    }
                }

                header("HTTP/1.1 200 OK");
                exit();
            }
            else {
                echo json_encode(["error" => 0, "message" => "Nothing"]);
                exit();
            }
        }
        catch (Exception $e) {
            echo json_encode([
                "error"     => 1,
                "message"   => "Error: " . $e->getMessage(),
                "data"      => $data
            ]);
            exit();
        }
    }

    echo json_encode(["error" => 1, "code" => 400, "message" => "Bad request"]);
    exit();
}

echo json_encode(["error" => 1, "code" => 404, "message" => "Not found"]);
exit();
?>