<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * Provides a factory to loading a connector along with any key->value options to initialize on the
 * source.  The name of the class to be loaded, corresponds to the path on the file system. For example a source
 * with the name ext_soap_hoovers would be ext/soap/hoovers.php
 * @api
 */
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
        $pos = strrpos($file, '/');
        //if(file_exists("connectors/sources/{$dir}/{$file}.php") || file_exists("custom/connectors/sources/{$dir}/{$file}.php")){
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
        //}

        return null;
    }
}
