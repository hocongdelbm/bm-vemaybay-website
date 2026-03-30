<?php
class LoggerHelper
{
    protected static $logPath = "secure_sessions/request_logs";

    public static function setLogPath($path)
    {
        self::$logPath = rtrim($path, '/');
    }

    /**
     * Log a message with a given level.
     *
     * @param string $level Log level: emergency, alert, critical, error, warning, notice, info, debug
     * @param string $message Log message
     * @param mixed Context additional contextual data
     * @return string Log id
     */
    public static function log($level, $message, $context = null): ?string
    {
        // Generate log id
        $logId = self::generateLogId();

        try {
            $level = strtoupper($level);
            $dateTime = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $date = $dateTime->format('Y-m-d');
            $time = $dateTime->format('H:i:s');

            // Convert context array to string for logging
            $contextString = '';
            if (is_array($context) || is_object($context)) $contextString = json_encode($context);
            elseif (is_string($context)) $contextString = $context;

            $logLine = trim("[{$date} {$time}][$logId] {$level} {$message}");
            if ($contextString !== '') $logLine .= " {$contextString}";
            $logLine .= PHP_EOL;

            $curYear = date('Y');
            $curMonth = date('m');

            $path = self::$logPath . "/{$curYear}/$curMonth";
            $logFile = "$path/{$date}.log";
            if (!is_dir($path)) {
                mkdir($path, 0744, true);
            }

            if (file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX)) return $logId;
            return null;
        } catch (Throwable $th) {
            $GLOBALS['log']->fatal("[{$logId}] {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
            return $logId;
        }
    }

    /**
     * Generate log id
     * 
     * @return string
     * @author DucPham
     */
    public static function generateLogId()
    {
        return "LOG" . round(microtime(true) * 1000) . bin2hex(random_bytes(6));
    }

    // Shortcut methods for common levels
    public static function error(string $message, $context = null): ?string
    {
        return self::log(__FUNCTION__, $message, $context);
    }

    public static function warning(string $message, $context = null): ?string
    {
        return self::log(__FUNCTION__, $message, $context);
    }

    public static function info(string $message, $context = null): ?string
    {
        return self::log(__FUNCTION__, $message, $context);
    }

    public static function debug(string $message, $context = null): ?string
    {
        return self::log(__FUNCTION__, $message, $context);
    }
}
