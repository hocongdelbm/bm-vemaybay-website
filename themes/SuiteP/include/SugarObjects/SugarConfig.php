<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * Config manager
 * @api
 */
class SugarConfig
{
    public $_cached_values = array();

    public static function getInstance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new SugarConfig();
        }
        return $instance;
    }

    public function get($key, $default = null)
    {
        if (!isset($this->_cached_values[$key])) {
            if (!class_exists('SugarArray', true)) {
                require 'include/utils/array_utils.php';
            }
            $this->_cached_values[$key] = isset($GLOBALS['sugar_config']) ?
                SugarArray::staticGet($GLOBALS['sugar_config'], $key, $default) :
                $default;
        }
        return $this->_cached_values[$key];
    }

    public function clearCache($key = null)
    {
        if (is_null($key)) {
            $this->_cached_values = array();
        } else {
            unset($this->_cached_values[$key]);
        }
    }
}
