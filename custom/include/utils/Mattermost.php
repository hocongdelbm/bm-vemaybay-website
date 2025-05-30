<?php
class Mattermost {
    public static $line_separation = "\n`------------------------------`\n";

    /**
     * Send message (text)
     * 
     * @param string $message
     * @param string $channel_id
     * @return string JSON
     */
    public static function sendMessage($message, $channel_id) {
        if(!$message || !$channel_id) return json_encode(['id' => null, 'message_error' => 'Invalid params']);

        try {
            global $sugar_config;
            $bot_token = $sugar_config['mattermost']['bot_token'] ?? '';
            $body_request = json_encode([
                "channel_id" => $channel_id,
                "message" => trim($message),
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://chat.timchuyenbay.vn/api/v4/posts",
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer $bot_token"
                ],
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $body_request,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 15
            ]);
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorno  = curl_errno($curl);
            $error    = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorno) {
                $m = "cURL error ($errorno): $error";
                return json_encode(["id" => null, "message_error" => $m]);
            }
            elseif($httpcode > 300 ) {
                return json_encode(["id" => null, "message_error" => "Response HTTP code $httpcode"]);
            }
            return $response;
        }
        catch(Throwable $th) {
            return json_encode([
                "id" => null,
                "message_error" => $th->getMessage()
            ]);
        }
    }

    public static function markdownLink($link, $name = 'Link') {
        return "[$name]($link)";
    }

    public static function markdownHeading($str) {
        return "### $str";
    }

    public static function markdownQuote($str) {
        return "> $str";
    }

    public static function markdownCode($str) {
        return "`$str`";
    }
}