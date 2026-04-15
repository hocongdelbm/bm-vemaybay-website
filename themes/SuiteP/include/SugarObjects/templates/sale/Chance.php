<?php


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/SugarObjects/templates/basic/Basic.php';

class Chance extends Basic
{

    /**
     * Chance constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
