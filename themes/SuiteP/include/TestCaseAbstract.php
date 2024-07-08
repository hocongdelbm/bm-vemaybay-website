<?php

namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use PHPUnit\Framework\TestCase;
use SuiteCRM\Exception\Exception;

/**
 * Class TestCaseAbstract
 * @package SuiteCRM
 */
abstract class TestCaseAbstract extends TestCase
{
    use DatabaseTransactions;
    use RefreshDatabase;

    protected static $verbose = true;
    protected static $cleanupStrategy = 'transaction';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        if (self::$verbose) {
            $currentTestName = get_class($this) . '::' . $this->getName(false);
            fwrite(STDOUT, "\t" . $currentTestName . ' ..');
            for ($i = 60, $iMax = strlen($currentTestName); $i > $iMax; $i--) {
                fwrite(STDOUT, '.');
            }
        }

        if (self::$cleanupStrategy === 'transaction') {
            $this->startDBTransaction();
        } elseif (self::$cleanupStrategy === 'refresh') {
            $this->refreshDatabase();
        } else {
            throw new Exception('Failed to cleanup database, invalid cleanup strategy specified.');
        }
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (self::$cleanupStrategy === 'transaction') {
            $this->rollbackDBTransaction();
        }

        if (self::$verbose) {
            fwrite(STDOUT, " [done]\n");
        }
    }
}
