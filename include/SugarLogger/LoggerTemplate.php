<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * Generic logger
 * @api
 */
interface LoggerTemplate
{
    /**
     * Main method for handling logging a message to the logger
     *
     * @param string $level logging level for the message
     * @param string $message
     */
    public function log(
        $method,
        $message
    );
}
