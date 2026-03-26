<?php
namespace Custom\Services\Notification\Channels;

use Custom\Services\Notification\NotificationChannelInterface;

class MattermostChannel implements NotificationChannelInterface {
    private string $channelId;
    private string $logsChannelId;

    public function __construct(array $config) {
        $this->channelId     = $config['channel_id']      ?? '';
        $this->logsChannelId = $config['channel_id_logs'] ?? '';
    }

    public function sendMessage(string $message, string $type = 'info', array $opts = []): string {
        return'';
    }
}