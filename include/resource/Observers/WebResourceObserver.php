<?php



require_once('include/resource/Observers/ResourceObserver.php');

/**
 * WebResourceObserver.php
 * This is a subclass of ResourceObserver to provide notification handling
 * for web clients.
 */
class WebResourceObserver extends ResourceObserver
{
    public function __construct($module)
    {
        parent::__construct($module);
    }

    /**
     * notify
     * Web implementation to notify the browser
     * @param msg String message to possibly display
     *
     */
    public function notify($msg = '')
    {
        echo $msg;
        sugar_cleanup(true);
    }
}
