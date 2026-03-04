<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    handleWebhook();
    http_response_code(200);
    exit;
}

http_response_code(405);
echo json_encode(["status" => 0, "message" => "Method not allowed"]);
exit;

function handleWebhook() {
    try {
        $update = json_decode(file_get_contents("php://input"), true);

        if (isset($update['callback_query'])) {
            $callback       = $update['callback_query'];
            $callback_id    = $callback['id']; 
            $chat_id        = $callback['message']['chat']['id'];
            $message_id     = $callback['message']['message_id'];

            $data_query = explode('|', $callback['data']); // Get data
            $action = $data_query[0] ?? null;

            if($action == 'updateUserTopupCards') {
                global $sugar_config;
                $botToken = $sugar_config['telegram']['event020925']['bot_token'];
                $data = [
                    "code"   => $data_query[1],
                    "value"  => $data_query[2],
                    "cardId" => $data_query[3],
                    "status" => 1
                ];

                require_once 'custom/entrypoints/entryNonAuthClass/entryEvent020925Class.php';
                $endpoint = new entryEvent020925Class();
                $response = $endpoint->$action($data);

                // Trả lời callback để tắt loading
                $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";
                $params = [
                    'callback_query_id' => $callback_id,
                    'text' => $response["status"] == 1 ? '✅ Thành công' : '❌ Thất bại'
                ];
                file_get_contents($url . "?" . http_build_query($params));

                // Nếu thành công thì thả reaction 👍
                if($response["status"] == 1) {
                    $url = "https://api.telegram.org/bot{$botToken}/setMessageReaction";
                    $params = [
                        'chat_id' => $chat_id,
                        'message_id' => $message_id,
                        'reaction' => json_encode([
                            ["type" => "emoji", "emoji" => "👍"]
                        ])
                    ];
                    file_get_contents($url . "?" . http_build_query($params));
                }
            }
            elseif($action == 'setSpinPrize') {
                global $sugar_config;
                $botToken = $sugar_config['telegram']['event020925']['bot_token'];
                $data = [
                    "code"      => $data_query[1],
                    "typePrize" => $data_query[2],
                    "prize"     => $data_query[3],
                    "prizeId"   => $data_query[4]
                ];

                require_once 'custom/entrypoints/entryNonAuthClass/entryEvent020925Class.php';
                $endpoint = new entryEvent020925Class();
                $response = $endpoint->$action($data);

                // Trả lời callback để tắt loading
                $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";
                $params = [
                    'callback_query_id' => $callback_id,
                    'text' => $response["status"] == 1 ? '✅ Thành công' : '❌ Thất bại'
                ];
                file_get_contents($url . "?" . http_build_query($params));

                // Nếu thành công thì thả reaction 👍
                if($response["status"] == 1) {
                    $url = "https://api.telegram.org/bot{$botToken}/setMessageReaction";
                    $params = [
                        'chat_id' => $chat_id,
                        'message_id' => $message_id,
                        'reaction' => json_encode([
                            ["type" => "emoji", "emoji" => "👍"]
                        ])
                    ];
                    file_get_contents($url . "?" . http_build_query($params));
                }
            }
            return true;
        }
        return false;
    }
    catch (\Throwable $th) {
        return $th->getMessage();
    }
}
