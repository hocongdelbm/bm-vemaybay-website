<?php
class Mattermost {
    private static $default_key = 'channel_test';
    private static $default_bot_token = 'fgg7yg91ibyzj8eb7sn481d7wo';
    
    /**
     * Get config
     * 
     * @param string $key
     * @return array [chat_id, bot_token]
     */
    public static function getConfig($key = '') {
        try {
            $json = sugar_file_get_contents("custom/configs/mattermost.json");
            if($json && !empty($json)) {
                $arr = json_decode($json, true);
                return isset($arr[$key]) ? $arr[$key] : $arr[self::$default_key];
            }
            return [];
        }
        catch(\Throwable $th) {
            return [];
        }
    }

    /**
     * Send message (text)
     * 
     * @param string $message
     * @param string $channel_id
     * @param string $bot_token
     * @return string JSON
     */
    public static function sendMessage($message, $channel_id, $bot_token = '') {
        try {
            if(!$channel_id || empty($channel_id)) {
                $arr = self::getConfig();
                $channel_id = $arr['channel_id'] ?? '';
                $bot_token = $arr['bot_token'] ?? '';
            }
            if(empty($bot_token)) $bot_token = self::$default_bot_token;

            $body_request = json_encode([
                "channel_id" => $channel_id,
                "message" => trim($message),
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://chat.timchuyenbay.vn/api/v4/posts',
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer $bot_token"
                ],
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $body_request,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 10
            ]);
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
        catch(Throwable $th) {
            return $th->getMessage();
        }
    }
}