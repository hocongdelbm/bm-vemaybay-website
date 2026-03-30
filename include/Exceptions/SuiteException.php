<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SuiteException extends Exception
{
    const NO_ID = 1;
}
