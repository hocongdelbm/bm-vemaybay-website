<?php



/**
 * ResourceObserver.php
 * This class serves as the base class for the notifier/observable pattern used
 * by the resource management framework.
 */
class ResourceObserver
{
    public $module;
    public $limit;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function notify($msg = '')
    {
        if ($this->dieOnError) {
            die($GLOBALS['app_strings']['ERROR_NOTIFY_OVERRIDE']);
        } else {
            echo ($GLOBALS['app_strings']['ERROR_NOTIFY_OVERRIDE']);
        }
    }
}
