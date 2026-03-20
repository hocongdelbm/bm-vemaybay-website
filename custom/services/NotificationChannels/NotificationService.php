<?php
class NotificationService {
    private static ?NotificationChannelInterface $channel = null;
    private static array $channels = []; // Cache changes from single to array

    public static function getChannel(string $profile = 'default'): NotificationChannelInterface {
        // Profile-aware cache key
        if (isset(self::$channels[$profile])) return self::$channels[$profile];

        global $sugar_config;

        $channelName = strtolower($sugar_config['notification_channel'] ?? 'Telegram');
        switch ($channelName) {
            // case 'mattermost':
            //     self::$channels[$profile] = new MattermostChannel();
            //     break;
            default:
                self::$channels[$profile] = new TelegramChannel($profile);
                break;
        }

        return self::$channels[$profile];
    }

    public static function getChannelName(): string {
        global $sugar_config;
        return strtolower($sugar_config['notification_channel'] ?? 'telegram');
    }
    
    /**
     * Send message
     * @param string $message
     * @param string $profile Profile in sugar config
     * @param array $opts
     */
    public static function sendMessage(string $message, string $profile = 'default', array $opts = []) {
        self::getChannel($profile)->sendMessage($message, '', $opts);
    }
    
    /**
     * Send warning message
     * @param string $message
     * @param string $profile Profile in sugar config
     * @param array $opts
     */
    public static function sendWarningMessage(string $message, string $profile = 'default', array $opts = []) {
        self::getChannel($profile)->sendMessage($message, 'warning', $opts);
    }
    
    /**
     * Send error message
     * @param string $message
     * @param string $profile Profile in sugar config
     * @param array $opts
     */
    public static function sendErrorMessage(string $message, string $profile = 'default', array $opts = []) {
        self::getChannel($profile)->sendMessage($message, 'error', $opts);
    }
    
    /**
     * Send fatal message
     * @param string $message
     * @param string $profile Profile in sugar config
     * @param array $opts
     */
    public static function sendFatalMessage(string $message, string $profile = 'default', array $opts = []) {
        self::getChannel($profile)->sendMessage($message, 'fatal', $opts);
    }

    // Reset all or one profile
    public static function reset(string $profile = null): void {
        if ($profile === null) self::$channels = [];
        else unset(self::$channels[$profile]);
    }
}