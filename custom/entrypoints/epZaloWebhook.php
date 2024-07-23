<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require 'vendor/autoload.php';
use WebSocket\Client;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    global $sugar_config;

    $headers = getallheaders();
    $data    = file_get_contents('php://input'); // json
    $arr     = json_decode($data, true);

    $timestamp  = $arr['timestamp'];
    $app_id     = $sugar_config['zalo_config']['app_id'] ? $sugar_config['zalo_config']['app_id'] : '';
    $oa_secret  = $sugar_config['zalo_config']['oa_secret'] ? $sugar_config['zalo_config']['oa_secret'] : '';
    $mac        = "mac=".hash('sha256', $app_id.$data.$timestamp.$oa_secret);
    $h_mac      = isset($headers['X-Zevent-Signature']) ? $headers['X-Zevent-Signature'] : '';

    if($mac === $h_mac) {
        $event = isset($arr['event_name']) ? $arr['event_name'] : '';

        try {
            $event_list = [
                'user_send_text', 'user_send_image', 'user_send_gif', 'user_send_link', 'user_send_sticker', 'user_send_location', 'user_send_file', 'user_send_audio', 'user_send_video',
                'oa_send_text', 'oa_send_image', 'oa_send_gif', 'oa_send_sticker', 'oa_send_file', 'oa_send_list', 'oa_send_template'
            ];

            if(in_array($event, $event_list)) {
                $client = new Client("wss://".$_SERVER['SERVER_NAME']."/chatz/");
                $client->send($data);
                $client->close();
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
                "data"      => $arr
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