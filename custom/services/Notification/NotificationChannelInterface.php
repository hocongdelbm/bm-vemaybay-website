<?php
namespace Custom\Services\Notification;

interface NotificationChannelInterface {
    /**
     * Send message
     * @param string $message Message content
     * @param string $type Message type: info, warning, error, fatal
     * @param array $opts Message options
     * @return string JSON
     */
    public function sendMessage(string $message, string $type = 'info', array $opts = []): string;
}