<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {}

http_response_code(404);
echo json_encode(["status" => 0, "message" => "Not found"]);
exit();

// function send_webhook_message($mobile, $reward_value, $message, $bot_token, $chat_id, $thread_id = '', $parse_mode = "html"){
//     $date = date("Y-m-d");
//     $keyboard = [
//         'inline_keyboard' => [
//             ['text' =>  'Đã trả thưởng', "callback_data" => "$mobile|$reward_value|$date"]    
//         ]
//     ];

//     $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
//     $params = [
//         'chat_id' => $chat_id,
//         'text' => $message,
//         'parse_mode' => $parse_mode,
//         'reply_markup' => $keyboard
//     ];
//     $curl = curl_init();
//         curl_setopt($curl, CURLOPT_URL, $url);
//         curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
//         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($curl, CURLOPT_TIMEOUT, 12);
//         curl_setopt($curl, CONNECTION_TIMEOUT, 6);
//         curl_setopt($curl, CURLOPT_POST, true);
//         curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
//         $response = curl_exec($curl);
//         curl_close($curl);
//         return $response;
// }

// function send_message($message, $bot_token, $chat_id, $thread_id = '', $parse_mode = 'HTML'){
//         $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
//         $params = [
//             'chat_id' => $chat_id,
//             'text' => $message,
//             'parse_mode' => $parse_mode,
//         ];
//         if (!empty($thread_id))
//             $params['message_thread_id'] = $thread_id;
//         $curl = curl_init();
//         curl_setopt($curl, CURLOPT_URL, $url);
//         curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
//         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($curl, CURLOPT_TIMEOUT, 12);
//         curl_setopt($curl, CONNECTION_TIMEOUT, 6);
//         curl_setopt($curl, CURLOPT_POST, true);
//         curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
//         $response = curl_exec($curl);
//         curl_close($curl);
//         return $response;
// }

function handleWebhook() {
    $update = json_decode(file_get_contents("php://input"), true);

    if (isset($update['callback_query'])) {
        $callback = $update['callback_query'];
        $callback_id = $callback['id']; 
        $chat_id = $callback['message']['chat']['id'];
        $message_id = $callback['message']['message_id'];

        global $bot_token;

        $url = "https://api.telegram.org/bot{$bot_token}/answerCallbackQuery";
        $params = [
            'callback_query_id' => $callback_id
        ];
        file_get_contents($url . "?" . http_build_query($params));

        // 2. Thả reaction 🎉
        $url = "https://api.telegram.org/bot{$bot_token}/setMessageReaction";
        $params = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reaction' => json_encode([
                ["type" => "emoji", "emoji" => "🎉"]
            ])
        ];
        file_get_contents($url . "?" . http_build_query($params));
    }
}

handleWebhook();