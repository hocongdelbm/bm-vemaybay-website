<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class FeedLogicBase
{
    public $module = '';
    
    public function pushFeed($bean, $event, $arguments)
    {
    }

    public function installHook($file, $className)
    {
        check_logic_hook_file($this->module, "before_save", array(1, $this->module . " push feed",  $file, $className, "pushFeed"));
    }

    public function removeHook($file, $className)
    {
        remove_logic_hook($this->module, "before_save", array(1, $this->module . " push feed",  $file, $className, "pushFeed"));
    }
}
