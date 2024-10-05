<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Log management
 *
 * @method LoggerManager debug(string $message)
 * @method LoggerManager info(string $message)
 * @method LoggerManager warn(string $message)
 * @method LoggerManager deprecated(string $message)
 * @method LoggerManager error(string $message)
 * @method LoggerManager fatal(string $message)
 * @method LoggerManager security(string $message)
 *
 * @api
 * @method debug(string $string)
 * @method info(string $string)
 * @method warn(string $string)
 * @method deprecated(string $string)
 * @method error(string $string)
 * @method fatal(string $string)
 * @method security(string $string)
 * @method off(string $string)
 */
class LoggerManager
{
    /**
     * This is the current log level
     * @var string
     */
    private static $_level = 'fatal';

    //this is a list of different loggers that have been loaded
    protected static $_loggers = [];

    /**
     * This is the instance of the LoggerManager
     * @var null|LoggerManager
     */
    private static $_instance;

    //these are the mappings for levels to different log types
    private static $_logMapping = [
        'default' => 'SugarLogger',
    ];

    // These are the log level mappings anything with a lower value than your current log level will be logged
    private static $_levelMapping = [
        'debug' => 100,
        'info' => 70,
        'warn' => 50,
        'deprecated' => 40,
        'error' => 25,
        'fatal' => 10,
        'security' => 5,
        'off' => 0,
    ];

    //only let the getLogger instantiate this object
    private function __construct()
    {
        $level = SugarConfig::getInstance()->get('logger.level', self::$_level);
        if (!empty($level)) {
            $this->setLevel($level);
        }

        if (empty(self::$_loggers)) {
            $this->_findAvailableLoggers();
        }
    }

    /**
     * Overloaded method that handles the logging requests.
     *
     * @param string $method
     * @param string $message - also handles array as parameter, though that is deprecated.
     */
    public function __call(
        $method,
        $message
    ) {
        if (!isset(self::$_levelMapping[$method])) {
            $method = self::$_level;
        }
        //if the method is a direct match to our level let's let it through this allows for custom levels
        if ($method === self::$_level
            //otherwise if we have a level mapping for the method and that level is less than or equal to the current level let's let it log
            || (!empty(self::$_levelMapping[$method])
                && (
                    (isset(self::$_levelMapping[self::$_level]) ? self::$_levelMapping[self::$_level] : null) >=
                    (isset(self::$_levelMapping[$method]) ? self::$_levelMapping[$method] : null)
                ))) {
            //now we get the logger type this allows for having a file logger an email logger, a firebug logger or any other logger you wish you can set different levels to log differently
            $logger = !empty(self::$_logMapping[$method]) ?
                self::$_logMapping[$method] : self::$_logMapping['default'];
            //if we haven't instantiated that logger let's instantiate
            if (!isset(self::$_loggers[$logger])) {
                self::$_loggers[$logger] = new $logger();
            }
            //tell the logger to log the message
            self::$_loggers[$logger]->log($method, $message);
        }
    }

    /**
     * Check if this log level will be producing any logging
     * @param string $method
     * @return boolean
     */
    public function wouldLog($method)
    {
        if (!isset(self::$_levelMapping[$method])) {
            $method = self::$_level;
        }
        if ($method === self::$_level
            //otherwise if we have a level mapping for the method and that level is less than or equal to the current level let's let it log
            || (!empty(self::$_levelMapping[$method])
                && self::$_levelMapping[self::$_level] >= self::$_levelMapping[$method])) {
            return true;
        }

        return false;
    }

    /**
     * Used for doing design-by-contract assertions in the code; when the condition fails we'll write
     * the message to the debug log
     *
     * @param string $message
     * @param boolean $condition
     */
    public function assert(
        $message,
        $condition
    ) {
        if (!$condition) {
            $this->__call('debug', $message);
        }
    }

    /**
     * Sets the logger to the level indicated
     *
     * @param string $name name of logger level to set it to
     */
    public function setLevel(
        $name
    ) {
        if (isset(self::$_levelMapping[$name])) {
            self::$_level = $name;
        }
    }

    /**
     * Returns a logger instance
     * @return LoggerManager
     */
    public static function getLogger()
    {
        if (!self::$_instance) {
            self::$_instance = new LoggerManager();
        }

        return self::$_instance;
    }

    /**
     * Sets the logger to use a particular backend logger for the given level. Set level to 'default'
     * to make it the default logger for the application
     *
     * @param string $level name of logger level to set it to
     * @param string $logger name of logger class to use
     */
    public static function setLogger(
        $level,
        $logger
    ) {
        self::$_logMapping[$level] = $logger;
    }

    /**
     * Finds all the available loggers in the application
     */
    protected function _findAvailableLoggers()
    {
        foreach (['include/SugarLogger', 'custom/include/SugarLogger'] as $location) {
            if (is_dir($location) && $dir = opendir($location)) {
                while (($file = readdir($dir)) !== false) {
                    if ($file === '..'
                        || $file === '.'
                        || $file === 'LoggerTemplate.php'
                        || $file === 'LoggerManager.php'
                        || !is_file("$location/$file")
                    ) {
                        continue;
                    }
                    require_once("$location/$file");
                    $loggerClass = basename($file, '.php');
                    if (class_exists($loggerClass) && class_implements($loggerClass, 'LoggerTemplate')) {
                        self::$_loggers[$loggerClass] = new $loggerClass();
                    }
                }
            }
        }
    }

    public static function getAvailableLoggers()
    {
        return array_keys(self::$_loggers);
    }

    public static function getLoggerLevels()
    {
        $loggerLevels = self::$_levelMapping;
        foreach ($loggerLevels as $key => $value) {
            $loggerLevels[$key] = ucfirst($key);
        }

        return $loggerLevels;
    }

    public static function setLogLevel($level)
    {
        $instance = self::$_instance;
        $instance::$_level = $level;
    }

    public static function getLogLevel()
    {
        $instance = self::$_instance;

        return $instance::$_level;
    }

    /**
     * Sets the level Mapping.
     *
     * @param string $level name of logger level to set it to
     * @param string $value value of this level
     */
    public static function setLevelMapping($level, $value)
    {
        self::$_levelMapping[$level] = $value;
    }
}
