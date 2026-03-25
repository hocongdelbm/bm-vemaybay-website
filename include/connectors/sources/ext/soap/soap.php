<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


require_once('include/connectors/sources/default/source.php');
require_once('include/nusoap/nusoap.php');

/**
 * ext_soap
 * This class is the soap implementation for the connector framework.
 * Connectors that use SOAP calls should subclass this class and provide
 * a getList and getItem method override to return results from the connector
 * @api
 */
abstract class ext_soap extends source
{
    protected $_client;

    /**
     * obj2array
     * Given an object, returns the object as an Array
     *
     * @param $obj Object to convert to an array
     * @return $out Array reflecting the object's properties
     */
    public function obj2array($obj)
    {
        $out = array();
        if (empty($obj)) {
            return $out;
        }

        foreach ($obj as $key => $val) {
            switch (true) {
                case is_object($val):
                    $out[$key] = $this->obj2array($val);
                    break;
                case is_array($val):
                    $out[$key] = $this->obj2array($val);
                    break;
                default:
                    $out[$key] = $val;
            }
        }
        return $out;
    }
}
