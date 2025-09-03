<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    handleWebhook();
    http_response_code(200);
    exit();
}

// Nếu không phải POST
http_response_code(404);
echo json_encode(["status" => 0, "message" => "Not found"]);
exit();

// function callEndpoint($code, $value, $cardId){
//     global $sugar_config;
//     $host_name = $sugar_config["host_name"];
//     $url = "https://".$host_name."/index.php?entryPoint=entryPointGeneralNA";
//     $params = [
//         "code" => $code,
//         "value" => $value,
//         "status" => 1,
//         "cardId" => $cardId
//     ];
//     $body = [
//         "class"  => "entryEvent020925Class",
//         "method" => "updateUserTopupCards",
//         "params" => $params
//     ];
//     $key = $sugar_config['api_key']['non_auth_entrypoint'];
//     $request = json_encode($body);

//     $curl = curl_init();
//     curl_setopt($curl, CURLOPT_URL, $url);
//     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Api-Key: '.$key]);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($curl, CURLOPT_TIMEOUT, 12);
//     curl_setopt($curl, CURLOPT_POST, true);
//     curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
//     $response = curl_exec($curl);
//     curl_close($curl);

//     if(!$response){
//         return ["status" => 2]; // lỗi
//     }
//     $fetch = json_decode($response, true);
//     return $fetch ?? ["status" => 2];
// }

function handleWebhook() {
    try {
        $update = json_decode(file_get_contents("php://input"), true);

        if (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $callback_id = $callback['id']; 
            $chat_id = $callback['message']['chat']['id'];
            $message_id = $callback['message']['message_id'];
            $data_query = explode('|', $callback['data']); // ✅ lấy từ data

            $code   = $data_query[0] ?? null;
            $value  = $data_query[1] ?? null;
            $cardId = $data_query[2] ?? null;

            global $sugar_config;
            $botToken = $sugar_config['telegram']['event020925']['bot_token'];
            $data = [
                "code" => $code,
                "value" => $value,
                "status" => 1,
                "cardId" => $cardId
            ];
            // $response = callEndpoint($code, $value, $cardId); 
            require_once 'custom/entrypoints/entryNonAuthClass/entryEvent020925Class.php';
            $endpoint = new entryEvent020925Class();
            $response = $endpoint->updateUserTopupCards($data);
            // Trả lời callback để tắt loading
            $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";
            $params = [
                'callback_query_id' => $callback_id,
                'text' => $response["status"] == 1 ? '✅ Thành công' : '❌ Thất bại'
            ];
            file_get_contents($url . "?" . http_build_query($params));

            // Nếu thành công thì thả reaction 👍
            if($response["status"] == 1){
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
            return true;
        }

        return false;
    } catch (\Throwable $th) {
        //throw $th;
        return $th->getMessage();
    }
}
