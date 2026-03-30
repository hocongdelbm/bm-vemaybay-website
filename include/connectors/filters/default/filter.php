<?php

/**
 * Generic filter
 * @api
 */
class default_filter
{
    public $_component;

    public function __construct() {}

    public function setComponent($component)
    {
        $this->_component = $component;
    }

    /**
     * getList
     * Returns a nested array containing a key/value pair(s) of a source record
     *
     * @param array $args Array of arguments to search/filter by
     * @param string $module String optional value of the module that the connector framework is attempting to map to
     * @return array of key/value pair(s) of source record; empty Array if no results are found
     */
    public function getList($args, $module)
    {
        $args = $this->_component->mapInput($args, $module);
        return $this->_component->getSource()->getList($args, $module);
    }
}
