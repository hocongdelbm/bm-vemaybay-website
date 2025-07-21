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
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
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
}