<?php

namespace SuiteCRM;

use DBManagerFactory;
use SuiteCRM\Exception\Exception;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Trait RefreshDatabase
 * @package SuiteCRM
 */
trait RefreshDatabase
{
    /**
     * Truncates the database/table before each unit test
     * @param string $table database table to truncate or 'ALL' to truncate all tables.
     * @throws Exception
     */
    public function refreshDatabase($table = 'ALL')
    {
        $db = DBManagerFactory::getInstance();

        if ($table === 'ALL') {
            foreach ($db->getTablesArray() as $table) {
                if (!$db->query('TRUNCATE TABLE ' . $table)) {
                    throw new Exception('Failed to truncate database');
                }
            }
        } elseif (!$db->query('TRUNCATE TABLE ' . $table)) {
            throw new Exception('Failed to truncate table: ' . $table);
        }
    }
}
