<?php
/**
 * Platform Notification Service
 * 
 * A unified service for sending notifications across multiple platforms:
 * - Mattermost
 * - Telegram
 * - (Add more platforms easily)
 * 
 * Usage:
 *   FlatformNoti::send('Mattermost', 'message', 'channel_id');
 *   FlatformNoti::send('Telegram', 'message', 'chat_id', $config);
 *   FlatformNoti::send('all', 'message'); // Send to all configured platforms
 */

require_once 'custom/include/utils/Mattermost.php';
require_once 'custom/include/utils/Telegram.php';

class FlatformNoti {
    
    const MATTERMOST = 'Mattermost';
    const TELEGRAM = 'Telegram';
    
    /**
     * Send notification to specified platform(s)
     * 
     * @param string|array $platforms Platform name(s): 'Mattermost', 'Telegram', 'all', or ['Mattermost', 'Telegram']
     * @param string $message Message to send
     * @param array $options Platform-specific options
     * @return array Result with status and details
     */
    public static function send($platforms, $message, $options = []) {
        $results = [];
        
        // Normalize platforms to array
        if ($platforms === 'all') {
            $platforms = [self::MATTERMOST, self::TELEGRAM];
        } elseif (is_string($platforms)) {
            $platforms = [$platforms];
        }
        
        foreach ($platforms as $platform) {
            $result = self::sendToPlatform($platform, $message, $options);
            $results[$platform] = $result;
        }
        
        return $results;
    }
    
    /**
     * Send to a specific platform
     * 
     * @param string $platform
     * @param string $message
     * @param array $options
     * @return array
     */
    private static function sendToPlatform($platform, $message, $options = []) {
        switch ($platform) {
            case self::MATTERMOST:
                return self::sendToMattermost($message, $options);
            case self::TELEGRAM:
                return self::sendToTelegram($message, $options);
            default:
                return [
                    'success' => false,
                    'error' => "Unknown platform: $platform"
                ];
        }
    }
    
    // =========================================================================
    // Mattermost Integration
    // =========================================================================
    
    /**
     * Send notification to Mattermost
     * 
     * @param string $message
     * @param array $options [
     *     'channel_id' => string,  // Required
     *     'props' => array,       // Optional - Message props
     *     'metadata' => array     // Optional - Message metadata
     * ]
     * @return array
     */
    public static function sendToMattermost($message, $options = []) {
        $channelId = $options['channel_id'] ?? '';
        
        if (empty($channelId)) {
            return [
                'success' => false,
                'error' => 'Mattermost channel_id is required'
            ];
        }
        
        try {
            $props = $options['props'] ?? [];
            $metadata = $options['metadata'] ?? [];
            
            $response = Mattermost::sendMessage($channelId, $message, $props, $metadata);
            $responseData = json_decode($response, true);
            
            if (isset($responseData['id']) && $responseData['id'] !== null) {
                return [
                    'success' => true,
                    'message_id' => $responseData['id'],
                    'platform' => self::MATTERMOST
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $responseData['message_error'] ?? 'Unknown error',
                    'platform' => self::MATTERMOST
                ];
            }
        } catch (Throwable $th) {
            return [
                'success' => false,
                'error' => $th->getMessage(),
                'platform' => self::MATTERMOST
            ];
        }
    }
    
    // =========================================================================
    // Telegram Integration
    // =========================================================================
    
    /**
     * Send notification to Telegram
     * 
     * @param string $message
     * @param array $options [
     *     'bot_token' => string,   // Required if not in config
     *     'chat_id' => string,      // Required
     *     'thread_id' => string,    // Optional
     *     'parse_mode' => string    // Optional: 'HTML' or 'Markdown'
     * ]
     * @return array
     */
    public static function sendToTelegram($message, $options = []) {
        $chatId = $options['chat_id'] ?? '';
        
        if (empty($chatId)) {
            return [
                'success' => false,
                'error' => 'Telegram chat_id is required'
            ];
        }
        
        try {
            $botToken = $options['bot_token'] ?? self::getTelegramBotToken();
            $threadId = $options['thread_id'] ?? '';
            $parseMode = $options['parse_mode'] ?? 'HTML';
            
            if (empty($botToken)) {
                return [
                    'success' => false,
                    'error' => 'Telegram bot_token is required'
                ];
            }
            
            $response = Telegram::sendMessage($message, $botToken, $chatId, $threadId, $parseMode);
            $responseData = json_decode($response, true);
            
            if (isset($responseData['ok']) && $responseData['ok'] === true) {
                return [
                    'success' => true,
                    'message_id' => $responseData['result']['message_id'] ?? null,
                    'platform' => self::TELEGRAM
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $responseData['description'] ?? 'Unknown error',
                    'platform' => self::TELEGRAM
                ];
            }
        } catch (Throwable $th) {
            return [
                'success' => false,
                'error' => $th->getMessage(),
                'platform' => self::TELEGRAM
            ];
        }
    }
    
    // =========================================================================
    // Helper Methods
    // =========================================================================
    
    /**
     * Get Telegram bot token from config
     * 
     * @return string
     */
    private static function getTelegramBotToken() {
        global $sugar_config;
        return $sugar_config['telegram']['bot_token'] ?? '';
    }
    
    /**
     * Format error message for notification
     * 
     * @param string $title Error title
     * @param string $exceptionMessage Exception message
     * @param array $data Additional data to display
     * @return string
     */
    public static function formatError($title, $exceptionMessage, $data = []) {
        $message = "<b>[ERROR] $title</b>";
        $message .= "\n" . $exceptionMessage;
        
        if (!empty($data)) {
            $message .= "\n<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        }
        
        return $message;
    }
    
    /**
     * Format success message for notification
     * 
     * @param string $title Success title
     * @param string $messageBody Message body
     * @param array $data Additional data
     * @return string
     */
    public static function formatSuccess($title, $messageBody, $data = []) {
        $message = "<b>[SUCCESS] $title</b>";
        $message .= "\n" . $messageBody;
        
        if (!empty($data)) {
            $message .= "\n<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        }
        
        return $message;
    }
    
    /**
     * Format info message for notification
     * 
     * @param string $title Info title
     * @param string $messageBody Message body
     * @return string
     */
    public static function formatInfo($title, $messageBody) {
        return "<b>[INFO] $title</b>\n$messageBody";
    }
}
