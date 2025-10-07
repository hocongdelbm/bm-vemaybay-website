<?php
class LoggerHelper {
    protected static $logPath = "secure_sessions/request_logs";

    /**
     * Log a message with a given level.
     *
     * @param string $level Log level: emergency, alert, critical, error, warning, notice, info, debug
     * @param string $message Log message
     * @param mixed context Additional contextual data
     */
    public static function log($level, $message, $context = null): void
    {
        try {
            $level = strtoupper($level);
            $dateTime = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $date = $dateTime->format('Y-m-d');
            $time = $dateTime->format('H:i:s');

            // Convert context array to string for logging
            $contextString = '';
            if(is_array($context) || is_object($context)) $contextString = json_encode($context);
            elseif(is_string($context)) $contextString = $context;

            $logLine = trim("[{$date} {$time}] {$level} {$message}");
            if ($contextString !== '') $logLine .= " {$contextString}";
            $logLine .= PHP_EOL;

            $curYear = date('Y');
            $curMonth = date('m');
            $path = self::$logPath . "/{$curYear}/$curMonth";
            $logFile = "$path/{$date}.log";
            if (!is_dir($path)) {
                mkdir($path, 0744, true);
            }

            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        }
        catch(Throwable $th) {
            pr($th->getMessage());
        }
    }

    // Shortcut methods for common levels
    public static function error(string $message, $context = null): void
    {
        self::log(__FUNCTION__, $message, $context);
    }

    public static function warning(string $message, $context = null): void
    {
        self::log(__FUNCTION__, $message, $context);
    }

    public static function info(string $message, $context = null): void
    {
        self::log(__FUNCTION__, $message, $context);
    }

    public static function debug(string $message, $context = null): void
    {
        self::log(__FUNCTION__, $message, $context);
    }
}
