<?php 
abstract class entryClass {
    protected $requestIp;
    protected $debugIPList;
    protected $domain;

    public function __construct() {
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
}
