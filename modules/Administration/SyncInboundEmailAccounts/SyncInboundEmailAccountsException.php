<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SyncInboundEmailAccountsException extends Exception
{
    const UNKNOWN_ERROR = 100;
    const PROCESS_OUTPUT_CLEANUP_ERROR = 110;
    const PROCESS_OUTPUT_WRITE_ERROR = 120;
}
