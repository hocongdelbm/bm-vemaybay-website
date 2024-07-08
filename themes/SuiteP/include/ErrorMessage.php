<?php

namespace SuiteCRM;

use LoggerManager;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ErrorMessage
{

    /**
     * integer
     */
    const DEFAULT_CODE = 1;

    /**
     * string
     */
    const DEFAULT_LOG_LEVEL = 'fatal';

    /**
     *
     * @var string
     */
    protected $message;

    /**
     *
     * @var integer
     */
    protected $code;

    /**
     *
     * @var string
     */
    protected $level;

    /**
     *
     * @var boolean
     */
    protected $throw;

    /**
     *
     * @param string $message
     * @param integer|null $code
     * @param string $level
     * @param boolean $throw
     */
    public function __construct($message = '', $code = null, $level = self::DEFAULT_LOG_LEVEL, $throw = true)
    {
        $this->setState($message, $code, $level, $throw);
    }

    /**
     *
     * @param string $message
     * @param integer|null $code
     * @param string $level
     * @param boolean $throw
     */
    public function setState($message = '', $code = null, $level = self::DEFAULT_LOG_LEVEL, $throw = true)
    {
        $this->message = $message;
        $this->code = $code;
        $this->level = $level;
        $this->throw = $throw;
    }

    /**
     *
     * @throws ErrorMessageException
     */
    public function handle()
    {
        if ($this->level) {
            $log = LoggerManager::getLogger();
            $level = $this->level;
            $log->$level($this->message);
        }
        if ($this->throw) {
            throw new ErrorMessageException($this->message, $this->code);
        }
    }

    /**
     *
     * @param string $message
     * @param string $level
     * @param boolean $throw
     * @param integer $code
     * @throws ErrorMessageException
     */
    public static function handler($message, $level = self::DEFAULT_LOG_LEVEL, $throw = true, $code = self::DEFAULT_CODE)
    {
        $errorMessage = new ErrorMessage($message, $code, $level, $throw);
        $errorMessage->handle();
    }

    /**
     *
     * @param string $message
     * @param integer $level
     */
    public static function log($message, $level = self::DEFAULT_LOG_LEVEL)
    {
        self::handler($message, $level, false);
    }

    /**
     *
     * @param string $message
     * @param integer $code
     */
    public static function drop($message, $code = self::DEFAULT_CODE)
    {
        self::handler($message, $code);
    }
}
