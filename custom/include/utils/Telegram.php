<?php
class Telegram {
    /**
     * Send message
     * 
     * @param string $message
     * @param string $bot_token
     * @param string $chat_id
     * @param string $thread_id
     * @param string $parseMode
     * @return string JSON
     */
    public static function sendMessage($message, $bot_token, $chat_id, $thread_id = '', $parseMode = 'HTML') {
        if(empty($chat_id) || empty($bot_token)) return false;

        $url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id";
        if(!empty($thread_id)) $url .= "&message_thread_id=$thread_id";
        $url .= "&parse_mode=$parseMode&text=" . urlencode(trim($message));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 12);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 6);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
    
    /**
     * Send message with data
     * 
     * @param string $data JSON
     * @param string $bot_token
     * @param string $chat_id
     * @param string $thread_id
     * @return string JSON
     */
    public static function sendMessageData($data, $bot_token, $chat_id, $thread_id = '') {
        if(empty($chat_id) || empty($bot_token)) return false;

        $res = self::postMessageData($data, $bot_token, $chat_id, $thread_id);

        // Group được nâng cấp lên supergroup -> Telegram trả chat_id mới qua migrate_to_chat_id.
        // Tự động gửi lại với chat_id mới để thông báo không bị mất khi config chưa kịp cập nhật.
        $decoded = json_decode((string) $res, true);
        if (is_array($decoded) && empty($decoded['ok']) && !empty($decoded['parameters']['migrate_to_chat_id'])) {
            $res = self::postMessageData($data, $bot_token, $decoded['parameters']['migrate_to_chat_id'], $thread_id);
        }

        return $res;
    }

    /**
     * Gửi 1 request sendMessage (POST JSON body) tới 1 chat_id cụ thể
     *
     * @param string $data JSON
     * @param string $bot_token
     * @param string|int $chat_id
     * @param string $thread_id
     * @return string|false JSON
     */
    private static function postMessageData($data, $bot_token, $chat_id, $thread_id = '') {
        $url = "https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id";
        if(!empty($thread_id)) $url .= "&message_thread_id=$thread_id";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * Send document
     * 
     * @param string $document_path
     * @param string $document_name Name to display (filename.txt)
     * @param string $bot_token
     * @param string $chat_id
     * @param string $thread_id
     * @return string JSON
     */
    public static function sendDocument($document_path, $document_name, $bot_token, $chat_id, $thread_id = '') {
        if(empty($chat_id) || empty($bot_token)) return false;

        $url = "https://api.telegram.org/bot$bot_token/sendDocument";
        $post_fields = [
            'chat_id' => $chat_id,
            'document' => new \CURLFile(realpath($document_path), mime_content_type($document_path), $document_name),
        ];
        if(!empty($thread_id)) $post_fields['message_thread_id'] = $thread_id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * Send message with inline keyboards
     * @param string $message
     * @param array $inline_keyboard Inline keyboard array (rows of button arrays)
     * @param string $bot_token
     * @param string|int $chat_id
     * @param string|int $thread_id
     * @param string $parse_mode
     * @return string JSON
     */
    public static function sendInlineKeyboardMessage($message, $inline_keyboard, $bot_token, $chat_id, $thread_id = '', $parse_mode = "html"){
        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $params = [
            "chat_id" => $chat_id,
            "text" => $message,
            "parse_mode" => $parse_mode,
            "reply_markup" => [
                "inline_keyboard" => $inline_keyboard
            ]
        ];
        if(!empty($thread_id)) $params["message_thread_id"] = $thread_id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 16);
        curl_setopt($curl, CONNECTION_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}