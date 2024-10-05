<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class SugarcrmKeepalive extends SugarBean
{
    public function keepalive(){
        // Set session timeout to 24 hours (86400 seconds)
        $lifetime = 86400;

        session_start();
        setcookie(session_name(), session_id(), time() + $lifetime, "/");
        session_write_close();
    }
}

$sugarcrm_keepalive = new SugarcrmKeepalive();
$sugarcrm_keepalive->keepalive();
