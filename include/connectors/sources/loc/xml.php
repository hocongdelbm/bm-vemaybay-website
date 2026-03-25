<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


/**
 * Local XML source
 * @api
 */
abstract class loc_xml extends source
{
    public function __parse($file)
    {
        $contents = file_get_contents($file);
        libxml_disable_entity_loader(true);
        return simplexml_load_string($contents);
    }
}
