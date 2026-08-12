<?php
use custom\services\Notification\NotificationService;

abstract class entryClass {
    protected $requestIp;
    protected $debugIPList;
    protected $domain;
    protected $currentUser;
    public $notificationChannel;
    public $telegramConfig;
    public $mattermostConfig;

    public function __construct() {
        global $sugar_config, $current_user;
        
        $this->currentUser = $current_user;
        $this->notificationChannel = $sugar_config['notification_channel'] ?? 'Telegram';
        if($this->notificationChannel == 'Mattermost') $this->mattermostConfig = $sugar_config['mattermost'] ?? [];
        else $this->telegramConfig = $sugar_config['telegram'] ?? [];

        if(function_exists('get_ip_address_from_client')) $this->requestIp = get_ip_address_from_client();
        else $this->requestIp = '';
        $this->debugIPList = [
            '127.0.0.1',
            '14.161.31.237',
        ];
        $this->domain = $_SERVER['HTTP_HOST'] ?? '';
    }

    protected function isDebug() {
        if(in_array($this->requestIp, $this->debugIPList) || stripos($this->domain, 'localhost')) return true;
        return false;
    }

    /**
     * Clean input data
     * 
     * @param string $data
     * @return string
     */
    public function cleanInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Send SQL error notification
     * 
     * @param string $sqlQuery
     * @return void
     */
    public function sendSQLErrorNotification($sqlQuery) {
        $m = "Run query fail in auth entrypoint";
        $m .= "\n<pre>$sqlQuery</pre>";
        NotificationService::sendErrorMessage($m, "default", ["threadKey" => 'logs']);
    }

    /**
     * Send SQL error notification
     * 
     * @param string $sqlQuery
     * @return void
     */
    public function sendSQLDebugNotification($sqlQuery) {
        $m = "Debug query in auth entrypoint";
        $m .= "\n<pre>$sqlQuery</pre>";
        NotificationService::sendMessage($m, "default", ["threadKey" => 'logs']);
    }
}
