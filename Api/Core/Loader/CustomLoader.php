<?php
namespace Api\Core\Loader;

use Exception;
use LoggerManager;
use Slim\App;

/**
 * CustomLoader
 *
 * @author gyula
 */
class CustomLoader
{
    const ERR_NO_ERROR = 0;
    const ERR_FILE_NOT_FOUND = 1;
    const ERR_ROUTE_FILE_NOT_FOUND = 2;
    const ERR_WRONG_CUSTOM_FORMAT = 3;
    
    /**
     *
     * @var int
     */
    protected static $lastError = self::ERR_NO_ERROR;
    
    /**
     *
     * @var string
     */
    protected static $customPath = 'custom/application/Ext/Api/V8/';
    
    public static function setCustomPath($customPath = 'custom/application/Ext/Api/V8/')
    {
        self::$customPath = $customPath;
    }
    
    public static function getCustomPath()
    {
        return self::$customPath;
    }
    
    /**
     *
     * @return int
     */
    public static function getLastError()
    {
        $ret = self::$lastError;
        self::$lastError = self::ERR_NO_ERROR;
        return $ret;
    }
    
    /**
     * merge multidimensional arrays
     *
     * @param array $arrays
     * @return array
     */
    public static function arrayMerge($arrays)
    {
        $result = [];
        foreach ((array)$arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_int($key)) {
                    // is indexed?
                    $result[] = $value;
                } elseif (isset($result[$key]) && is_array($value) && is_array($result[$key])) {
                    // is associative?
                    $result[$key] = self::arrayMerge([$result[$key], $value]);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
    
    /**
     * include and merge custom arrays (custom file should return an array)
     *
     * @param array $array
     * @param string $customFile
     * @return array
     * @throws Exception
     */
    public static function mergeCustomArray($array, $customFile)
    {
        self::getLastError();
        $customFile = self::$customPath . $customFile;
        if (!file_exists($customFile)) {
            self::$lastError = self::ERR_FILE_NOT_FOUND;
            LoggerManager::getLogger()->debug('Custom file is not exists: ' . $customFile);
        } else {
            $customs = include $customFile;
            if (!is_array($customs)) {
                throw new Exception('Custom file should return an array.', self::ERR_WRONG_CUSTOM_FORMAT);
            }
            $array = self::arrayMerge([$array, $customs]);
        }
        
        return $array;
    }
    

    /**
     *
     * @param App $app
     * @return App
     */
    public static function loadCustomRoutes(App $app, $customRoutesFile = 'Config/routes.php')
    {
        self::getLastError();
        $customRoutesFile = self::$customPath . $customRoutesFile;
        if (!file_exists($customRoutesFile)) {
            self::$lastError = self::ERR_ROUTE_FILE_NOT_FOUND;
            LoggerManager::getLogger()->debug('Custom routes file is not exists: ' . $customRoutesFile);
        } else {
            include $customRoutesFile;
        }
        return $app;
    }
}
