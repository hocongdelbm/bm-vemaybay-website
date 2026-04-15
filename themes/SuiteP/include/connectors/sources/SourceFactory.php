<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SourceFactory
{
    /**
     * Given a source param, load the correct source and return the object
     * @param string $source string representing the source to load
     * @return source
     */
    public static function getSource($class, $call_init = true)
    {
        $dir = str_replace('_', '/', $class);
        $parts = explode("/", $dir);
        $file = $parts[count($parts) - 1];
        require_once('include/connectors/sources/default/source.php');
        require_once('include/connectors/ConnectorFactory.php');
        ConnectorFactory::load($class, 'sources');
        try {
            $instance = new $class();
            if ($call_init) {
                $instance->init();
            }
            return $instance;
        } catch (Exception $ex) {
            return null;
        }

        return null;
    }
}
