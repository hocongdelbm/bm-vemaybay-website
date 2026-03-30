<?php
namespace custom\services\Notification\Channels;

use custom\services\Notification\NotificationChannelInterface;

class TelegramChannel implements NotificationChannelInterface {
    public string $parseMode;
    private string $botToken;
    private string $chatId;
    private array $threadIds;

    public function __construct(string $profile = 'default') {
        global $sugar_config;
        $profile_config     = $sugar_config['telegram'][$profile] ?? $sugar_config['telegram']['default'] ?? [];
        $this->parseMode    = $sugar_config['telegram']['parse_mode'] ?? 'HTML';
        $this->botToken     = $profile_config['bot_token'] ?? $sugar_config['telegram']['default']['bot_token'];
        $this->chatId       = $profile_config['chat_id'] ?? '';
        $this->threadIds    = $profile_config['thread_ids'] ?? [];
    }

    /**
     * Send message
     * @param string $message
     * @param string $type
     * @param array $opts
     * @return string JSON
     */
    public function sendMessage(string $message, string $type = 'info', array $opts = []): string {
        if(!empty($message) && !empty($this->chatId) && !empty($this->botToken)) {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage?chat_id={$this->chatId}";
            
            $threadKey = $opts['threadKey'] ?? '';
            if(!empty($threadKey) && isset($this->threadIds[$threadKey])) $url .= "&message_thread_id=" . $this->threadIds[$threadKey];

            $url .= "&parse_mode={$this->parseMode}";

            $message_type = "";
            if($this->parseMode == 'HTML') {
                switch ($type) {
                    case 'warning':
                        $message_type = "⚠️WARNING:";
                        break;
                    case 'error':
                        $message_type = "🔴ERROR:";
                        break;
                    case 'alert':
                        $message_type = "🆘FATAL:";
                        break;
                    default:
                        $message_type = "";
                        break;
                }
            }
            
            $url .= "&text=" . urlencode(trim("$message_type $message"));
            return $this->sendHTTPRequest('GET', $url);
        }
        return $this->returnError("Invalid params");
    }

    /**
     * Send message with data
     * @param array $data
     * @param string $threadKey
     * @return string JSON
     */
    public function sendMessageData($data, $threadKey = '') {
        if(!empty($message) && !empty($this->chatId) && !empty($this->botToken)) {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage?chat_id={$this->chatId}";
            if(!empty($threadKey) && isset($this->threadIds[$threadKey])) $url .= "&message_thread_id=" . $this->threadIds[$threadKey];

            return $this->sendHTTPRequest('POST', $url, ['Content-Type: application/json'], json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        return $this->returnError("Invalid params");
    }

    /**
     * Send document
     * @param string $documentPath
     * @param string $documentName Name to display (filename.txt)
     * @param string $threadKey
     * @return string JSON
     */
    public function sendDocument($documentPath, $documentName, $threadKey = '') {
        if(!empty($message) && !empty($this->chatId) && !empty($this->botToken)) {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendDocument";

            $reqBody = [
                'chat_id' => $this->chatId,
                'document' => new \CURLFile(realpath($documentPath), mime_content_type($documentPath), $documentName),
            ];
            if(!empty($threadKey) && isset($this->threadIds[$threadKey])) $reqBody["message_thread_id"] = $this->threadIds[$threadKey];

            return $this->sendHTTPRequest('POST', $url, [], $reqBody);
        }
        return $this->returnError("Invalid params");
    }

    /**
     * Send message with inline keyboards
     * @param string $message
     * @param array $inlineKeyboard Inline keyboard array (rows of button arrays)
     * @param string $threadKey
     * @return string JSON
     */
    public function sendInlineKeyboardMessage($message, $inlineKeyboard, $threadKey = '') {
        if(!empty($message) && !empty($this->chatId) && !empty($this->botToken)) {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $reqBody = [
                "chat_id" => $this->chatId,
                "text" => $message,
                "parse_mode" => $this->parseMode,
                "reply_markup" => [
                    "inline_keyboard" => $inlineKeyboard
                ]
            ];
            if(!empty($threadKey) && isset($this->threadIds[$threadKey])) $reqBody["message_thread_id"] = $this->threadIds[$threadKey];

            return $this->sendHTTPRequest('POST', $url, ['Content-Type: application/json'], json_encode($reqBody));
        }
        return $this->returnError("Invalid params");
    }

    /**
     * Send HTTP request
     * 
     * @param string $method GET, POST, PUT,...
     * @param string $url
     * @param array $header
     * @param array|string $requestBody
     * @param array $curlOptions
     * @return string JSON
     */
    protected function sendHTTPRequest($method, $url, $header = [], $requestBody = null, $curlOptions = []) {
        try {
            $curl = curl_init();
            if ($curl === false) {
                // LoggerHelper::error("{$method} {$url} cURL failed to initialize");
                return $this->returnError("cURL failed to initialize in BM");
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if(!empty($header)) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            if(!is_null($requestBody)) curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($curl, CURLOPT_TIMEOUT, 16);
            foreach ($curlOptions as $key => $value) curl_setopt($curl, $key, $value);

            $response = curl_exec($curl); // JSON
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $errorNo = curl_errno($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response === false || $errorNo) {
                // LoggerHelper::error("{$method} {$url} cURL error $errorNo: $error");
                return $this->returnError("cURL error $errorNo: $error");
            }

            return $response;
        }
        catch (\Throwable $th) {
            $message = "Exception error {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()}";
            // LoggerHelper::error("{$method} {$this->ENDPOINT}/{$path} $message");
            return $this->returnError("An exception error has occurred: $message");
        }
        finally {
            if (isset($curl) && is_resource($curl)) curl_close($curl);
        }
    }


    /**
     * Return error template
     * 
     * @param string $description A human-readable description of the error
     * @return string JSON
     */
    private function returnError(string $description): string {
        return json_encode(["ok" => false, "description" => $description]);
    }
}