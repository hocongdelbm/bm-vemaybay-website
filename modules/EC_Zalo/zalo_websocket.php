<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require('../../vendor/autoload.php');

class ChatZalo implements MessageComponentInterface {
    protected $clients;
    protected $maxConnections;
    protected $currentConnections;
    protected $allowedIPs;

    public function __construct($maxConnections) {
        $this->clients = new \SplObjectStorage;
        $this->maxConnections = $maxConnections;
        $this->currentConnections = 0;
        $this->allowedIPs = ['127.0.0.1'];
    }

    public function onOpen(ConnectionInterface $conn) {
        // IP address validation
        $clientIP = $conn->remoteAddress;
        if (!in_array($clientIP, $this->allowedIPs)) {
            $conn->custom_message = "$clientIP forbidden";
            $conn->close();
            return;
        }

        // Check if max connections reached
        if ($this->currentConnections >= $this->maxConnections) {
            $conn->custom_message = "Connection limit reached";
            $conn->close();
            return;
        }

        // Increment connections count
        $this->currentConnections++;

        // Add connection to the storage
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Retrieve custom data if it exists
        $custom_message = isset($conn->custom_message) ? $conn->custom_message : '';

        // Remove the connection from the storage
        $this->clients->detach($conn);

        // Decrement connections count
        $this->currentConnections--;

        $str = "Connection {$conn->resourceId} has disconnected\n";
        if(!empty($custom_message)) $str = "Connection {$conn->resourceId} has disconnected. {$custom_message}\n";
        echo $str;
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$maxConnections = 130; // Maximum number of connections allowed
$app = new HttpServer(
    new WsServer(
        new ChatZalo($maxConnections)
    )
);

$server = IoServer::factory($app, 8080);
$server->run();
