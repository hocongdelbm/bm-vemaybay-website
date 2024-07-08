<?php

namespace SuiteCRM;

use DBManagerFactory;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Trait DatabaseTransactions
 * @package SuiteCRM
 */
trait DatabaseTransactions
{
    public function startDBTransaction()
    {
        $db = DBManagerFactory::getInstance();
        $db->query('START TRANSACTION');
    }

    public function rollbackDBTransaction()
    {
        $db = DBManagerFactory::getInstance();
        $db->query('ROLLBACK');
    }
}
